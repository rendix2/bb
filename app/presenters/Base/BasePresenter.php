<?php

namespace App\Presenters\Base;

use App\Authorization\Authorizator;
use App\Controls\BootstrapForm;
use App\Controls\MenuControl;
use App\Models\BansManager;
use App\Services\TranslatorFactory;
use Nette;
use Nette\Http\IResponse;
use App\BBCode;

/**
 * Base presenter for all application presenters.
 *
 * @author rendix2
 * @package App\Presenters\Base
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    //use SecuredLinksPresenterTrait;
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_SUCCESS = 'success';
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_DANGER = 'danger';
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_WARNING = 'warning';
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_INFO = 'info';
    
    /**
     * @var string
     */
    const BECK_END_NAMESPACE = 'backend';
    
    /**
     * @var string
     */
    const FRONT_END_NAMESPACE = 'frontend';
    
    /**
     * @var string
     */
    const MODERATOR_END_SPACE = 'moderator';
    
    /**
     * @var BansManager $banManager
     * @inject
     */
    public $banManager;
      
    /**
     * @var BootstrapForm $bootStrapForm
     */
    private $bootstrapForm;

    /**
     * @var TranslatorFactory $translatorFactory
     * @inject
     */
    public $translatorFactory;

    /**
     * BasePresenter constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->bootstrapForm = BootstrapForm::create();
    }

    /**
     * BasePresenter destructor.
     */
    public function __destruct()
    {
        $this->banManager        = null;
        $this->bootstrapForm     = null;
        $this->translatorFactory = null;
    }

    /**
     * BasePresenter startup.
     */
    public function startup()
    {
        parent::startup();

        $this->banUser();

        $this->template->id          = $this->getParameter('id');
        $this->template->user_id     = $this->getParameter('user_id');
        $this->template->category_id = $this->getParameter('category_id');
        $this->template->forum_id    = $this->getParameter('forum_id');
        $this->template->topic_id    = $this->getParameter('topic_id');
        $this->template->post_id     = $this->getParameter('post_id');
        $this->template->page        = $this->getParameter('page');
    }
    
    /**
     * create BootstrapForm
     *
     * @return BootstrapForm
     */
    public function createBootstrapForm()
    {
        return new BootstrapForm();
    }
    
    /**
     * @return BootstrapForm
     */
    public function getBootstrapForm()
    {
        return $this->bootstrapForm;
    }
    
    /**
     * ban user
     */
    private function banUser()
    {
        $bans     = $this->banManager->getAllCached();
        $user     = $this->user;
        $identity = $user->getIdentity();
        
        
        // if not main admin or role is not admin, so you can not ban admin, if some problem....
        if ($user->id !== 1 || !in_array(Authorizator::ROLES[5], $user->roles, true)) {
            foreach ($bans as $ban) {
                if ($identity && $user->loggedIn) {
                    if ($ban->ban_email === $identity->getData()['user_email'] || $ban->ban_user_name === $identity->getData()['user_name']) {
                        $this->error('Banned', IResponse::S403_FORBIDDEN);
                    }
                }

                if ($ban->ban_ip === $this->getHttpRequest()->getRemoteAddress()) {
                    $this->error('Banned', IResponse::S403_FORBIDDEN);
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function checkLoggedIn()
    {
        $identity = $this->user->identity;
        
        if (!$identity) {
            return false;
        }
        
        return $this->user->loggedIn;
    }

    /**
     * @return bool
     */
    protected function checkUserLoggedIn()
    {
        return $this->checkAdminLoggedIn() && !in_array('guest', $this->user->roles, true);
    }

    /**
     * @return bool
     */
    protected function checkJuniorAdminLoggedIn()
    {
        return $this->checkLoggedIn() && $this->user->isInRole('juniorAdmin');
    }

    /**
     * @return bool
     */
    protected function checkAdminLoggedIn()
    {
        return $this->checkLoggedIn() && $this->user->isInRole('admin');
    }

    /**
     * @return MenuControl
     */
    protected function createComponentMenuAdmin()
    {
        $leftMenu = [
            0 =>  ['presenter' => ':Admin:Index:',    'title' => 'menu_index'],
            1 =>  ['presenter' => ':Admin:Forum:',    'title' => 'menu_forums'],
            2 =>  ['presenter' => ':Admin:Category:', 'title' => 'menu_categories'],
            3 =>  ['presenter' => ':Admin:User:',     'title' => 'menu_users'],
            4 =>  ['presenter' => ':Admin:Avatar:',   'title' => 'menu_avatar'],
            5 =>  ['presenter' => ':Admin:Email:',    'title' => 'menu_emails'],
            6 =>  ['presenter' => ':Admin:Cache:',    'title' => 'menu_cache'],
            7 =>  ['presenter' => ':Admin:Language:', 'title' => 'menu_language'],
            8 =>  ['presenter' => ':Admin:Group:',    'title' => 'menu_groups'],
            9 =>  ['presenter' => ':Admin:Rank:',     'title' => 'menu_ranks'],
            10 => ['presenter' => ':Admin:Report:',   'title' => 'menu_reports'],
            11 => ['presenter' => ':Admin:Ban:',      'title' => 'menu_bans'],
            12 => ['presenter' => ':Admin:Smilies:',  'title' => 'menu_smilies'],
            13 => ['presenter' => ':Admin:File:',     'title' => 'menu_files'],
            /*12 => ['presenter' => ':Admin:Config:Index:default', 'title' => 'menu_config',
                'submenu' => [0 => ['presenter' => ':Admin:Config:Database:dumps', 'title' => 'menu_database']]
            ],*/
        ];

        $rightMenu = [
            0 => ['presenter' => ':Admin:Index:logout', 'title' => 'logout'],
            1 => ['presenter' => ':Forum:Index:default', 'title' => 'menu_forum'],
        ];

        return new MenuControl($this->translatorFactory->createAdminTranslatorFactory(), $leftMenu, $rightMenu);
    }
}
