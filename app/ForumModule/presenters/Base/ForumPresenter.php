<?php

namespace App\ForumModule\Presenters\Base;

use App\Authorization\Authorizator;
use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;
use App\Authorization\Scopes\ForumScope;
use App\Authorization\Scopes\PostScope;
use App\Authorization\Scopes\TopicScope;
use App\Authorization\Scopes\User;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Models\Entity\TopicEntity;
use App\Models\Manager;
use App\Models\ModeratorManager;
use App\Models\PmManager;
use App\Models\ThankManager;
use App\Models\Traits\ForumsTrait;
use App\Models\Traits\PostTrait;
use App\Models\Traits\TopicsTrait;
use App\Models\Users2ForumsManager;
use App\Models\User2GroupManager;
use App\Presenters\Base\AuthenticatedPresenter;
use Exception;
use Nette\DI\Attributes\Inject;
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
     * @var ModeratorManager $moderators
     * @inject
     */
    public ModeratorManager $moderators;
    
    /**
     *
     * @var ThankManager $thanksManager
     * @inject
     */
    public ThankManager $thanksManager;

    /**
     * @var Authorizator $authorizator
     * @inject
     */
    public Authorizator $authorizator;
    
    /**
     * @var User2GroupManager $users2GroupsManager
     * @inject
     */
    public User2GroupManager $users2GroupsManager;
    
    /**
     *
     * @var Users2ForumsManager $users2ForumsManager
     * @inject
     */
    public Users2ForumsManager $users2ForumsManager;

    /**
     * Translator
     *
     * @var ITranslator $forumTranslator
     */
    private ITranslator $translator;
    
    /**
     * @var PmManager $pmManager
     * @inject
     */
    public PmManager $pmManager;

    /**
     * @var Manager $manager
     */
    private Manager $manager;

    #[Inject]
    private EntityManagerDecorator $em;

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

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getTranslator(): ITranslator
    {
        return $this->translator;
    }

    /**
     * @param $element
     */
    public function checkRequirements($element): void
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

        $this->translator = $this->translatorFactory->getForumTranslator();

        //$this->em->getRepository()

        //$this->template->pm_count = $this->pmManager->getCountSent();
    }

    /**
     *
     */
    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->getTemplate()->setTranslator($this->translator);
    }

    /**
     * @return User
     */
    protected function getLoggedInUser(): User
    {
        $identity = new Identity($this->getUser()->id, $this->getUser()->roles);
        
        return new User($identity);
    }

    protected function loadForum(\App\Model\Entity\ForumEntity $forum): ForumScope
    {
        $moderators = $this->moderators->getAllByRight($forum->id);
        $moderatorsI = [];
        
        foreach ($moderators as $moderator) {
            $moderatorIdentity = new Identity($moderator->user_id, ForumScope::ROLE_MODERATOR);
            $moderatorUser     = new User($moderatorIdentity);
            
            $moderatorsI[] = $moderatorUser;
        }
                
        return new ForumScope($forum, $moderatorsI, $this->users2GroupsManager, $this->users2ForumsManager);
    }

    /**
     * @param \App\Model\Entity\ForumEntity $forum
     * @param TopicEntity $topic
     *
     * @return TopicScope
     */
    protected function loadTopic(\App\Model\Entity\ForumEntity $forum, \App\Model\Entity\TopicEntity $topic): TopicScope
    {
        $topicIdentity = new Identity($topic->getTopic_first_user_id(), [TopicScope::ROLE_AUTHOR]);
        $topicAuthor   = new User($topicIdentity);
        
        $thanks = $this->thanksManager->getAllByTopic($topic->getTopic_id());
        
        return new TopicScope($topic, $topicAuthor, $this->loadForum($forum), $thanks);
    }

    /**
     * @param ForumEntity $forumEntity
     * @param \App\Model\Entity\TopicEntity $topicEntity
     * @param \App\Model\Entity\PostEntity $postEntity
     *
     * @return PostScope
     */
    protected function loadPost(\App\Model\Entity\ForumEntity $forumEntity, \App\Model\Entity\TopicEntity $topicEntity, \App\Model\Entity\PostEntity $postEntity): PostScope
    {
        $postIdentity  = new Identity($postEntity->user->id, [PostScope::ROLE_AUTHOR]);
                        
        return new PostScope($postEntity, $this->loadTopic($forumEntity, $topicEntity), $topicEntity);
    }

    /**
     * @param IAuthorizationScope $scope
     * @param array               $action
     * @throws Exception
     */
    protected function requireAccess(IAuthorizationScope $scope, array $action): void
    {
        if (!$this->isAllowed($scope, $action)) {
            throw new Exception();
        }
    }

    protected function isAllowed(IAuthorizationScope $scope, array $action): bool
    {
        return $this->authorizator->isAllowed($this->getLoggedInUser()->getIdentity(), $scope, $action);
    }
}
