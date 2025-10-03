<?php
declare(strict_types=1);

namespace App\App\Serializer;

use App\App\Serializer\Validator\RecursiveValidator;
use App\App\Serializer\Validator\ReflectionHelper;
use App\App\Serializer\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;

class ValidatorFactory
{
    public static function create(): ValidatorInterface
    {
        $symfonyValidator = Validation::createValidator();
        $reflectionHelper = new ReflectionHelper();

        return new RecursiveValidator($symfonyValidator, $reflectionHelper);
    }
}
