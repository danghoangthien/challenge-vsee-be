<?php

namespace App\Exceptions;

class VisitorAlreadyInQueueException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Visitor is already in queue');
    }
} 