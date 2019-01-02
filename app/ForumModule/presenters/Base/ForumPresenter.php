<?php

namespace App\ForumModule\Presenters\Base;

use App\Authorization\Authorizator;
use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;
use App\Authorization\Scopes\CategoryScope;
use App\Authorization\Scopes\ForumScope;
use App\Authorization\Scopes\PostScope;
use App\Authorization\Scopes\TopicScope;
use App\Authorization\Scopes\User;
use App\Controls\BootstrapForm;
use App\Models\Entity\ForumEntity;
use App\Models\Entity\PostEntity;
use App\Models\Entity\TopicEntity;
use App\Models\Manager;
use App\Models\ModeratorsManager;
use App\Models\PmManager;
use App\Models\ThanksManager;
use App\Models\Traits\ForumsTrait;
use App\Models\Traits\PostTrait;
use App\Models\Traits\TopicsTrait;
use App\Models\Users2ForumsManager;
use App\Models\Users2GroupsManager;
use App\Presenters\Base\AuthenticatedPresenter;
use Exception;
use Nette\Localization\ITranslator;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @package App\ForumModule\Presenters\Base
 */
abstract class ForumPresenter extends AuthenticatedPresenter
{
    use PostTrait;
    use TopicsTrait;
    use ForumsTrait;

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
     * @var Authorizator $authorizator
     * @inject
     */
    public $authorizator;
    
    /**
     * @var Users2GroupsManager $users2GroupsManager
     * @inject
     */
    public $users2GroupsManager;
    
    /**
     *
     * @var Users2ForumsManager $users2ForumsManager
     * @inject
     */
    public $users2ForumsManager;

    /**
     * Translator
     *
     * @var ITranslator $forumTranslator
     */
    private $translator;
    
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
    
    /**
     * ForumPresenter destructor.
     */
    public function __destruct()
    {
        $this->translator      = null;
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
    public function getTranslator()
    {
        return $this->translator;
    }
    
    /**
     *
     * @return BootstrapForm
     */
    public function createBootstrapForm()
    {
        $bf = BootstrapForm::create();
        $bf->setTranslator($this->getTranslator());
        
        return $bf;
    }

    /**
     *
     * @return BootstrapForm
     */
    public function getBootstrapForm()
    {
        $bf = parent::getBootstrapForm();
        $bf->setTranslator($this->getTranslator());
        
        return $bf;
    }

    /**
     * @param $element
     */
    public function checkRequirements($element)
    {
        $user = $this->user;
        
        $user->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
             
        parent::checkRequirements($element);
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $this->translator = $this->translatorFactory->getForumTranslator();
        
        $this->template->pm_count = $this->pmManager->getCountSent();
    }

    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->translator);
    }

    /**
     * @return User
     */
    protected function getLoggedInUser()
    {
        $identity = new Identity($this->user->id, $this->user->roles);
        
        return new User($identity);
    }

    /**
     * @param $id
     *
     * @return CategoryScope
     */
    protected function loadCategory($id)
    {
        return new CategoryScope();
    }


    /**
     * @param ForumEntity $forum
     *
     * @return ForumScope
     */
    protected function loadForum(ForumEntity $forum)
    {
        $moderators = $this->moderators->getAllByRight($forum->getForum_id());
        $moderatorsI = [];
        
        foreach ($moderators as $moderator) {
            $moderatorIdentity = new Identity($moderator->user_id, ForumScope::ROLE_MODERATOR);
            $moderatorUser     = new User($moderatorIdentity);
            
            $moderatorsI[] = $moderatorUser;
        }
                
        return new ForumScope($forum, $moderatorsI, $this->users2GroupsManager, $this->users2ForumsManager);
    }
    
    /**
     * @param ForumEntity $forum
     * @param TopicEntity $topic
     *
     * @return TopicScope
     */
    protected function loadTopic(ForumEntity $forum, TopicEntity $topic)
    {
        $topicIdentity = new Identity($topic->getTopic_first_user_id(), [TopicScope::ROLE_AUTHOR]);
        $topicAuthor   = new User($topicIdentity);
        
        $thanks = $this->thanksManager->getAllByTopic($topic->getTopic_id());
        
        return new TopicScope($topic, $topicAuthor, $this->loadForum($forum), $thanks);
    }
    
    /**
     * @param ForumEntity $forumEntity
     * @param TopicEntity $topicEntity
     * @param PostEntity  $postEntity
     *
     * @return PostScope
     */
    protected function loadPost(ForumEntity $forumEntity, TopicEntity $topicEntity, PostEntity $postEntity)
    {
        $postIdentity  = new Identity($postEntity->getPost_user_id(), [PostScope::ROLE_AUTHOR]);
                        
        return new PostScope($postEntity, $this->loadTopic($forumEntity, $topicEntity), $topicEntity);
    }

    /**
     * @param IAuthorizationScope $scope
     * @param array               $action
     * @throws Exception
     */
    protected function requireAccess(IAuthorizationScope $scope, array $action)
    {
        if (!$this->isAllowed($scope, $action)) {
            throw new Exception();
        }
    }

    /**
     * @param IAuthorizationScope $scope
     * @param array               $action
     * @return bool
     */
    protected function isAllowed(IAuthorizationScope $scope, array $action)
    {
        return $this->authorizator->isAllowed($this->getLoggedInUser()->getIdentity(), $scope, $action);
    }
}
