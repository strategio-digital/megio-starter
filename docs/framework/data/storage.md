---
layout: 'page'
uri: '/data/storage'
position: 4
slug: 'data-storage'
parent: 'data'
navTitle: 'Storage'
title: 'File Storage (S3/Minio)'
description: 'S3-compatible file storage for uploads, downloads, and file management.'
---

# File Storage (S3/Minio)

S3-compatible file storage for uploads, downloads, and file management.

## How It Works

1. **Storage** service resolves adapter from config
2. **StorageAdapter** interface defines operations
3. **S3Storage** implements adapter for AWS S3/Minio
4. **Files** are uploaded, retrieved, listed, or deleted

## Configuration

### Environment Variables

```dotenv
# Storage adapter type
APP_STORAGE_ADAPTER=s3

# S3/Minio configuration
S3_ENDPOINT=http://minio:9000
S3_REGION=us-east-1
S3_BUCKET=my-bucket
S3_KEY=minioadmin
S3_SECRET=minioadmin
```

### Minio (Development)

```yaml
# docker-compose.yml
services:
  minio:
    image: minio/minio
    ports:
      - "9000:9000"
      - "9001:9001"
    environment:
      MINIO_ROOT_USER: minioadmin
      MINIO_ROOT_PASSWORD: minioadmin
    command: server /data --console-address ":9001"
    volumes:
      - minio_data:/data
```

### AWS S3 (Production)

```dotenv
S3_ENDPOINT=https://s3.eu-central-1.amazonaws.com
S3_REGION=eu-central-1
S3_BUCKET=production-bucket
S3_KEY=AKIAIOSFODNN7EXAMPLE
S3_SECRET=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
```

## Using Storage

### In Facade

```php
<?php
declare(strict_types=1);

namespace App\Product\Facade;

use Megio\Storage\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class UploadProductImageFacade
{
    public function __construct(
        private Storage $storage,
    ) {}

    public function execute(UploadedFile $file, string $productId): string
    {
        $adapter = $this->storage->get();

        $result = $adapter->upload(
            file: $file,
            destination: "products/{$productId}/",
            name: 'image',
            publish: true,
        );

        return $result->getPathname();
    }
}
```

## StorageAdapter Interface

```php
interface StorageAdapter
{
    public function upload(
        UploadedFile $file,
        string $destination,
        ?string $name = null,
        bool $publish = true,
    ): SplFileInfo;

    public function get(string $destination): ?string;

    public function put(string $destination, string $newLine): void;

    public function delete(SplFileInfo $file): void;

    public function deleteFolder(string $destination): void;

    /** @return array<int, SplFileInfo> */
    public function list(string $destination): array;
}
```

## Operations

### Upload File

```php
$adapter = $this->storage->get();

// Upload with original name
$result = $adapter->upload($uploadedFile, 'uploads/');

// Upload with custom name
$result = $adapter->upload($uploadedFile, 'avatars/', 'user-123');

// Upload as private (no public access)
$result = $adapter->upload($uploadedFile, 'private/', null, false);

// Get path
$path = $result->getPathname(); // "uploads/image.jpg"
```

### Get File URL

```php
$adapter = $this->storage->get();

$url = $adapter->get('products/123/image.jpg');
// Returns: "https://bucket.s3.amazonaws.com/products/123/image.jpg"
// Returns: null if file doesn't exist
```

### List Files

```php
$adapter = $this->storage->get();

$files = $adapter->list('products/123/');
// Returns: array<SplFileInfo>

foreach ($files as $file) {
    echo $file->getPathname();  // "products/123/image.jpg"
    echo $file->getBasename();  // "image.jpg"
    echo $file->getExtension(); // "jpg"
}
```

### Delete File

```php
$adapter = $this->storage->get();

$file = new SplFileInfo('products/123/image.jpg');
$adapter->delete($file);

// Also deletes thumbnails matching pattern:
// products/123/image--thumb*.jpg
```

### Delete Folder

```php
$adapter = $this->storage->get();

$adapter->deleteFolder('products/123/');
// Deletes all files in the folder
```

### Append to File (Streaming)

```php
$adapter = $this->storage->get();

$adapter->put('logs/2024-01.log', 'New log entry');
// Appends line to file (creates if doesn't exist)
```

## File Paths

### Path Format

```
{destination}/{name}.{extension}

# Examples:
products/123/image.jpg
avatars/user-456.png
documents/report.pdf
```

### Public vs Private

```php
// Public - accessible via URL
$adapter->upload($file, 'public/', null, true);

// Private - requires signed URL
$adapter->upload($file, 'private/', null, false);
```

## Request Handler Example

### Upload Endpoint

```php
<?php
declare(strict_types=1);

namespace App\Product\Http\Request;

use App\Product\Facade\UploadProductImageFacade;
use Megio\Http\Request\AbstractRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UploadProductImageRequest extends AbstractRequest
{
    public function __construct(
        private readonly UploadProductImageFacade $facade,
    ) {}

    public function process(Request $request): Response
    {
        $file = $request->files->get('image');
        $productId = $request->request->get('product_id');

        if ($file === null) {
            return $this->error(['errors' => ['No file uploaded']], 400);
        }

        $path = $this->facade->execute($file, $productId);

        return $this->json(['path' => $path]);
    }
}
```

## S3 Client Access

For advanced operations, access the raw S3 client:

```php
$adapter = $this->storage->get();

if ($adapter instanceof S3Storage) {
    $s3Client = $adapter->getS3Client();

    // Use AWS SDK directly
    $s3Client->copyObject([
        'Bucket' => $_ENV['S3_BUCKET'],
        'CopySource' => 'source/file.jpg',
        'Key' => 'destination/file.jpg',
    ]);
}
```

## Storage Rules

### Required Patterns

- Use `Storage` service (injected via DI)
- Use `StorageAdapter` interface methods
- Handle null returns from `get()`
- Use meaningful destination paths

### Forbidden

- Direct S3Client instantiation
- Hardcoded bucket names
- Storing sensitive files publicly
- Storing files without validation

### File Organization

| Content | Path Pattern |
|---------|-------------|
| User avatars | `avatars/{userId}.{ext}` |
| Product images | `products/{productId}/{name}.{ext}` |
| Documents | `documents/{type}/{id}.{ext}` |
| Temporary | `temp/{hash}.{ext}` |
| Logs | `logs/{date}.log` |
