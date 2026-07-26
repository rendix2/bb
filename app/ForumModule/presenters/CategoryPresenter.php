<?php

namespace App\UI\Forum\Category;

use App\Controls\BreadCrumbControl;
use App\Database\EntityManagerDecorator;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Models\CategoryManager;

/**
 * Description of CategoryPresenter
 *
 * @author rendix2
 * @method CategoryManager getManager()
 * @package App\ForumModule\Presenters
 */
class CategoryPresenter extends BaseForumPresenter
{
    /**
     * CategoryPresenter constructor.
     *
     * @param CategoryManager $manager
     * @param EntityManagerDecorator $em
     */
    public function __construct(
        CategoryManager $manager,
        private readonly EntityManagerDecorator $em
    )
    {
        parent::__construct($manager);
    }

    /**
     *
     * @param int $category_id
     */
    public function renderDefault(int $category_id = 0): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        //$categories = $this->getManager()->getMptt()->get_tree($category_id);

        /*
        $forums = $this->forumsManager
                ->getFluentByCategory($category_id)
                ->orderBy('forum_left', dibi::ASC)
                ->fetchAll();
        */

        $forums = $this
            ->em
            ->getRepository(ForumEntity::class)
            ->findBy(
                [
                    'category' => $categoryEntity,
                ]
            );

        if (!count($forums)) {
            $this->flashMessage('No forums in this category.', self::FLASH_MESSAGE_DANGER);
        }

        $this->template->forums = $forums;

        /*
        if ($categories) {
            $this->template->categories = $categories;
        } else {
            $this->template->categories = [];
            
            $this->flashMessage('No subcategories.', self::FLASH_MESSAGE_DANGER);
        }
        */
    }
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbCategory(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_category']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
