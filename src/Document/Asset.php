<?php

namespace App\Document;

use App\Entity\Asset as AssetEntity;

class Asset implements Document
{
    private int $id;
    private int $projectId;
    private string $projectName;
    private string $name;

    /**
     * @var Attribute[]
     */
    private array $attributes;

    private function __construct(int $id, int $projectId, string $projectName, string $name, array $attributes)
    {
        $this->id = $id;
        $this->projectId = $projectId;
        $this->projectName = $projectName;
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public static function fromEntity(object $entity): Document
    {
        if (!$entity instanceof AssetEntity) {
            throw WrongEntityException::createFromObject($entity);
        }

        $attributes = [];

        foreach ($entity->getAttributes()->toArray() as $attribute) {
            $attributes[] = Attribute::fromEntity($attribute);
        }

        return new self(
            $entity->getId(),
            $entity->getProject()->getId(),
            $entity->getProject()->getName(),
            $entity->getName(),
            $attributes,
        );
    }

    public static function fromArray(array $document): Document
    {
        $attributes = [];

        foreach ($document['attributes'] as $attribute) {
            $attributes[] = Attribute::fromArray($attribute);
        }

        return new self(
            $document['id'],
            $document['project_id'],
            $document['project_name'],
            $document['name'],
            $attributes,
        );
    }

    public function toArray(): array
    {
        $attributes = [];

        foreach ($this->getAttributes() as $attribute) {
            $attributes[] = $attribute->toArray();
        }

        return [
            'id' => $this->getId(),
            'project_id' => $this->getProjectId(),
            'project_name' => $this->getProjectName(),
            'name' => $this->getName(),
            'attributes' => $attributes,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getProjectName(): string
    {
        return $this->projectName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}