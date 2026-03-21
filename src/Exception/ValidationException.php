<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ValidationException extends Exception implements IApiValidationException
{
    public function __construct(private readonly array $violations, string $message = 'Validation error')
    {
        parent::__construct(message: $message, code: Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'violations' => $this->violations,
        ];
    }

}
