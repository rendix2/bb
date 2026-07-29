<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;

/**
 * Description of CategoryFacade
 *
 * @author rendix2
 * @package App\Models
 */
#[\JetBrains\PhpStorm\Deprecated]
class CategoryFacade
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumFacade            $forumFacade,

        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository $forumRepository,
    ) {
    }

    public function delete(int $item_id): void
    {
        $subCategories = $this->categoryRepository
            ->findBy(
                [
                    'parent' => $item_id,
                ]
            );
        
        foreach ($subCategories as $subCategory) {
            $this->delete($subCategory->category_id);
        }

        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $item_id,
                ]
            );

        /**
         * @var ForumEntity[] $forums
         */
        $forums = $this->forumRepository
            ->findBy(
                [
                    'category' => $categoryEntity,
                ]
            );
        
        foreach ($forums as $forum) {
            $this->forumFacade->delete($forum);
        }

        $this->em->remove($categoryEntity);
        $this->em->flush();
    }
}
