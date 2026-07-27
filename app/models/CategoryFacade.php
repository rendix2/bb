<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;

/**
 * Description of CategoryFacade
 *
 * @author rendix2
 * @package App\Models
 */
#[\JetBrains\PhpStorm\Deprecated]
class CategoryFacade
{
    /**
     * CategoryFacade constructor.
     *
     * @param EntityManagerDecorator $em
     * @param ForumFacade $forumFacade
     */
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumFacade            $forumFacade,
    ) {
    }

    public function delete(int $item_id): void
    {
        $subCategories = $this->em
            ->getRepository(\App\Model\Entity\CategoryEntity::class)
            ->findBy(
                [
                    'parent' => $item_id,
                ]
            );
        
        foreach ($subCategories as $subCategory) {
            $this->delete($subCategory->category_id);
        }

        $categoryEntity = $this->em
            ->getRepository(\App\Model\Entity\CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $item_id,
                ]
            );

        /**
         * @var ForumEntity[] $forums
         */
        $forums = $this->em
            ->getRepository(ForumEntity::class)
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
