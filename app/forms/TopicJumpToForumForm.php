<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\Models\ForumsManager;
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
     * @var ForumsManager $forumManager
     */
    private $forumManager;
    
    /**
     *
     * @var ITranslator $translator
     */
    private $translator;

    /**
     * JumpToForumControl constructor.
     *
     * @param ForumsManager                   $forumManager
     * @param \Nette\Localization\ITranslator $translator
     */
    public function __construct(
        ForumsManager $forumManager,
        ITranslator   $translator
    ) {
        parent::__construct();

        $this->forumManager = $forumManager;
        $this->translator   = $translator;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->forumManager = null;
        $this->translator   = null;
    }

    /**
     * render jump to forum
     */
    public function render()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $template = $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'topicJumpToForum.latte');
        
        $template->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentJumpToForum()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translator);

        $form->addSelect('forum_id', null, $this->forumManager->getAllPairsCached('forum_name'))->setTranslator();
        $form->addSubmit('send', 'Redirect');

        $form->onSuccess[] = [$this, 'jumpToForumSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function jumpToForumSuccess(Form $form, ArrayHash $values)
    {
        $forum = $this->forumManager->getById($values->forum_id);

        $this->presenter
            ->redirect(
                ':Forum:Forum:default',
                $forum->forum_category_id,
                $values->forum_id
            );
    }
}
