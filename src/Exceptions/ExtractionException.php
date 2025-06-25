<?php

namespace Torol\Exceptions;

use Exception;

class ExtractionException extends Exception
{
    public function __construct(string $exception)
    {
            parent::__construct($exception);
    }
}
