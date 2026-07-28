<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\LanguageEntity;
use App\Model\Entity\UserEntity;
use App\Models\LanguageManager;

/**
 * Description of LanguagePresenter
 *
 * @author rendix2
 * @method LanguageManager getManager()
 * @package App\AdminModule\Presenters
 */
class LanguagePresenter extends AdminPresenter
{
    /**
     * LanguagePresenter constructor.
     *
     * @param LanguageManager $manager
     */
    public function __construct(
        LanguageManager $manager,
        private readonly EntityManagerDecorator $em,
    )
    {
        parent::__construct($manager);
    }

    /**
     * @param ?int $id
     */
    public function renderEdit($id = null): void
    {
        parent::renderEdit($id);

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

    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->getTranslator());

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

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_languages']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default',    'text' => 'menu_index'],
            1 => ['link' => 'Language:default', 'text' => 'menu_languages'],
            2 => ['link' => 'Language:edit',    'text' => 'menu_language'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
