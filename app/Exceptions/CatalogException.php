<?php

namespace App\Exceptions;

use Exception;

class CatalogException extends Exception
{
    public function __construct(string $message, int $status = 502)
    {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->getCode() >= 400 && $this->getCode() <= 599
            ? $this->getCode()
            : 502;
    }
}
