<?php

namespace App\Exceptions;

class NotFoundException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
} 