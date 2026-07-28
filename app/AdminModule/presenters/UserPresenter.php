<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Authorization\Authorizator;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Forms\UserChangePasswordForm;
use App\Forms\UserDeleteAvatarForm;
use App\Forms\UserForumsForm;
use App\Forms\UserGroupsForm;
use App\Forms\UserModeratorForm;
use App\Models\LanguageManager;
use App\Models\RankManager;
use App\Models\UsersManager;
use App\Services\ChangePasswordFactory;
use App\Services\DeleteAvatarFactory;
use App\Settings\Avatars;
use App\Settings\Ranks;

/**
 * Description of UserPresenter
 *
 * @author rendix2
 * @method UsersManager getManager()
 * @package App\AdminModule\Presenters
 */
class UserPresenter extends AdminPresenter
{
    /**
     * @var array $active
     */
    private static array $active = [0 => 'Not active', 1 => 'Active'];


    /**
     * @var LanguageManager $languagesManager
     * @inject
     */
    public LanguageManager $languagesManager;
    
    /**
     * @var Avatars $avatar
     * @inject
     */
    public Avatars $avatars;
    
    /**
     *
     * @var Ranks $rank
     * @inject
     */
    public Ranks $ranks;

    /**
     *
     * @var ChangePasswordFactory $changePasswordFactory
     * @inject
     */
    public ChangePasswordFactory $changePasswordFactory;
    
    /**
     *
     * @var DeleteAvatarFactory $deleteAvatarFactory
     * @inject
     */
    public DeleteAvatarFactory $deleteAvatarFactory;

    /**
     * @var RankManager $ranksManager
     * @inject
     */
    public RankManager $ranksManager;

    /**
     * UserPresenter constructor.
     *
     * @param UsersManager $manager
     */
    public function __construct(
        private readonly UserGroupsForm    $userGroupsForm,
        private readonly UserForumsForm    $userForumsForm,
        private readonly UserModeratorForm $userModeratorForm,
        UsersManager $manager
    )
    {
        parent::__construct($manager);
    }

    /**
     * @param int $page
     */
    public function renderDefault($page = 1)
    {
        parent::renderDefault($page);
        
        $this->getTemplate()->roles = Authorizator::ROLES;
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null): void
    {
        parent::renderEdit($id);
        
        if (!$id) {
            $this[self::FORM_NAME]->setDefaults(['user_role_id' => 2]);
        }
        
        $this->getTemplate()->avatarsDir = $this->avatars->getTemplateDir();
        $this->getTemplate()->ranksDir   = $this->ranks->getTemplateDir();
    }
    
    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addGroup('user_data');
        $form->addText('user_name', 'User name:')->setRequired(true);
        $form->addEmail('user_email', 'User mail:')->setRequired(true);
        $form->addGroup('user_settings');
        $form->addSelect('user_role_id', 'User role:', Authorizator::ROLES);
        $form->addSelect('user_lang_id', 'User language:', $this->languagesManager->getAllPairsCached('lang_name'));
        $form->addTextArea('user_signature', 'User signature:');
        $form->addSelect('user_special_rank', 'User special rank:', $this->ranksManager->getAllFluent()->where('%n = %i', 'rank_special', 1)->getAllPairs('rank_name'));
        //$form->addUpload('user_avatar', 'User avatar:');

        $form->addCheckbox('user_active', 'User active:');

        $form->addSubmit('Send', 'Send');
        $form->onSuccess[]  = [$this, self::FORM_ON_SUCCESS];
        $form->onValidate[] = [$this, self::FORM_ON_VALIDATE];

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
        $this->gf->addFilter('user_id', 'user_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('user_name', 'user_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('user_post_count', 'user_post_count', GridFilter::FROM_TO_INT);
        $this->gf->addFilter('user_topic_count', 'user_topic_count', GridFilter::FROM_TO_INT);
        $this->gf->addFilter('user_thank_count', 'user_thank_count', GridFilter::FROM_TO_INT);
        $this->gf->addFilter('user_role_id', 'user_role_id', GridFilter::CHECKBOX_LIST, Authorizator::ROLES);
        $this->gf->addFilter('user_active', 'user_active', GridFilter::CHECKBOX_LIST, self::$active);
        $this->gf->addFilter('user_register_time', 'user_register_time', GridFilter::DATE_TIME);
        $this->gf->addFilter('user_last_login_time', 'user_last_login_time', GridFilter::DATE_TIME);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);
            
        return $this->gf;
    }

    /**
     * @return UserChangePasswordForm
     */
    protected function createComponentChangePasswordControl(): UserChangePasswordForm
    {
        return $this->changePasswordFactory->getAdmin();
    }
    
    /**
     *
     * @return UserGroupsForm
     */
    protected function createComponentGroupForm(): UserGroupsForm
    {
        return $this->userGroupsForm;
    }
    
    /**
     *
     * @return UserForumsForm
     */
    protected function createComponentForumsForm(): UserForumsForm
    {
        return $this->userForumsForm;
    }

    /**
     * @return UserDeleteAvatarForm
     */
    protected function createComponentDeleteAvatar(): UserDeleteAvatarForm
    {
        return $this->deleteAvatarFactory->getAdmin();
    }

    /**
     * @return UserModeratorForm
     */
    protected function createComponentModeratorsForm(): UserModeratorForm
    {
        return $this->userModeratorForm;
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_users']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:default',  'text' => 'menu_users'],
            2 => ['link' => 'User:edit',     'text' => 'menu_user'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
