<?php

namespace App\ModeratorModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\PostFacade;
use App\Models\PostsHistoryManager;
use App\Models\PostsManager;
use App\Models\TopicsManager;
use App\ModeratorModule\Presenters\Base\ModeratorPresenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of PostPresenter
 *
 * @author rendix2
 * @method PostsManager getManager()
 * @package App\ModeratorModule\Presenters
 */
class PostPresenter extends ModeratorPresenter
{
    /**
     *
     * @var PostsHistoryManager $postsHistoryManager
     * @inject
     */
    public $postsHistoryManager;
            
    /**
     *
     * @var PostFacade $postFacade
     * @inject
     */
    public $postFacade;
    
    /**
     *
     * @var TopicsManager $topicsManager
     * @inject
     */
    public $topicsManager;

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
        $this->template->posts = $this->postsHistoryManager->getByPost($post_id);
    }

    /**
     * @return BootstrapForm
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
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        return $this->gf;
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
        $res = $this->postFacade->move($this->getParameter('id'), $values->post_topic_id);
        
        if ($res) {
            $this->flashMessage('Topic was changed', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Topic was not changed.', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->redirect('this');
    }

    /**
     * @param int $topic_id
     */
    public function renderPosts($topic_id)
    {
        $this->template->posts = $this->getManager()->getFluentByTopic($topic_id)->fetchAll();
    }
}
