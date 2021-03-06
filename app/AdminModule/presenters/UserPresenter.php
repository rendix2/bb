<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Authorization\Authorizator;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Forms\UserChangePasswordForm;
use App\Forms\UserDeleteAvatarForm;
use App\Forms\UserForumsForm;
use App\Forms\UserGroupsForm;
use App\Forms\UserModeratorForm;
use App\Models\ForumsManager;
use App\Models\GroupsManager;
use App\Models\LanguagesManager;
use App\Models\ModeratorsManager;
use App\Models\RanksManager;
use App\Models\Users2ForumsManager;
use App\Models\Users2GroupsManager;
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
    private static $active = [0 => 'Not active', 1 => 'Active'];

    /**
     *
     * @var ForumsManager $forumsManager
     * @inject
     */
    public $forumsManager;

    /**
     * @var LanguagesManager $languagesManager
     * @inject
     */
    public $languagesManager;
    
    /**
     * @var Users2GroupsManager $group2User
     * @inject
     */
    public $group2UserManager;
    
    /**
     * @var GroupsManager $groupManager
     * @inject
     */
    public $groupsManager;
    
    /**
     * @var Users2ForumsManager $users2Forums
     * @inject
     */
    public $users2ForumsManager;
    
    /**
     * @var Avatars $avatar
     * @inject
     */
    public $avatars;
    
    /**
     *
     * @var Ranks $rank
     * @inject
     */
    public $ranks;

    /**
     * moderators manager
     *
     * @var ModeratorsManager $moderatorsManager
     * @inject
     */
    public $moderatorsManager;

    /**
     *
     * @var ChangePasswordFactory $changePasswordFactory
     * @inject
     */
    public $changePasswordFactory;
    
    /**
     *
     * @var DeleteAvatarFactory $deleteAvatarFactory
     * @inject
     */
    public $deleteAvatarFactory;

    /**
     * @var RanksManager $ranksManager
     * @inject
     */
    public $ranksManager;

    /**
     * UserPresenter constructor.
     *
     * @param UsersManager $manager
     */
    public function __construct(UsersManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * UserPresenter destructor.
     */
    public function __destruct()
    {
        $this->forumsManager         = null;
        $this->languagesManager      = null;
        $this->group2UserManager     = null;
        $this->groupsManager         = null;
        $this->users2ForumsManager   = null;
        $this->avatars               = null;
        $this->ranks                 = null;
        $this->moderatorsManager     = null;
        $this->changePasswordFactory = null;
        $this->deleteAvatarFactory   = null;
        
        parent::__destruct();
    }

    /**
     * @param int $page
     */
    public function renderDefault($page = 1)
    {
        parent::renderDefault($page);
        
        $this->template->roles = Authorizator::ROLES;
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);
        
        if (!$id) {
            $this[self::FORM_NAME]->setDefaults(['user_role_id' => 2]);
        }
        
        $this->template->avatarsDir = $this->avatars->getTemplateDir();
        $this->template->ranksDir   = $this->ranks->getTemplateDir();
    }
    
    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();

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

        return $this->addSubmitB($form);
    }

    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter()
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
    protected function createComponentChangePasswordControl()
    {
        return $this->changePasswordFactory->getAdmin();
    }
    
    /**
     *
     * @return UserGroupsForm
     */
    protected function createComponentGroupForm()
    {
        return new UserGroupsForm(
            $this->groupsManager,
            $this->group2UserManager,
            $this->getTranslator()
        );
    }
    
    /**
     *
     * @return UserForumsForm
     */
    protected function createComponentForumsForm()
    {
        return new UserForumsForm(
            $this->forumsManager,
            $this->users2ForumsManager,
            $this->getTranslator()
        );
    }

    /**
     * @return UserDeleteAvatarForm
     */
    protected function createComponentDeleteAvatar()
    {
        return $this->deleteAvatarFactory->getAdmin();
    }

    /**
     * @return UserModeratorForm
     */
    protected function createComponentModeratorsForm()
    {
        return new UserModeratorForm(
            $this->forumsManager,
            $this->moderatorsManager,
            $this->getTranslator()
        );
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
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
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'User:default',  'text' => 'menu_users'],
            2 => ['link' => 'User:edit',     'text' => 'menu_user'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
