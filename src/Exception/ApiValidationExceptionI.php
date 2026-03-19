<?php

namespace App\Exception;

interface ApiValidationExceptionI
{
    public function toArray(): array;
}
