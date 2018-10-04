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
 * @author rendix2
 */
class TopicFastReplyForm extends \Nette\Application\UI\Control
{
    /**
     *
     * @var TranslatorFactory $translatorFactory
     */
    private $translatorFactory;
    
    /**
     *
     * @var User $user
     */
    private $user;
    
    /**
     *
     * @var PostFacade $postFacade
     */
    private $postFacade;
    
    /**
     *
     * @var \Nette\Http\IRequest $request
     */
    private $request;

    /**
     * TopicFastReplyForm constructor.
     *
     * @param TranslatorFactory $translatorFactory
     * @param User              $user
     * @param PostFacade        $postFacade
     */
    public function __construct(TranslatorFactory $translatorFactory, User $user, PostFacade $postFacade, \Nette\Http\IRequest $request)
    {
        parent::__construct();
        
        $this->translatorFactory = $translatorFactory;
        $this->user              = $user;
        $this->postFacade        = $postFacade;
        $this->request           = $request;
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
        $category_id = $this->presenter->getParameter('category_id');
        $forum_id    = $this->presenter->getParameter('forum_id');
        $topic_id    = $this->presenter->getParameter('topic_id');
        $page        = $this->presenter->getParameter('page');
        $user_id     = $this->user->getId();
        
        $post = new \App\Models\Entity\Post(
            null,
            $user_id,
            $category_id,
            $forum_id,
            $topic_id,
            '',
            $values->post_text,
            time(),
            $this->request->getRemoteAddress(),
            '',
            0,
            0,
            0,
            1
        );        

        $res = $this->postFacade->add($post);

        if ($res) {
            $this->presenter->flashMessage('Post was added.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        }

        $this->presenter->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
    }
}
