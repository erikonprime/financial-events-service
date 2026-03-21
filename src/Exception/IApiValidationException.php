<?php

namespace App\Exception;

interface IApiValidationException
{
    public function toArray(): array;
}
