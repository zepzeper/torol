<?php

namespace Torol\Exceptions;

use Exception;

class LoadException extends Exception
{
    public function __construct(string $exception)
    {
            parent::__construct($exception);
    }
}
