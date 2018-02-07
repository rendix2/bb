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

            $this['editForm']->setDefaults([]);
        }
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();

        $form->setTranslator($this->getAdminTranslator());

        $form->addText('forum_name', 'Forum name:')->setRequired(true);
        $form->addText('forum_description', 'Forum description:')->setRequired(true);
        $form->addSelect('forum_category_id', 'Forum category:', $this->categoryManager->getForSelect())->setRequired(true)->setTranslator(null);
        $form->addCheckbox('forum_active', 'Forum active:');
        $form->addCheckbox('forum_thank', 'Forum thank:');

        return $this->addSubmitB($form);
    }

}
