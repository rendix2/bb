<?php declare(strict_types=1);

namespace App\services;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;

class BreadcrumbService
{

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository $forumRepository,
    ) {
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function getCategoryBreadCrumb(int $categoryId): array
    {
        $category = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $categoryId,
                ],
            );

        if ($category === null) {
            return [];
        }

        $breadcrumbs = [];
        $current = $category;

        while ($current !== null) {
            array_unshift($breadcrumbs, [
                'link' => ':Forum:Category:default',
                'params' => ['category_id' => $current->id],
                'text' => $current->name,
                't' => 0,
            ]);

            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    /**
     * @param int $forumId
     * @return ForumEntity[]
     */
    public function getForumBreadCrumb(int $forumId): array
    {
        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forumId,
                ],
            );

        if ($forumEntity === null) {
            return [];
        }

        $breadcrumbs = [];
        $current = $forumEntity;

        while ($current !== null) {
            array_unshift($breadcrumbs, [
                'link' => 'Forum:default',
                'params' => [
                    'category_id' => $current->category->id,
                    'forum_id' => $current->id,
                ],
                'text' => $current->name,
                't' => 0,
            ]);

            $current = $current->parent;
        }

        return $breadcrumbs;
    }

}
