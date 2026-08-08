<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\LanguageEntity;
use App\Model\Entity\UserEntity;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of LanguagePresenter
 *
 * @author rendix2
 * @package App\AdminModule\Presenters
 */
class LanguagePresenter extends AdminPresenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly Translator $translator,
    )
    {
        parent::__construct();
    }

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

    public function actionDefault(int $page = 1): void
    {
        $items = $this->getManager()->getAllFluent();

        $this->gf->applyWhere($items);
        $this->gf->applyOrderBy($items);

        $paginator = new PaginatorControl($items, 20, 5, $page);
        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage(sprintf('No %s.', $this->getTitle()), self::FLASH_MESSAGE_DANGER);
        }

        $this->template->items      = $items->fetchAll();
        $this->template->countItems = $paginator->getCount();
    }

    public function renderDefault(int $page = 1): void
    {
        $this->template->title = $this->getTitleOnDefault();
    }

    public function renderEdit($id = null): void
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Parameter $id of CrudPresenter::renderEdit($id) is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item $' . $this->getTitle() . '[' . $id . '] was not found.');
            }

            $this['editForm']->setDefaults($item);

            $this->template->item_id = $id;
            $this->template->item    = $item;
            $this->template->title   = $this->getTitleOnEdit();
        } else {
            $this->template->item_id = null;
            $this->template->title   = $this->getTitleOnAdd();
            $this->template->item    = [];

            $this['editForm']->setDefaults([]);
        }

        $languageEntity = $this->em
            ->getRepository(LanguageEntity::class)
            ->findOneBy(
                [
                    'id' => $id,
                ]
            );

        $countOfUsers = $this->em
            ->getRepository(UserEntity::class)
            ->count(
                [
                    'language' => $languageEntity,
                ]
            );

        $this->getTemplate()->countOfUsers = $countOfUsers;
    }

    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->translator);

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('lang_id', 'lang_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('lang_name', 'lang_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);
        
        return $this->gf;
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addText('lang_name', 'Language name:')
            ->setRequired();

        $form->addSubmit('Send', 'Send');

        $form->onValidate[] = [$this, 'editFormOnValidate'];
        $form->onSuccess[]  = [$this, 'editFormOnSuccess'];

        return $form;
    }

    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $id = $this->getParameter('id');

        try {
            if ($id) {
                $result = $this->getManager()->update($id, $values);
            } else {
                $result = $id = $this->getManager()->add($values);
            }

            if ($result) {
                $this->flashMessage($this->getTitle() . ' was saved.', self::FLASH_MESSAGE_SUCCESS);
            } else {
                $this->flashMessage('Nothing to save.', self::FLASH_MESSAGE_INFO);
            }
        } catch (DriverException $e) {
            $this->flashMessage(
                'There was some problem during saving into database. Form was NOT saved.',
                self::FLASH_MESSAGE_DANGER
            );

            Debugger::log($e->getMessage(), ILogger::CRITICAL);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_languages']
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',    'text' => 'menu_index'],
            1 => ['link' => 'Language:default', 'text' => 'menu_languages'],
            2 => ['link' => 'Language:edit',    'text' => 'menu_language'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
}
