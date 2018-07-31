<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\CategoriesManager;
use App\Models\ForumsManager;
use App\Models\PostsManager;
use App\Models\TopicsManager;
use App\Models\UsersManager;
use App\Models\ModeratorsManager;

/**
 * Description of ForumPresenter
 *
 * @author rendi
 * @method ForumsManager getManager()
 */
class ForumPresenter extends Base\AdminPresenter
{
    /**
     * category manager
     *
     * @var CategoriesManager $categoryManager
     * @inject
     */
    public $categoryManager;
    
    /**
     * user manager
     *
     * @var UsersManager $userManager
     * @inject
     */
    public $userManager;
    
    /**
     * topic manager
     *
     * @var TopicsManager $topicManager
     * @inject
     */
    public $topicManager;
    
    /**
     * post mananager
     *
     * @var PostsManager $postManager
     * @inject
     */
    public $postManager;
    
    /**
     * @var ModeratorsManager $moderatorsManager
     * @inject
     */
    public $moderatorsManager;

    /**
     * ForumPresenter constructor.
     *
     * @param ForumsManager $manager
     */
    public function __construct(ForumsManager $manager)
    {
        parent::__construct($manager);
    }
    
    public function handleReorder()
    {
        // todo
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Param id is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item #' . $id . ' not found.');
            }

            $this[self::FORM_NAME]->setDefaults($item);

            $subForums = $this->getManager()
                ->createForums($this->getManager()->getByParent($id), (int)$id);

            if (!$subForums) {
                $this->flashMessage('No sub forums.', self::FLASH_MESSAGE_WARNING);
            }

            $lastTopic = $this->topicManager->getLastTopicByForum($id);

            if (!$lastTopic) {
                $this->flashMessage('No last topic.', self::FLASH_MESSAGE_WARNING);
            }

            $lastPost = $this->postManager->getLastByForum($id);

            if ($lastPost) {
                $userData = $this->userManager->getById($lastPost->post_user_id);
            } else {
                $userData = false;
            }
            
            $moderators = $this->moderatorsManager->getAllJoinedByRight($id);

            $this->template->topicData  = $lastTopic;
            $this->template->lastPost   = $lastPost;
            $this->template->userData   = $userData;
            $this->template->item       = $item;
            $this->template->title      = $this->getTitleOnEdit();
            $this->template->forums     = $subForums;
            $this->template->moderators = $moderators;
        } else {
            $this->template->title      = $this->getTitleOnAdd();
            $this->template->forums     = [];
            $this->template->moderators = [];
            $this[self::FORM_NAME]->setDefaults([]);
        }
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();

        $form->addGroup('forum');
        $form->addText('forum_name', 'Forum name:')
            ->setRequired(true);
        
        $form->addText('forum_description', 'Forum description:')
            ->setRequired(true);
        
        $form->addSelect(
            'forum_category_id',
            'Forum category:',
            $this->categoryManager->getAllPairsCached('category_name')
        )
            ->setRequired(true)
            ->setTranslator(null);
        
        $form->addTextArea('forum_rules', 'Forum rules:');
        $form->addCheckbox('forum_active', 'Forum active:');
        $form->addCheckbox('forum_fast_reply', 'Forum enable fast reply:');

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
    
    /**
     * 
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getAdminTranslator());
            
        $this->gf->addFilter('forum_id', 'forum_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('forum_name', 'forum_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter(null, null, GridFilter::NOTHING);        
        
        return $this->gf;
    }
}
