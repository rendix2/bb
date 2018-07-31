<?php

namespace App\AdminModule\Presenters;

use App\Authenticator;
use App\Authorizator;
use App\Controls\BootstrapForm;
use App\Forms\ChangePasswordForm;
use App\Controls\DeleteAvatarControl;
use App\Controls\GridFilter;
use App\Forms\UserChangePasswordForm;
use App\Forms\UserDeleteAvatarForm;
use App\Models\ForumsManager;
use App\Models\GroupsManager;
use App\Models\LanguagesManager;
use App\Models\ModeratorsManager;
use App\Models\Users2ForumsManager;
use App\Models\Users2GroupsManager;
use App\Models\UsersManager;
use App\Services\ChangePasswordFactory;
use App\Services\DeleteAvatarFactory;
use App\Settings\Avatars;
use App\Settings\Ranks;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of UserPresenter
 *
 * @author rendi
 */
class UserPresenter extends Base\AdminPresenter
{
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
    public $groupManager;
    
    /**
     * @var ForumsManager $forumsManager
     * @inject
     */
    public $forumsManager;
    
    /**
     * @var Users2ForumsManager $users2Forums
     * @inject
     */
    public $users2Forums;
    
    /**
     * @var Avatars $avatar
     * @inject
     */
    public $avatar;
    
    /**
     *
     * @var Ranks $rank
     * @inject
     */
    public $rank;

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
     * UserPresenter constructor.
     *
     * @param UsersManager $manager
     */
    public function __construct(UsersManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        if ($this->getAction() === 'default') {
            $this->gf->setTranslator($this->getAdminTranslator());
            
            $this->gf->addFilter('user_id', 'user_id', GridFilter::INT_EQUAL);
            $this->gf->addFilter('user_name', 'user_name', GridFilter::TEXT_LIKE);
            $this->gf->addFilter('user_post_count', 'user_post_count', GridFilter::FROM_TO_INT);
            $this->gf->addFilter('user_topic_count', 'user_topic_count', GridFilter::FROM_TO_INT);
            $this->gf->addFilter('user_thank_count', 'user_thank_count', GridFilter::FROM_TO_INT);
            $this->gf->addFilter('user_role_id', 'user_role_id', GridFilter::CHECKBOX_LIST, Authorizator::ROLES);
            $this->gf->addFilter('user_active', 'user_active', GridFilter::CHECKBOX_LIST, [0 => 'Not active', 1 =>'Active']);
            $this->gf->addFilter('user_register_time', 'user_register_time', GridFilter::DATE_TIME);
            $this->gf->addFilter('user_last_login_time', 'user_last_login_time', GridFilter::DATE_TIME);
            $this->gf->addFilter(null, null, GridFilter::NOTHING);

            $this->addComponent($this->gf, 'gridFilter');
        }
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
     * @param null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);

        $this->template->groups   = $this->groupManager->getAllCached();
        $this->template->myGroups = array_values($this->group2UserManager->getPairsByLeft($id));

        $this->template->forums   = $this->forumsManager->createForums($this->forumsManager->getAllCached(), 0);
        $this->template->myForums = array_values($this->users2Forums->getPairsByLeft($id));
        
        $this->template->myModerators = $this->moderatorsManager->getPairsByLeft($id);
        
        $this->template->avatarsDir = $this->avatar->getTemplateDir();
        $this->template->ranksDir   = $this->rank->getTemplateDir();
    }

    /**
     * @return UserChangePasswordForm
     */
    protected function createComponentChangePasswordControl()
    {
        return $this->changePasswordFactory->getAdmin();
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
        $form->addSelect('user_role_id', 'User role:', Authenticator::ROLES);
        $form->addSelect('user_lang_id', 'User language:', $this->languagesManager->getAllPairsCached('lang_name'));
        $form->addTextArea('user_signature', 'User signature:');
        //$form->addUpload('user_avatar', 'User avatar:');

        $form->addCheckbox('user_active', 'User active:');

        return $this->addSubmitB($form);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentGroupFrom()
    {
        $form = $this->createBootstrapForm();

        $form->addSubmit('send_group', 'Send');
        $form->onSuccess[] = [$this, 'groupSuccess'];

        return $form;
    }

    /**
     * @return UserDeleteAvatarForm
     */
    public function createComponentDeleteAvatar()
    {
        return $this->deleteAvatarFactory->getAdmin();
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentForumsForm()
    {
        $form = $this->createBootstrapForm();

        $form->addSubmit('send_forum', 'Send');
        $form->onSuccess[] = [$this, 'forumsSuccess'];
        return $form;
    }

    /**
     * @return BootstrapForm
     */
    public function createComponentModeratorsForm()
    {
        $form = $this->createBootstrapForm();
        
        $form->addSubmit('send_moderator', 'Send');
        $form->onSuccess[] = [$this, 'moderatorsSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function forumsSuccess(Form $form, ArrayHash $values)
    {
        $forums  = $form->getHttpData($form::DATA_TEXT, 'forums[]');
        $user_id = $this->getParameter('id');

        $this->users2Forums->addByLeft((int) $user_id, array_values($forums));
        $this->flashMessage('Forums saved.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('User:edit', $user_id);
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function moderatorsSuccess(Form $form, ArrayHash $values)
    {
        $moderators  = $form->getHttpData($form::DATA_TEXT, 'moderators[]');
        $user_id = $this->getParameter('id');

        $this->moderatorsManager->addByLeft((int) $user_id, array_values($moderators));
        $this->flashMessage('Forums saved.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('User:edit', $user_id);
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function groupSuccess(Form $form, ArrayHash $values)
    {
        $groups  = $form->getHttpData($form::DATA_TEXT, 'group[]');
        $user_id = $this->getParameter('id');

        $this->group2UserManager->addByLeft((int) $user_id, array_values($groups));
        $this->flashMessage('Groups saved.', self::FLASH_MESSAGE_SUCCESS);
        $this->redirect('User:edit', $user_id);
    }
}
