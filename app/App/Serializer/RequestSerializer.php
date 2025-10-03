<?php
declare(strict_types=1);

namespace App\App\Serializer;

use App\App\Serializer\Dto\ResponseDtoInterface;
use App\App\Serializer\Validator\ValidatorInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

readonly class RequestSerializer
{
    private Serializer $serializer;

    public function __construct(
        private ValidatorInterface $validator,
    ) {
        $this->serializer = new Serializer(
            normalizers: [
                new DateTimeNormalizer(),
                new ObjectNormalizer(),
                new ArrayDenormalizer(),
            ],
            encoders: [new JsonEncoder()],
        );
    }

    /**
     * @template T
     *
     * @param class-string<T> $dtoClass
     * @param array<int|string, mixed> $data
     *
     * @throws RequestSerializerException
     *
     * @return T
     */
    public function denormalize(
        string $dtoClass,
        array $data,
    ) {
        $errors = $this->validator->validate($dtoClass, $data);

        if (count($errors) > 0) {
            throw new RequestSerializerException($errors);
        }

        $dto = $this->serializer->denormalize(
            data: $data,
            type: $dtoClass,
            format: JsonEncoder::FORMAT,
        );
        assert($dto instanceof $dtoClass);
        return $dto;
    }

    public function serialize(ResponseDtoInterface $dto): JsonResponse
    {
        try {
            $json = $this->serializer->serialize($dto, 'json');
            return new JsonResponse($json, Response::HTTP_OK, [], true);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Chyba při serializaci dat: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
