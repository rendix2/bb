<?php

namespace App\AdminModule\Presenters;

/**
 * Description of ForumPresenter
 *
 * @author rendi
 * @method \App\Models\ForumsManager getManager()
 */
class ForumPresenter extends Base\AdminPresenter {

    private $categoryManager;
    
    private $userManager;
    
    private $topicManager;

    public function __construct(\App\Models\ForumsManager $manager) {
        parent::__construct($manager);
    }

    public function injectCategoryManager(\App\Models\CategoriesManager $categoryManager) {
        $this->categoryManager = $categoryManager;
    }
    
    public function injectUserManager(\App\Models\UsersManager $userManager){
        $this->userManager = $userManager;
    }
    
    public function injectTopicManager(\App\Models\TopicsManager $topicManager){
        $this->topicManager = $topicManager;
    }

    public function renderEdit($id = null) {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Param id is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item #' . $id . ' not found.');
            }

            $this['editForm']->setDefaults($item);

            $subForums = $this->getManager()->createForums($this->getManager()->getForumsByForumParentId($id), intval($id));

            if (!$subForums) {
                $this->flashMessage('No subforums.', self::FLASH_MESSAGE_WARNING);
            }

            $lastTopic = $this->topicManager->getById($item->forum_last_topic_id);
            
            if ( !$lastTopic ){
                $this->flashMessage('No last topic', self::FLASH_MESSAGE_WARNING);               
            }
            
            $this->template->topicData = $lastTopic;    
            $this->template->userData = $this->userManager->getById($item->forum_last_post_user_id);
            $this->template->item = $item;
            $this->template->title = $this->getTitleOnEdit();
            $this->template->forums = $subForums;           
        } else {
            $this->template->title = $this->getTitleOnAdd();
            $this->template->forums = [];
            
//            $this->template->item = \Nette\Utils\ArrayHash::from(['forum_topic_count' =>null, 'forum_last_post_id' => null, 'forum_last_post_user_id' => null]);
//            $this->template->topicData = [];   
//            $this->template->userData = \Nette\Utils\ArrayHash::from(['user_name' => null]);
            $this['editForm']->setDefaults([]);
        }
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();

        $form->setTranslator($this->getAdminTranslator());

        $form->addGroup('forum');
        $form->addText('forum_name', 'Forum name:')->setRequired(true);
        $form->addText('forum_description', 'Forum description:')->setRequired(true);
        $form->addSelect('forum_category_id', 'Forum category:', $this->categoryManager->getForSelect())->setRequired(true)->setTranslator(null);
        $form->addCheckbox('forum_active', 'Forum active:');
        
        $form->addGroup('user');
        $form->addCheckbox('forum_thank', 'Forum thank:');
        $form->addCheckbox('forum_post_add', 'Forum add post:');
        $form->addCheckbox('forum_post_delete', 'Forum post delete:');
        $form->addCheckbox('forum_post_update', 'Forum post update:');
        $form->addCheckbox('forum_topic_add', 'Forum topic add:');
        $form->addCheckbox('forum_topic_update', 'Forum topic update:');
        $form->addCheckbox('forum_topic_delete', 'Forum delete topic:');

        return $this->addSubmitB($form);
    }
}
