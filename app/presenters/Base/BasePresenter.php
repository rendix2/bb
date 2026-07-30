<?php

namespace App\Presenters\Base;

use App\Authorization\Authorizator;
use App\Controls\MenuControl;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
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
    const string FLASH_MESSAGE_SUCCESS = 'success';
    
    const string FLASH_MESSAGE_DANGER = 'danger';
    
    const string FLASH_MESSAGE_WARNING = 'warning';
    
    const string FLASH_MESSAGE_INFO = 'info';
    
    const string BACK_END_NAMESPACE = 'backend';
    
    const string FRONT_END_NAMESPACE = 'frontend';
    
    /**
     * @var string
     */
    const string MODERATOR_END_SPACE = 'moderator';

    #[Nette\DI\Attributes\Inject]
    public TranslatorFactory $translatorFactory;

    #[Nette\DI\Attributes\Inject]
    public EntityManagerDecorator $aem;


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

    private function banUser(): void
    {
        $bans = $this->aem
            ->getRepository(BanEntity::class)
            ->findAll();

        $user     = $this->getUser();
        $identity = $user->getIdentity();
        
        // if not main admin or role is not admin, so you can not ban admin, if some problem....
        if ($user->getId() !== 1 || !in_array(Authorizator::ROLES[5], $user->getRoles(), true)) {
            foreach ($bans as $ban) {
                if ($identity && $user->isLoggedIn()) {
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
