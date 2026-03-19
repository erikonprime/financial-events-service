<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class DuplicateEventException extends Exception implements ApiExceptionI
{
    public function __construct(string $message = 'Event already processed')
    {
        parent::__construct(message: $message, code: Response::HTTP_CONFLICT);
    }
}
