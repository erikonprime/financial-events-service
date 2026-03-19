<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class DatabasePersistenceException extends Exception implements ApiExceptionI
{
    public function __construct(string $message = 'Database persistence error')
    {
        parent::__construct(message: $message, code: Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
