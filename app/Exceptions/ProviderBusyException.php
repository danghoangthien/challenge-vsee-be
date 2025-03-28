<?php

namespace App\Exceptions;

class ProviderBusyException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Provider is already examining another visitor');
    }
} 