<?php

namespace App\Forms;

use App\Presenters\Base\BasePresenter;
use App\Controls\BootstrapForm;
use App\Services\TranslatorFactory;
use App\Models\PostFacade;

use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Security\User;


/**
 * Description of TopicFastReplyForm
 *
 * @author rendi
 */
class TopicFastReplyForm extends \Nette\Application\UI\Control
{
    /**
     *
     * @var \App\Services\TranslatorFactory $translatorFactory
     */
    private $translatorFactory;
    
    /**
     *
     * @var \Nette\Security\User $user
     */
    private $user;
    
    /**
     *
     * @var \App\Models\PostFacade $postFacade 
     */
    private $postFacade;
    
    public function __construct(TranslatorFactory $translatorFactory, User $user, PostFacade $postFacade)
    {
        parent::__construct();
        
        $this->translatorFactory = $translatorFactory;
        $this->user              = $user;
        $this->postFacade        = $postFacade;
    }
    
    public function render()
    {
        $this['fastReply']->render();
    }

        /**
     * @return BootstrapForm
     */
    protected function createComponentFastReply()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translatorFactory->forumTranslatorFactory());

        $form->addGroup('Fast reply');
        $form->addTextArea('post_text');
        $form->addSubmit('send', 'Send');

        $form->onSuccess[] = [$this, 'fastReplySuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function fastReplySuccess(Form $form, ArrayHash $values)
    {
        $forum_id = $this->presenter->getParameter('forum_id');
        $topic_id = $this->presenter->getParameter('topic_id');
        $page     = $this->presenter->getParameter('page');

        $values->post_forum_id = $forum_id;
        $values->post_topic_id = $topic_id;
        $values->post_user_id  = $this->user->getId();

        $res = $this->postFacade->add($values);

        if ($res) {
            $this->presenter->flashMessage('Post was added.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        }

        $this->presenter->redirect('Topic:default', $forum_id, $topic_id, $page);
    }    
    
}