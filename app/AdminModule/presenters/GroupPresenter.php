<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\UserSearchControl;
use App\Models\Forums2GroupsManager;
use App\Models\ForumsManager;
use App\Models\GroupsManager;
use App\Models\Users2GroupsManager;
use App\Models\UsersManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of GroupPresenter
 *
 * @author rendix2
 * @method GroupsManager getManager()
 * @package App\AdminModule\Presenters
 */
class GroupPresenter extends AdminPresenter
{
    /**
     * @var Users2GroupsManager $users2Groups
     * @inject
     */
    public $users2GroupsManager;
    
    /**
     * @var Forums2GroupsManager $forums2groups
     * @inject
     */
    public $forums2groupsManager;
    
    /**
     * @var ForumsManager $forumsManager
     * @inject
     */
    public $forumsManager;

    /**
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;
    
    /**
     * GroupPresenter constructor.
     *
     * @param GroupsManager $manager
     */
    public function __construct(GroupsManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * GroupPresenter destructor.
     */
    public function __destruct()
    {
        $this->users2GroupsManager  = null;
        $this->forums2groupsManager = null;
        $this->forumsManager        = null;
        $this->usersManager         = null;
        
        parent::__destruct();
    }

    /**
     * @param array $added_forum_row
     *
     * @return array
     */
    private function map(array $added_forum_row)
    {
        $result = [];

        foreach ($this->forumsManager->getAllCached() as $forum) {
            $result[$forum->forum_id] = false;
            
            foreach ($added_forum_row as $forum_row) {
                if ($forum->forum_id === (int)$forum_row) {
                    $result[$forum->forum_id] = true;
                }
            }
        }

        return $result;
    }
    
    /**
     *
     * @param int    $user_id
     * @param string $user_name
     */
    public function handleSetUserId($user_id, $user_name)
    {
        $this[self::FORM_NAME]->setDefaults(['group_moderator_id' => $user_id, 'group_moderator' => $user_name]);
        $this->redrawControl('editForm');
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);

        if ($id) {
            if ($this->getParameter('user_name') && $this->getParameter('user_id')) {
                $this[self::FORM_NAME]->setDefaults([
                    'group_moderator'    => $this->getParameter('user_name'),
                    'group_moderator_id' => $this->getParameter('user_id')
                ]);
            } else {
                $item      = $this->template->item;
                $moderator = $this->usersManager->getById($item->group_moderator_id);
        
                $this[self::FORM_NAME]->setDefaults(['group_moderator' => $moderator->user_name]);
            }
        }

        $permission   = [];
        $forums = $this->forums2groupsManager->getAllByRight($id);

        foreach ($forums as $forum) {
            $permission[$forum->forum_id]['post_add']         = $forum->post_add;
            $permission[$forum->forum_id]['post_update']      = $forum->post_update;
            $permission[$forum->forum_id]['post_delete']      = $forum->post_delete;
            $permission[$forum->forum_id]['topic_add']        = $forum->topic_add;
            $permission[$forum->forum_id]['topic_update']     = $forum->topic_update;
            $permission[$forum->forum_id]['topic_delete']     = $forum->topic_delete;
            $permission[$forum->forum_id]['topic_thank']      = $forum->topic_thank;
            $permission[$forum->forum_id]['topic_fast_reply'] = $forum->topic_fast_reply;
        }

        $this->template->countOfUsers = $this->users2GroupsManager->getCountByRight($id);
        $this->template->forums       = $this->forumsManager->createForums($this->forumsManager->getAll(), 0);
        $this->template->permissions  = $permission;
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();

        $form->addHidden('group_moderator_id');
        $form->addText('group_name', 'Group name:')
            ->setRequired(true);
        $form->addText('group_moderator', 'Group moderator:')->setDisabled();

        return $this->addSubmitB($form);
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('group_id', 'group_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('group_name', 'group_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);

        return $this->gf;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values)
    {
        unset($values->group_moderator);

        parent::editFormSuccess($form, $values);
    }
    
    /**
     *
     * @return UserSearchControl
     */
    protected function createComponentUserSearch()
    {
        return new UserSearchControl($this->usersManager, $this->getTranslator());
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentForumsForm()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->getTranslator());
        
        $form->addSubmit('send', 'Send');
        $form->onSuccess[] = [$this, 'forumsSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function forumsSuccess(Form $form, ArrayHash $values)
    {
        $post_add         = $form->getHttpData($form::DATA_TEXT, 'post_add[]');
        $post_update      = $form->getHttpData($form::DATA_TEXT, 'post_update[]');
        $post_delete      = $form->getHttpData($form::DATA_TEXT, 'post_delete[]');
        $topic_add        = $form->getHttpData($form::DATA_TEXT, 'topic_add[]');
        $topic_update     = $form->getHttpData($form::DATA_TEXT, 'topic_update[]');
        $topic_delete     = $form->getHttpData($form::DATA_TEXT, 'topic_delete[]');
        $forum_id         = $form->getHttpData($form::DATA_TEXT, 'forum_id[]');
        $topic_thank      = $form->getHttpData($form::DATA_TEXT, 'topic_thank[]');
        $topic_fast_reply = $form->getHttpData($form::DATA_TEXT, 'topic_fast_reply[]');
        
        /**
         * @var int $count count
         *
         */
        $count    = $this->forumsManager->getCount();
        $group_id = $this->getParameter('id');

        $forumsData = $this->forumsManager->getAllCached();
        $groups = [];
        $forums = [];

        foreach ($forumsData as $forum) {
            $forums[$forum->forum_id] = $forum->forum_id;
            $groups[$forum->forum_id] = (int)$group_id;
        }

        $data = [
            'post_add'         => $this->map(array_pad($post_add, $count + 1, 0)),
            'post_update'      => $this->map(array_pad($post_update, $count + 1, 0)),
            'post_delete'      => $this->map(array_pad($post_delete, $count + 1, 0)),
            'topic_add'        => $this->map(array_pad($topic_add, $count + 1, 0)),
            'topic_update'     => $this->map(array_pad($topic_update, $count + 1, 0)),
            'topic_delete'     => $this->map(array_pad($topic_delete, $count + 1, 0)),
            'topic_thank'      => $this->map(array_pad($topic_thank, $count + 1, 0)),
            'topic_fast_reply' => $this->map(array_pad($topic_fast_reply, $count + 1, 0)),
            'forum_id'         => $forums,
            'group_id'         => $groups
        ];
        
        $this->forums2groupsManager->addForums2group($group_id, $data);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_groups']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Group:default', 'text' => 'menu_groups'],
            2 => ['link' => 'Group:edit',    'text' => 'menu_group'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
