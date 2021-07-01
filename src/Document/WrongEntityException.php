<?php

namespace App\Document;

use Exception;

class WrongEntityException extends Exception
{
    public static function createFromObject(object $object): self
    {
        return new self();
    }
}