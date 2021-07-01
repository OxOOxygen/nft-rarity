<?php

namespace App\Document;

use App\Entity\Attribute as AttributeEntity;

class Attribute implements Document
{
    private string $traitType;
    private string $value;

    private function __construct(string $traitType, string $value)
    {
        $this->traitType = $traitType;
        $this->value = $value;
    }

    public static function fromEntity(object $entity): Document
    {
       if (!$entity instanceof AttributeEntity) {
           throw WrongEntityException::createFromObject($entity);
       }

        return new self($entity->getType(), $entity->getValue());
    }

    public static function fromArray(array $document): Document
    {
        return new self($document['trait_type'], $document['value']);
    }

    public function toArray(): array
    {
        return [
            'trait_type' => $this->getTraitType(),
            'value' => $this->getValue(),
        ];
    }

    public function getTraitType(): string
    {
        return $this->traitType;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}