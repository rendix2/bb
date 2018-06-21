<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\CategoriesManager;
use App\Models\ForumsManager;
use App\Models\PostsManager;
use App\Models\TopicsManager;
use App\Models\UsersManager;

/**
 * Description of ForumPresenter
 *
 * @author rendi
 * @method ForumsManager getManager()
 */
class ForumPresenter extends Base\AdminPresenter
{
    /**
     * @var CategoriesManager $categoryManager
     */
    private $categoryManager;
    
    /**
     * @var UsersManager $userManager
     */
    private $userManager;
    
    /**
     * @var TopicsManager $topicManager
     */
    private $topicManager;
    
    /**
     *
     * @var PostsManager $postManager
     */
    private $postManager;
    
    /**
     * @var \App\Models\ModeratorsManager $moderatorsManager
     */
    private $moderatorsManager;

    /**
     * ForumPresenter constructor.
     *
     * @param ForumsManager $manager
     */
    public function __construct(ForumsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @param CategoriesManager $categoryManager
     */
    public function injectCategoryManager(CategoriesManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * @param PostsManager $postManager
     */
    public function injectPostManager(PostsManager $postManager)
    {
        $this->postManager = $postManager;
    }

    /**
     * @param TopicsManager $topicManager
     */
    public function injectTopicManager(TopicsManager $topicManager)
    {
        $this->topicManager = $topicManager;
    }

    /**
     * @param UsersManager $userManager
     */
    public function injectUserManager(UsersManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param \App\Models\ModeratorsManager $moderatorsManager
     */
    public function injectModeratorsManager(\App\Models\ModeratorsManager $moderatorsManager)
    {
        $this->moderatorsManager = $moderatorsManager;
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();
        
        if ($this->getAction() === 'default') {
            $this->gf->setTranslator($this->getAdminTranslator());
            
            $this->gf->addFilter('forum_id', 'forum_id', GridFilter::INT_EQUAL);
            $this->gf->addFilter('forum_name', 'forum_name', GridFilter::TEXT_LIKE);
            $this->gf->addFilter(null, null, GridFilter::NOTHING);

            $this->addComponent($this->gf, 'gridFilter');
        }
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

            $this['editForm']->setDefaults($item);

            $subForums = $this->getManager()
                ->createForums($this->getManager()->getForumsByForumParentId($id), (int)$id);

            if (!$subForums) {
                $this->flashMessage('No sub forums.', self::FLASH_MESSAGE_WARNING);
            }

            $lastTopic = $this->topicManager->getLastTopicByForumId($id);

            if (!$lastTopic) {
                $this->flashMessage('No last topic', self::FLASH_MESSAGE_WARNING);
            }

            $lastPost = $this->postManager->getLastPostByForumId($id);

            if ($lastPost) {
                $userData = $this->userManager->getById($lastPost->post_user_id);
            } else {
                $userData = false;
            }
            
            $moderators = $this->moderatorsManager->getByRightJoined($id);
            
            \Tracy\Debugger::barDump($moderators);

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

            //            $this->template->item = \Nette\Utils\ArrayHash::from(['forum_topic_count' =>null, 'forum_last_post_id' => null, 'forum_last_post_user_id' => null]);
            //            $this->template->topicData = [];
            //            $this->template->userData = \Nette\Utils\ArrayHash::from(['user_name' => null]);
            $this['editForm']->setDefaults([]);
        }
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootStrapForm();

        $form->setTranslator($this->getAdminTranslator());

        $form->addGroup('forum');
        $form->addText('forum_name', 'Forum name:')
            ->setRequired(true);
        
        $form->addText('forum_description', 'Forum description:')
            ->setRequired(true);
        
        $form->addSelect('forum_category_id', 'Forum category:', $this->categoryManager->getAllPairsCached('category_name'))
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
}
