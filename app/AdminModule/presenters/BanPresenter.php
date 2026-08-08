<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
use Contributte\Datagrid\Datagrid;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of BanPresenter
 *
 * @author rendix2
 * @package App\AdminModule\Presenters
 */
class BanPresenter extends Presenter
{

    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly Translator $translator,
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

        $this->translator = $this->translator;
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->getTemplate()->setTranslator($this->translator);
    }

    public function actionEdit(int $id): void
    {
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

    /**
     * @param int $page
     */
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

    /**
     * @param int $page
     */
    public function renderDefault(int $page = 1): void
    {
        $this->template->title = $this->getTitleOnDefault();
    }

    public function renderEdit(int $id): void
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
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
        $form->addText('ban_user_name', 'User name:');
        $form->addText('ban_email', 'User mail:');
        $form->addText('ban_ip', 'User IP:');

        $form->addSubmit('Send', 'Send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[]  = [$this, 'editFormSuccess'];

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


    protected function createComponentDataGrid(): Datagrid
    {
        $bans = $this->em
            ->getRepository(BanEntity::class);

        $grid = new Datagrid();
        $grid->setDataSource($bans);

        $grid->addColumnText('username', 'Username');

        $grid->addColumnText('email', 'Email');

        $grid->addColumnText('ip', 'IP');

        $grid->addAction('edit', 'Edit', 'Ban:Edit');

        return $grid;
    }
    
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->translator);
            
        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('ban_id', 'ban_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('ban_user_name', 'ban_user_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('ban_email', 'ban_email', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('ban_ip', 'ban_ip', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);
        
        return $this->gf;
    }
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_bans']
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Ban:default',   'text' => 'menu_bans'],
            2 => ['link' => 'Ban:default',   'text' => 'menu_ban'],
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
}
