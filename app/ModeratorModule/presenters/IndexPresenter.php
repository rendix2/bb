<?php

namespace App\ModeratorModule\Presenters;

use App\Models\PostsManager;
use App\Presenters\Base\BasePresenter;
use Nette\Localization\ITranslator;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 */
class IndexPresenter extends BasePresenter
{
    /**
     *
     * @var ITranslator $adminTranslator
     */
    private $translator;
    
    /**
     * IndexPresenter constructor.
     *
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * @param $element
     */
    public function checkRequirements($element)
    {
        $this->getUser()->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        /*
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
        /*
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
        */
        
        $this->translator = $this->translatorFactory->forumTranslatorFactory();
        $this->template->setTranslator($this->translator);
    }
}
