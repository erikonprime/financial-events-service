<?php

declare(strict_types=1);

namespace App\Validator;

use App\Dto\EventDTO;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class EventValidator
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {}

    public function validate(EventDTO $eventDTO): void
    {
        $violations = $this->validator->validate($eventDTO);

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $v) {
                $errors[$v->getPropertyPath()] = $v->getMessage();
            }

            throw new ValidationException(violations: $errors);
        }
    }
}
