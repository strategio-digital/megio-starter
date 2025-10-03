<?php
declare(strict_types=1);

namespace App\App\Serializer;

use App\App\Serializer\Validator\ValidatorInterface;
use Exception;

/**
 * @phpstan-import-type ValidationErrors from ValidatorInterface
 */
class RequestSerializerException extends Exception
{
    /**
     * @param ValidationErrors $errors
     */
    public function __construct(
        private readonly array $errors = [],
        string $message = "Request deserialization failed",
    ) {
        $this->message = $message . ': ' . json_encode($errors);
        parent::__construct($this->message);
    }

    /**
     * @return ValidationErrors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
