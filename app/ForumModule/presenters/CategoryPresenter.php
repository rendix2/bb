<?php

namespace App\UI\Forum\Category;

use App\Controls\BreadCrumbControl;
use App\Database\EntityManagerDecorator;
use App\Model\Facade\CategoryFacade;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;

/**
 * Description of CategoryPresenter
 *
 * @author rendix2
 * @package App\ForumModule\Presenters
 */
class CategoryPresenter extends Presenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly Translator $translator,

        private readonly CategoryFacade $categoryFacade,

        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository    $forumRepository,
    )
    {
        parent::__construct();
    }

    public function renderDefault(int $category_id = 0): void
    {
        $categoryEntity = $this->categoryRepository
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

        $categories = $this->categoryRepository->findAll();

        $categories = $this->categoryFacade->createCategories($categories, 0);

        /*
        $forums = $this->forumsManager
                ->getFluentByCategory($category_id)
                ->orderBy('forum_left', dibi::ASC)
                ->fetchAll();
        */

        $forums = $this->forumRepository
            ->findBy(
                [
                    'category' => $categoryEntity,
                ]
            );

        if (!count($forums)) {
            $this->flashMessage('No forums in this category.', self::FLASH_MESSAGE_DANGER);
        }

        $this->getTemplate()->forums = $forums;
        $this->getTemplate()->categories = $categories;

        /*
        if ($categories) {
            $this->template->categories = $categories;
        } else {
            $this->template->categories = [];
            
            $this->flashMessage('No subcategories.', self::FLASH_MESSAGE_DANGER);
        }
        */
    }
    
    protected function createComponentBreadCrumbCategory(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_category']
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
}
