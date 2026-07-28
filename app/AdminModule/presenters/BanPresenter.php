<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
use App\Models\BanManager;
use Contributte\Datagrid\Datagrid;

/**
 * Description of BanPresenter
 *
 * @author rendix2
 * @method BanManager getManager()
 * @package App\AdminModule\Presenters
 */
class BanPresenter extends AdminPresenter
{
    /**
     * BanPresenter constructor.
     *
     * @param BanManager $manager
     */
    public function __construct(
        private readonly EntityManagerDecorator $em,
        BanManager $manager
    )
    {
        parent::__construct($manager);
    }

    public function actionEdit(int $id): void
    {
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

    protected function createComponentBanDataGrid(): Datagrid
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
    
    /**
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getTranslator());
            
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
        
        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
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
        
        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
