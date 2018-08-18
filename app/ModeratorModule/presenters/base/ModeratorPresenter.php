<?php

namespace App\ModeratorModule\Presenters\Base;

use App\Authorization\Authorizator;
use App\Authorization\Scopes\Forum;
use App\Authorization\Scopes\Topic;
use App\Authorization\Scopes\User;
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
     * @var Authorizator $authorizator
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

    /**
     * @param $element
     */
    public function checkRequirements($element)
    {
        parent::checkRequirements($element);
        
        $this->getUser()->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $user  = new User();
        $admin = new User();
        $forum = new Forum();
        $topic = new Topic($user, $forum);
        
        $enabledRoles = ['admin', 'juniorAdmin', 'moderator'];

        /*
        if ( !$this->getUser()->isInRole('admin') || !$this->getUser()->isInRole('moderator')) {
            $this->error('You are not in moderator role!s');
        }
         *
         */
        $canAccess = false;
        
        foreach ($enabledRoles as $role) {
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

    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translator);
    }
}
