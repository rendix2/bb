<?php

namespace App\Forms;

use App\Presenters\Base\BasePresenter;
use App\Controls\BootstrapForm;
use App\Services\TranslatorFactory;
use App\Models\PostFacade;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Security\User;
use Nette\Http\IRequest;

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
     * @var IRequest $request
     */
    private $request;

    /**
     * TopicFastReplyForm constructor.
     *
     * @param TranslatorFactory $translatorFactory
     * @param User              $user
     * @param PostFacade        $postFacade
     * @param IRequest          $request
     */
    public function __construct(TranslatorFactory $translatorFactory, User $user, PostFacade $postFacade, IRequest $request)
    {
        parent::__construct();
        
        $this->translatorFactory = $translatorFactory;
        $this->user              = $user;
        $this->postFacade        = $postFacade;
        $this->request           = $request;
    }
    
    public function __destruct()
    {
        $this->translatorFactory = null;
        $this->user              = null;
        $this->postFacade        = null;
        $this->request           = null;
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
        
        $post = new \App\Models\Entity\Post();
        $post->setPost_user_id($user_id)
             ->setPost_category_id($category_id)
             ->setPost_forum_id($forum_id)
             ->setPost_topic_id($topic_id)
             ->setPost_title('')
             ->setPost_text($values->post_text)
             ->setPost_add_time(time())
             ->setPost_add_user_ip($this->request->getRemoteAddress())
             ->setPost_order(1);

        $res = $this->postFacade->add($post);

        if ($res) {
            $this->presenter->flashMessage('Post was added.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        }

        $this->presenter->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
    }
}
