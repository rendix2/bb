<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Database\EntityManagerDecorator;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use Doctrine\DBAL\Exception as DbalException;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of CategoryPresenter
 *
 * @author rendix2
 * @package App\AdminModule\Presenters
 */
class CategoryPresenter extends AdminPresenter
{
    #[Inject]
    public ITranslator $adminTranslator;

    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository    $forumRepository,
    )
    {
        parent::__construct();
    }

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $user = $this->getUser();

        $user->getStorage()->setNamespace(self::BACK_END_NAMESPACE);

        parent::checkRequirements($element);

        if ($this->getName() !== 'Login' && !$user->isLoggedIn()) {
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    public function startup()
    {
        parent::startup();

        $this->adminTranslator = $this->translatorFactory->getAdminTranslator();
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->getTemplate()->setTranslator($this->adminTranslator);
    }

    /**
     * handle reorder
     */
    public function handleReorder()
    {
        // todo
    }

    /**
     * @param int $id
     */
    public function actionDelete(int $id)
    {
        if (!is_numeric($id)) {
            $this->error('Parameter is not numeric.');
        }

        $result = $this->getManager()->delete($id);

        if ($result) {
            $this->flashMessage('Item was deleted.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Item was not deleted.', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    public function renderDefault($page = 1): void
    {
        parent::renderDefault($page);

        $allCategories = $this->categoryRepository
            ->findAll();

        $rootCategories = array_filter($allCategories, fn($f) => $f->getParent() === null);
        
        $this->template->tree = $rootCategories;
    }

    public function renderEdit($id = null): void
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Param id is not numeric.');
            }

            $categoryEntity = $this->categoryRepository
                ->findOneBy(
                    [
                        'id' => $id,
                    ]
                );

            if ($categoryEntity === null) {
                $this->error('Item #' . $id . ' not found.');
            }

            $this['editForm']->setDefaults($categoryEntity);

            $forums = $this->forumRepository
                ->findBy(
                    [
                        'category' => $id,
                    ]
                );

            if ($forums === []) {
                $this->flashMessage('No forums in this category.', self::FLASH_MESSAGE_WARNING);
            }

            $this->template->item   = $categoryEntity;
            $this->template->title  = $this->getTitleOnEdit();
            $this->template->forums = $forums;
        } else {
            $this->template->title  = $this->getTitleOnAdd();
            $this->template->forums = [];

            $this['editForm']->setDefaults([]);
        }
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addText('category_name', 'Category name:')
            ->setRequired(true);

        $form->addSelect('category_parent_id', 'Category parent:', $this->categoryRepository->findPairs())
            ->setPrompt('-')
            ->setTranslator(null);

        $form->addCheckbox('category_active', 'Category active:');

        $form->addSubmit('Send', 'Send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[]  = [$this, 'editFormSuccess'];

        return $form;
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('category_id', 'category_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('category_name', 'category_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);

        return $this->gf;
    }
    
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_categories']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
    
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',    'text' => 'menu_index'],
            1 => ['link' => 'Category:default', 'text' => 'menu_categories'],
            2 => ['link' => 'Category:edit',    'text' => 'menu_category'],
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $id = $this->getParameter('id');

        try {
            if ($id) {
                $categoryParentEntity = $this->em
                    ->getRepository(\App\Model\Entity\CategoryEntity::class)
                    ->find($values->parent);

                $categoryEntity = $this->em
                    ->getRepository(\App\Model\Entity\CategoryEntity::class)
                    ->find($id);

                if ($categoryEntity === null) {
                    $this->flashMessage('Category not found', self::FLASH_MESSAGE_DANGER);
                    return;
                }

                $categoryEntity->name = $values->name;
                $categoryEntity->parent = $categoryParentEntity;
                $categoryEntity->active = (bool) $values->active;
                $this->em->persist($categoryEntity);
                $this->em->flush();

            } else {
                $categoryParentEntity = $this->em
                    ->getRepository(\App\Model\Entity\CategoryEntity::class)
                    ->find($values->parent);

                $categoryEntity = new \App\Model\Entity\CategoryEntity();
                $categoryEntity->name = $values->name;
                $categoryEntity->parent = $categoryParentEntity;
                $categoryEntity->active = (bool) $values->active;
                $categoryEntity->order = 0;
                
                $this->em->persist($categoryEntity);
                $this->em->flush();

                $this->flashMessage($categoryEntity->name . ' was saved.', self::FLASH_MESSAGE_SUCCESS);
                $this->redrawControl('flashes');
            }
        } catch (DbalException $e) {
            $this->flashMessage(
                'There was some problem during saving into database. Form was NOT saved.',
                self::FLASH_MESSAGE_DANGER
            );
            
            Debugger::log($e->getMessage(), ILogger::CRITICAL);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }
}
