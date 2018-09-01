<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\PostsHistoryManager;
use App\Models\PostsManager;
use App\Models\TopicsManager;
use App\Models\UsersManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of PostPresenter
 *
 * @author rendix2
 * @method PostsManager getManager()
 */
class PostPresenter extends ModeratorPresenter
{
    /**
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;
    
    /**
     * @var TopicsManager
     * @inject
     */
    public $topicsManager;
    
    /**
     *
     * @var PostsHistoryManager $postsHistoryManager
     * @inject
     */
    public $postsHistoryManager;

    /**
     * PostPresenter constructor.
     *
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     *
     * @param int $post_id
     */
    public function renderHistory($post_id)
    {
        $this->template->posts = $this->postsHistoryManager->getJoinedByPost($post_id);
    }

    /**
     *
     */
    protected function createComponentEditForm()
    {
        $form = BootstrapForm::create();
        
        $form->addText('post_title', 'Post title:');
        $form->addTextArea('post_text', 'Post:');
        $form->addCheckbox('post_locked', 'Post locked:');
        
        return $this->addSubmitB($form);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentChangePostAuthor()
    {
        $form = BootstrapForm::create();
        
        $form->addText('user_name', 'User name:');
        $form->addSubmit('send', 'Search and set');
        $form->onSuccess[] = [$this, 'changePostAuthorSuccess'];
        
        return $form;
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentChangeTopic()
    {
        $form = BootstrapForm::create();
                
        $form->addSelect('post_topic_id', 'Topic name:', $this->topicsManager->getAllPairs('topic_name'));
        $form->addSubmit('send', 'Change');
        $form->onSuccess[] = [$this, 'changeTopicSuccess'];
        
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changePostAuthorSuccess(Form $form, ArrayHash $values)
    {
        $user = $this->usersManager->getByName($values->user_name);

        if ($user) {
            $res = $this->getManager()->update(
                $this->getParameter('id'),
                ArrayHash::from(['post_user_id' => $user->user_id])
            );
            
            if ($res) {
                $this->flashMessage('Post author was updated', self::FLASH_MESSAGE_SUCCESS);
            } else {
                $this->flashMessage('Post author was NOT updated', self::FLASH_MESSAGE_DANGER);
            }
        } else {
            $this->flashMessage('User was not found', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->redirect('this');
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changeTopicSuccess(Form $form, ArrayHash $values)
    {
        $res = $this->getManager()->update($this->getParameter('id'), $values);
        
        if ($res) {
            $this->flashMessage('Topic was changed', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Topic was not changed.', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->redirect('this');
    }
    
    public function renderPosts($topic_id)
    {
        $this->template->posts = $this->getManager()->getByTopic($topic_id)->fetchAll();
    }
}
