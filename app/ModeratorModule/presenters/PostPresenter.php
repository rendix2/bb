<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\PostsManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of PostPresenter
 *
 * @author rendi
 */
class PostPresenter extends \App\ModeratorModule\Presenters\Base\ModeratorPresenter
{
    /**
     * @var \App\Models\UsersManager $usersManager
     * @inject
     */
    public $usersManager;
    
    /**
     * @var \App\Models\TopicsManager
     * @inject
     */
    public $topicsManager;
    
    /**
     *
     * @var \App\Models\PostsHistoryManager $postsHistoryManager
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
        $form = new BootstrapForm();
        
        $form->addText('post_title', 'Post title:');
        $form->addTextArea('post_text', 'Post:');
        $form->addCheckbox('post_locked', 'Post locked:');
        
        return $this->addSubmitB($form);
    }
    
    protected function createComponentChangePostAuthor()
    {
        $form = BootstrapForm::create();
        
        $form->addText('user_name', 'User name:');
        $form->addSubmit('send', 'Search and set');
        $form->onSuccess[] = [$this, 'changePostAuthorSuccess'];
        
        return $form;  
    }
    
    protected function createComponentChangeTopic()
    {
        $form = BootstrapForm::create();
                
        $form->addSelect('post_topic_id', 'Topic name:', $this->topicsManager->getAllPairs('topic_name'));
        $form->addSubmit('send', 'Change');
        $form->onSuccess[] = [$this, 'changeTopicSuccess'];
        
        return $form;
    }

    public function changePostAuthorSuccess(Form $form, ArrayHash $values)
    {
        $user = $this->usersManager->getByName($values->user_name);

        if ($user) {
            $res = $this->getManager()->update($this->getParameter('id'), ArrayHash::from(['post_user_id' => $user->user_id]));
            
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
}
