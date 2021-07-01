<?php

namespace App\Document;

interface Document
{
    /**
     * @return static
     * @throws WrongEntityException
     */
    public static function fromEntity(object $entity): Document;

    /**
     * @return static
     */
    public static function fromArray(array $document): Document;
    public function toArray(): array;
}