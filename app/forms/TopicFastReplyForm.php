<?php

namespace App\Forms;

use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\TopicRepository;
use App\Model\Repository\UserRepository;
use App\Models\PostFacade;
use App\Presenters\Base\BasePresenter;
use App\Services\TranslatorFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\IRequest;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of TopicFastReplyForm
 *
 * @author rendix2
 * @package App\Forms
 */
class TopicFastReplyForm extends Control
{

    public function __construct(
        private readonly TranslatorFactory $translatorFactory,
        private readonly User              $user,
        private readonly PostFacade        $postFacade,
        private readonly IRequest          $request,

        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository    $forumRepository,
        private readonly TopicRepository    $topicRepository,

        private readonly UserRepository     $userRepository,
    ) {
    }

    public function render(): void
    {
        $this['fastReply']->render();
    }

    protected function createComponentFastReply(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translatorFactory->getForumTranslator());

        $form->addGroup('Fast reply');
        $form->addTextArea('text');

        $form->addSubmit('send', 'Send');

        $form->onSuccess[] = [$this, 'fastReplySuccess'];

        return $form;
    }
    
    public function fastReplySuccess(Form $form, ArrayHash $values)
    {
        $category_id = $this->presenter->getParameter('category_id');
        $forum_id    = $this->presenter->getParameter('forum_id');
        $topic_id    = $this->presenter->getParameter('topic_id');
        $page        = $this->presenter->getParameter('page');
        $user_id     = $this->user->id;

        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id,
                ]
            );

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        $topicEntity = $this->topicRepository
            ->findOneBy(
                [
                    'id' => $topic_id,
                ]
            );

        $userEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $user_id,
                ]
            );

        $postEntity = new \App\Model\Entity\PostEntity();
        $postEntity->user = $userEntity;
        $postEntity->category = $categoryEntity;
        $postEntity->forum = $forumEntity;
        $postEntity->topic = $topicEntity;
        $postEntity->title = null;
        $postEntity->text = $values->text;
        $postEntity->addIpAddress = $this->request->getRemoteAddress();

        $res = $this->postFacade->add($postEntity);

        if ($res) {
            $this->presenter->flashMessage('Post was added.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        }

        $this->presenter->redirect('Topic:default', $category_id, $forum_id, $topic_id, $page);
    }
}
