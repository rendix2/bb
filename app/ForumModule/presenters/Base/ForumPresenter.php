<?php

namespace App\ForumModule\Presenters\Base;

use App\Authorizator;
use App\Controls\BootstrapForm;
use App\Models\Manager;
use App\Models\PmManager;
use App\Models\ModeratorsManager;
use App\Models\ThanksManager;
use App\Presenters\Base\AuthenticatedPresenter;
use Nette\Localization\ITranslator;
use App\Authorization\Authorizator as Aauthorizator;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 */
abstract class ForumPresenter extends AuthenticatedPresenter
{
    
    use \App\Models\Traits\PostTrait;
    use \App\Models\Traits\TopicsTrait;    
    use \App\Models\Traits\ForumsTrait;
    
    
    /**
     *
     * @var ModeratorsManager $moderators
     * @inject
     */
    public $moderators;
    
    /**
     *
     * @var ThanksManager $thanksManager
     * @inject
     */
    public $thanksManager;


    /**
     * @var Aauthorizator $authorizator
     * @inject 
     */
    public $aauthorizator;
    
    /**
     * @var \App\Models\Users2GroupsManager $users2GroupsManager
     * @inject
     */
    public $users2GroupsManager;
    
    /**
     *
     * @var \App\Models\Users2ForumsManager $users2ForumsManager
     * @inject
     */
    public $users2ForumsManager;


    /**
     * Translator
     *
     * @var ITranslator $forumTranslator
     */
    private $forumTranslator;

    /**
     * @var Authorizator $authorizator
     * @inject
     */
    public $authorizator;
    
    /**
     * @var PmManager $pmManager
     * @inject
     */
    public $pmManager;

    /**
     * @var Manager $manager
     */
    private $manager;

    /**
     * ForumPresenter constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();
        
        $this->manager = $manager;
    }
    
    public function __destruct() {
        $this->forumTranslator = null;
        $this->authorizator    = null;
        $this->pmManager       = null;
        $this->manager         = null;
        
        parent::__destruct();
    }

    /**
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return ITranslator
     */
    public function getForumTranslator()
    {
        return $this->forumTranslator;
    }
    
    /**
     *
     * @return BootstrapForm
     */
    public function createBootstrapForm()
    {
        $bf = BootstrapForm::create();
        $bf->setTranslator($this->getForumTranslator());
        
        return $bf;
    }

    /**
     *
     * @return BootstrapForm
     */
    public function getBootstrapForm()
    {
        $bf = parent::getBootstrapForm();
        $bf->setTranslator($this->getForumTranslator());
        
        return $bf;
    }

    /**
     * @param $element
     */
    public function checkRequirements($element)
    {
        $user = $this->getUser();
        
        $user->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
             
        parent::checkRequirements($element);
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $this->forumTranslator = $this->translatorFactory->forumTranslatorFactory();
        //$this->getUser()->setAuthorizator($this->authorizator->getAcl());
        //$this->user->setAuthorizator($this->aauthorizator);
        
        $this->template->pm_count = $this->pmManager->getCountSent();
    }

    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();
               
        //$post = $this->loadTopic(20);

        //$this->requireAccess($post, \App\Authorization\Scopes\Topic::ACTION_EDIT);

        $this->template->setTranslator($this->forumTranslator);
    }
    
    protected function getLoggedInUser()
    {        
        $identity = new \App\Authorization\Identity($this->user->id, $this->user->roles);
        
        return new \App\Authorization\Scopes\User($identity);
    }
    
    protected function loadCategory($id)
    {
        return new \App\Authorization\Scopes\Category();
    }
    
    protected function loadForum(\App\Models\Entity\Forum $forum)
    {        
        $moderators = $this->moderators->getAllByRight($forum->getForum_id());
        $moderatorsI = [];
        
        foreach ($moderators as $moderator) {
            $moderatorIdentity = new \App\Authorization\Identity($moderator->user_id, \App\Authorization\Scopes\Forum::ROLE_MODERATOR);
            $moderatorUser     = new \App\Authorization\Scopes\User($moderatorIdentity);
            
            $moderatorsI[] = $moderatorUser;
        }
                
        return new \App\Authorization\Scopes\Forum($forum, $moderatorsI, $this->users2GroupsManager, $this->users2ForumsManager); 
    }    
    
    protected function loadTopic(\App\Models\Entity\Forum $forum, \App\Models\Entity\Topic $topic)
    {
        
        $topicIdentity = new \App\Authorization\Identity($topic->getTopic_first_user_id(), [\App\Authorization\Scopes\Topic::ROLE_AUTHOR]);        
        $topicAuthor   = new \App\Authorization\Scopes\User($topicIdentity);
        
        $thanks = $this->thanksManager->getAllByTopic($topic->getTopic_id());
        
        return new \App\Authorization\Scopes\Topic($topic, $topicAuthor, $this->loadForum($forum), $thanks);
    }    
    
    protected function loadPost(\App\Models\Entity\Forum $forumEntity, \App\Models\Entity\Topic $topicEntity, \App\Models\Entity\Post $postEntity)
    {
        $postIdentity  = new \App\Authorization\Identity($postEntity->getPost_user_id(), [\App\Authorization\Scopes\Post::ROLE_AUTHOR]);        
        $postAuthor    = new \App\Authorization\Scopes\User($postIdentity);
                        
        return new \App\Authorization\Scopes\Post($postEntity, $this->loadTopic($forumEntity, $topicEntity), $topicEntity);
    }
        
    protected function requireAccess(\App\Authorization\IAuthorizationScope $scope, array $action)
    {
        if (!$this->isAllowed($scope, $action)) {
            throw new \Exception();
        }
    }

    protected function isAllowed(\App\Authorization\IAuthorizationScope $scope, array $action)
    {
        return $this->aauthorizator->isAllowed($this->getLoggedInUser()->getIdentity(), $scope, $action);
    }        
}
