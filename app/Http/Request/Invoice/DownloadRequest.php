<?php
/**
 * Copyright (c) 2022 Strategio Digital s.r.o.
 * @author Jiří Zapletal (https://strategio.dev, jz@strategio.dev)
 */
declare(strict_types=1);

namespace App\Http\Request\Invoice;

use Nette\Schema\Expect;
use Saas\Http\Request\Request;
use Saas\Storage\Storage;
use Symfony\Component\HttpFoundation\Response;

class DownloadRequest extends Request
{
    // Inject custom dependencies here
    public function __construct(protected readonly Storage $storage)
    {
    }
    
    public function schema(): array
    {
        return [
            // Add custom request validations
            'invoice' => Expect::structure([
                'type' => Expect::anyOf('invoice', 'proforma', 'invoice-annul', 'proforma-annul'),
                'number' => Expect::string()->required(),
                'issue_date' => Expect::string()->required(),
                'due_date' => Expect::string()->required(),
                'currency' => Expect::string()->required(),
                'items_text' => Expect::string(),
                'footer_text' => Expect::string(),
            ])->required()->castTo('array')
        ];
    }
    
    public function process(array $data): Response
    {
        // Process validated data here
        return $this->json($data);
    }
}