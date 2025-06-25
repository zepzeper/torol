<?php

namespace Torol\Exceptions;

use Exception;

class TransformationException extends Exception
{
    public function __construct(string $exception)
    {
            parent::__construct($exception);
    }
}
