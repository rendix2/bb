<?php

namespace App\Presenters\Base;

use App\Authorization\Authorizator;
use App\Controls\BootstrapForm;
use App\Controls\MenuControl;
use App\Models\BanManager;
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
    


    #[Nette\DI\Attributes\Inject]
    public BanManager $banManager;
      
    /**
     * @var BootstrapForm $bootStrapForm
     */
    private BootstrapForm $bootstrapForm;

    #[Nette\DI\Attributes\Inject]
    public TranslatorFactory $translatorFactory;

    /**
     * BasePresenter constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->bootstrapForm = BootstrapForm::create();
    }

    /**
     * beforeRender function
     */
    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->template->dir_separator = DIRECTORY_SEPARATOR;
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
    
    public function getBootstrapForm(): BootstrapForm
    {
        return $this->bootstrapForm;
    }

    private function banUser(): void
    {
        $bans     = $this->banManager->getAllCached();
        $user     = $this->getUser();
        $identity = $user->getIdentity();
        
        
        // if not main admin or role is not admin, so you can not ban admin, if some problem....
        if ($user->id !== 1 || !in_array(Authorizator::ROLES[5], $user->roles, true)) {
            foreach ($bans as $ban) {
                if ($identity && $user->loggedIn) {
                    if ($ban->ban_email === $identity->getData()['user_email'] || $ban->ban_user_name === $identity->getData()['user_name']) {
                        $this->error('Banned', IResponse::S403_Forbidden);
                    }
                }

                if ($ban->ban_ip === $this->getHttpRequest()->getRemoteAddress()) {
                    $this->error('Banned', IResponse::S403_Forbidden);
                }
            }
        }
    }

    protected function checkLoggedIn(): bool
    {
        $identity = $this->getUser()->identity;
        
        if (!$identity) {
            return false;
        }
        
        return $this->getUser()->loggedIn;
    }

    protected function checkUserLoggedIn(): bool
    {
        return $this->checkAdminLoggedIn() && !in_array('guest', $this->user->roles, true);
    }

    protected function checkJuniorAdminLoggedIn(): bool
    {
        return $this->checkLoggedIn() && $this->getUser()->isInRole('juniorAdmin');
    }

    protected function checkAdminLoggedIn(): bool
    {
        return $this->checkLoggedIn() && $this->getUser()->isInRole('admin');
    }

    protected function createComponentMenuAdmin(): MenuControl
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
            12 => ['presenter' => ':Admin:Smileys:',  'title' => 'menu_smileys'],
            13 => ['presenter' => ':Admin:File:',     'title' => 'menu_files'],
            /*12 => ['presenter' => ':Admin:Config:Index:default', 'title' => 'menu_config',
                'submenu' => [0 => ['presenter' => ':Admin:Config:Database:dumps', 'title' => 'menu_database']]
            ],*/
        ];

        $rightMenu = [
            0 => ['presenter' => ':Admin:Index:logout', 'title' => 'logout'],
            1 => ['presenter' => ':Forum:Index:default', 'title' => 'menu_forum'],
        ];

        return new MenuControl($this->translatorFactory->getAdminTranslator(), $leftMenu, $rightMenu);
    }
}
