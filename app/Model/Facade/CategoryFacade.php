<?php

namespace App\Model\Facade;

use App\Model\Entity\ForumEntity;

class CategoryFacade
{
    public function __construct(
    ) {
    }

    public function createCategories(array $categories, int $parentId): array
    {
        $result = [];

        foreach ($categories as $category) {
            if ($category?->parent?->id === $parentId) {
                $result[$category->id] = $category;
                $result[$category->id]['childs'] = $this->createCategories(
                    $categories,
                    $category->id
                );
            }
        }

        return $result;
    }

}
