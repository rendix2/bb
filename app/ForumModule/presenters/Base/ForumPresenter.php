<?php

namespace App\ForumModule\Presenters\Base;

use App\Authorization\Authorizator;
use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;
use App\Authorization\Scopes\User;
use App\Models\Manager;
use App\Models\ThankManager;
use Exception;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @package App\ForumModule\Presenters\Base
 */
abstract class ForumPresenter extends Presenter
{

    
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

    private Translator $translator;

    private Manager $manager;

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

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $user = $this->getUser();
        
        $user->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
             
        parent::checkRequirements($element);
    }

    public function startup()
    {
        parent::startup();

        //$this->em->getRepository()

        //$this->template->pm_count = $this->pmManager->getCountSent();
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->getTemplate()->setTranslator($this->translator);
    }

    protected function getLoggedInUser(): User
    {
        $identity = new Identity($this->getUser()->id, $this->getUser()->roles);
        
        return new User($identity);
    }

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
