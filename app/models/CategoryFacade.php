<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Models\Entity\CategoryEntity;
use Nette\Utils\ArrayHash;

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
     * @param CategoryManager $categoriesManager
     * @param ForumFacade       $forumFacade
     * @param ForumManager     $forumsManager
     */
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly CategoryManager        $categoriesManager,
        private readonly ForumFacade            $forumFacade,
        private readonly ForumManager           $forumsManager
    ) {
    }

    /**
     *
     * @param int $item_id
     *
     * @return bool
     */
    public function delete($item_id)
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
                
        $forums = $this->forumsManager->getFluentByCategory($item_id)->fetchAll();
        
        foreach ($forums as $forum) {
            $this->forumFacade->delete($forum->forum_id);
        }

        $categoryEntity = $this->em
            ->getRepository(\App\Model\Entity\CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $item_id,
                ]
            );

        $this->em->remove($categoryEntity);
        $this->em->flush();
    }
}
