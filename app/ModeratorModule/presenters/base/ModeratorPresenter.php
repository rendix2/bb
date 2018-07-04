<?php

namespace App\ModeratorModule\Presenters\Base;

use App\Presenters\crud\CrudPresenter;
use Nette\Localization\ITranslator;

/**
 * Description of ModeratorPresenter
 *
 * @author rendi
 */
abstract class ModeratorPresenter extends CrudPresenter
{
    /**
     *
     * @var ITranslator $adminTranslator
     */
    private $translator;

    /**
     * @var \App\Authorization\Authorizator $authorizator
     * @inject
     */
    public $authorizator;

    /**
     * @return ITranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    public function startup()
    {
        parent::startup();
        
        
        $user  = new \App\Authorization\Scopes\User();
        $admin = new \App\Authorization\Scopes\User();        
        $forum = new \App\Authorization\Scopes\Forum();        
        $topic = new \App\Authorization\Scopes\Topic($user, $forum);

        
        \Tracy\Debugger::barDump($this->authorizator->isAllowed($admin->getIdentity(), $topic, \App\Authorization\Scopes\Topic::ACTION_ADD));
        
        $enabledRoles = ['admin', 'juniorAdmin', 'moderator'];

        /*
        if ( !$this->getUser()->isInRole('admin') || !$this->getUser()->isInRole('moderator')) {
            $this->error('You are not in moderator role!s');
        }
         *
         */
        $canAccess = false;
        
        foreach ( $enabledRoles as $role ){
            if (in_array($role, $this->getUser()->getRoles(), true)) {
                $canAccess = true;
                break;
            }
        }
        
        if (!$canAccess) {
             $this->error('You are not in moderator role!s');
        }
        
        $this->translator = $this->translatorFactory->forumTranslatorFactory();
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translator);
    }
}
