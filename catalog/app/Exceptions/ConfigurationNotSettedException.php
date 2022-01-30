<?php

namespace App\Exceptions;

use Exception;

class ConfigurationNotSettedException extends Exception
{
    public function __construct(string $field, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->defineMessage($field);
    }

    private function defineMessage($field)
    {
        $this->message = "The field {$field} was not implemented";
    }

}
