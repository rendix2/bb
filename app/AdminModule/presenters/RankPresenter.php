<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Models\RankManager;
use App\services\RankService;
use App\Settings\Ranks;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of RankPresenter
 *
 * @author rendix2
 * @method RankManager getManager()
 * @package App\AdminModule\Presenters
 */
class RankPresenter extends AdminPresenter
{
    /**
     * @var Ranks $ranks
     * @inject
     */
    public Ranks $ranks;

    public function __construct(
        RankManager $manager,

        private readonly RankService $rankService,
    )
    {
        parent::__construct($manager);
    }

    public function actionDefault(int $page = 1): void
    {
        if (isset($this['gridFilter'])) {
            $this->getComponent('gridFilter');
        }

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

    public function renderEdit(int $id = null): void
    {
        $this->template->ranksDir = $this->ranks->getTemplateDir();

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

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        
        $form->addText('rank_name', 'Rank name:')->setRequired(true);
        $form->addInteger('rank_from', 'Rank from:');
        $form->addInteger('rank_to', 'Rank to:');
        $form->addUpload('rank_file', 'Rank file:');
        $form->addCheckbox('rank_special', 'Rank special:');

        $form->addSubmit('Send', 'Send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onValidate[] = [$this, 'editFormSuccess'];

        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormValidate(Form $form, ArrayHash $values): void
    {
        if ($values->rank_special) {
            if ($values->rank_to || $values->rank_from) {
                $form->addError('Special rank have not Rank from and Rank to.');
            }
        } else {
            if (!is_numeric($values->rank_from)) {
                $form->addError('Rank from is not numeric.');
            }

            if (!is_numeric($values->rank_to)) {
                $form->addError('Rank to is not numeric.');
            }

            if ($values->rank_from === $values->rank_to) {
                $form->addError('From and to should not to be same.');
            }
        }
    }

    /**
     * @param Form      $form   form
     * @param ArrayHash $values values
     */
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $id = $this->getParameter('id');

        $move = $this->rankService->moveRank($values->rank_file, $id);

        if ($move !== RankManager::NOT_UPLOADED) {
            $values->rank_file = $move;
        } else {
            unset($values->rank_file);
        }

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

    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('rank_id', 'rank_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('rank_name', 'rank_name', GridFilter::TEXT_LIKE);
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
            1 => ['text' => 'menu_ranks']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Rank:default',  'text' => 'menu_ranks'],
            2 => ['link' => 'Rank:edit',     'text' => 'menu_rank'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
