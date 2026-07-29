<?php

namespace App\ForumModule\Presenters\Base;

use App\Authorization\Authorizator;
use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;
use App\Authorization\Scopes\User;
use App\Models\Manager;
use App\Models\ModeratorManager;
use App\Models\ThankManager;
use App\Models\Traits\ForumsTrait;
use App\Models\Traits\PostTrait;
use App\Models\Traits\TopicsTrait;
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
     * Translator
     *
     * @var ITranslator $forumTranslator
     */
    private ITranslator $translator;

    /**
     * @var Manager $manager
     */
    private Manager $manager;

    /**
     * ForumPresenter constructor.
     *
     * @param Manager $manager
     */
    public function __construct(
        Manager $manager,
    )
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
