<?php

namespace App\Forms;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Repository\ForumRepository;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of JumpToForumControl
 *
 * @author rendix2
 * @package App\Forms
 */
class TopicJumpToForumForm extends Control
{

    /**
     *
     * @var ITranslator $translator
     */
    private ITranslator $translator;

    public function __construct(
        ITranslator   $translator,
        private readonly EntityManagerDecorator $em,
        private readonly ForumRepository        $forumRepository,
    ) {
        parent::__construct();

        $this->translator   = $translator;
    }

    /**
     * render jump to forum
     */
    public function render(): void
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->getTemplate()->setFile(__DIR__ . $sep . 'templates' . $sep . 'topicJumpToForum.latte');
        
        $template->render();
    }

    protected function createComponentJumpToForum(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addSelect('forum_id', null, $this->forumRepository->findPairs())
            ->setTranslator(null);

        $form->addSubmit('send', 'Redirect');

        $form->onSuccess[] = [$this, 'jumpToForumSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function jumpToForumSuccess(Form $form, ArrayHash $values): void
    {
        $forumEntity = $this->em
            ->getRepository(ForumEntity::class)
            ->findOneBy(
                [
                    'id' => $values->forum_id,
                ]
            );

        $this->getPresenter()
            ->redirect(
                ':Forum:Forum:default',
                $forumEntity->forum_category_id,
                $values->forum_id
            );
    }
}
