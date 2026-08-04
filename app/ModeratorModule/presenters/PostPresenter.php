<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\GridFilter;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\UserEntity;
use App\Model\Repository\PostRepository;
use App\Model\Repository\TopicRepository;
use App\Models\PostFacade;
use App\Models\PostsHistoryManager;
use App\Models\PostManager;
use App\Models\TopicManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of PostPresenter
 *
 * @author rendix2
 * @method PostManager getManager()
 * @package App\ModeratorModule\Presenters
 */
class PostPresenter extends ModeratorPresenter
{
    /**
     *
     * @var PostFacade $postFacade
     * @inject
     */
    public PostFacade $postFacade;
    
    /**
     *
     * @var TopicManager $topicsManager
     * @inject
     */
    public TopicManager $topicsManager;

    public function __construct(
        PostManager $manager,
        private readonly EntityManagerDecorator $em,
        private readonly TopicRepository        $topicRepository,
        private readonly PostRepository         $postRepository,
    )
    {
        parent::__construct($manager);
    }
    
    public function renderHistory(int $post_id): void
    {
        $postEntity = $this->postRepository->findOneBy(
            [
                'id' => $post_id
            ]
        );

        $this->getTemplate()->posts = $postEntity->historyPosts;
    }

    public function renderPosts(int $topic_id) : void
    {
        $posts = $this->postRepository->findByTopicId($topic_id);

        $this->getTemplate()->posts = $posts;
    }

    protected function createComponentEditForm(): BootstrapForm
    {
        $form = new BootstrapForm();

        $form->addText('title', 'Post title');
        $form->addTextArea('text', 'Post');

        $form->addCheckbox('locked', 'Locked');

        $form->addSubmit('send', 'Send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[]  = [$this, 'editFormSuccess'];

        return $form;
    }
    
    protected function createComponentGridFilter(): GridFilter
    {
        return $this->gf;
    }

    protected function createComponentChangePostAuthor(): BootstrapForm
    {
        $form = new BootstrapForm();

        $form->addText('username', 'Username');
        $form->addSubmit('send', 'Search and set');

        $form->onSuccess[] = [$this, 'changePostAuthorValidate'];
        $form->onSuccess[] = [$this, 'changePostAuthorSuccess'];

        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changePostAuthorSuccess(Form $form, ArrayHash $values): void
    {
        $userEntity = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy(
                [
                    'username' => $values->username
                ]
            );

        if ($userEntity === null)
        {
            $this->flashMessage('User was not found', self::FLASH_MESSAGE_DANGER);
        } else {
            $res = $this->getManager()->update(
                $this->getParameter('id'),
                ArrayHash::from(['post_user_id' => $userEntity->user_id])
            );

            if ($res) {
                $this->flashMessage('Post author was updated', self::FLASH_MESSAGE_SUCCESS);
            } else {
                $this->flashMessage('Post author was NOT updated', self::FLASH_MESSAGE_DANGER);
            }
        }

        $this->redirect('this');
    }

    protected function createComponentChangeTopic(): BootstrapForm
    {
        $form = new BootstrapForm();
                
        $form->addSelect('topic_id', 'Topic name:', $this->topicRepository->findPairs());
        $form->addSubmit('send', 'Change');

        $form->onSuccess[] = [$this, 'changeTopicValidate'];
        $form->onSuccess[] = [$this, 'changeTopicSuccess'];

        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changeTopicSuccess(Form $form, ArrayHash $values): void
    {
        $res = $this->postFacade->move($this->getParameter('id'), $values->topic_id);
        
        if ($res) {
            $this->flashMessage('Topic was changed', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Topic was not changed.', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->redirect('this');
    }
}
