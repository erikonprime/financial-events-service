<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class DuplicateEventException extends Exception implements IApiException
{
    public function __construct(string $message = 'Event already processed')
    {
        parent::__construct(message: $message, code: Response::HTTP_CONFLICT);
    }
}
