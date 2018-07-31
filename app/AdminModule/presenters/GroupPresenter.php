<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\Forums2GroupsManager;
use App\Models\ForumsManager;
use App\Models\GroupsManager;
use App\Models\Users2GroupsManager;
use App\Models\UsersManager;
use App\Controls\UserSearchControl;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of GroupPresenter
 *
 * @author rendi
 */
class GroupPresenter extends Base\AdminPresenter
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
     *
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
     * @param array $data
     *
     * @return array
     */
    private function map(array $data)
    {
        $result = [];

        foreach ($this->forumsManager->getAllCached() as $value) {
            $result[$value->forum_id] = false;
            
            foreach ($data as $value2) {
                if ($value->forum_id === (int)$value2) {
                    $result[$value->forum_id] = true;
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
                $this['editForm']->setDefaults(['group_moderator' => $this->getParameter('user_name'), 'group_moderator_id' => $this->getParameter('user_id')]);
            } else {
                $item      = $this->template->item;        
                $moderator = $this->usersManager->getById($item->group_moderator_id);
        
                $this['editForm']->setDefaults(['group_moderator' => $moderator->user_name]);                
            }            
        }
        
        $this->template->countOfUsers = $this->users2GroupsManager->getCountByRight($id);
        $this->template->forums       = $this->forumsManager->createForums($this->forumsManager->getAll(), 0);

        $data   = [];
        $forums = $this->forums2groupsManager->getAllByRight($id);

        foreach ($forums as $permission) {
            $data[$permission->forum_id]['post_add']     = $permission->post_add;
            $data[$permission->forum_id]['post_edit']    = $permission->post_edit;
            $data[$permission->forum_id]['post_delete']  = $permission->post_delete;
            $data[$permission->forum_id]['topic_add']    = $permission->topic_add;
            $data[$permission->forum_id]['topic_edit']   = $permission->topic_edit;
            $data[$permission->forum_id]['topic_delete'] = $permission->topic_delete;
            $data[$permission->forum_id]['topic_thank']  = $permission->topic_thank;
        }

        $this->template->permissions = $data;        
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
        $this->gf->setTranslator($this->getAdminTranslator());
    
        $this->gf->addFilter('group_id', 'group_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('group_name', 'group_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter(null, null, GridFilter::NOTHING);

        return $this->gf;
    }

    /**
     * 
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
        return new UserSearchControl($this->usersManager, $this->getAdminTranslator());
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentForumsForm()
    {
        $form = $this->createBootstrapForm();
        
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
        $post_add     = $form->getHttpData($form::DATA_TEXT, 'post_add[]');
        $post_edit    = $form->getHttpData($form::DATA_TEXT, 'post_edit[]');
        $post_delete  = $form->getHttpData($form::DATA_TEXT, 'post_delete[]');
        $topic_add    = $form->getHttpData($form::DATA_TEXT, 'topic_add[]');
        $topic_edit   = $form->getHttpData($form::DATA_TEXT, 'topic_edit[]');
        $topic_delete = $form->getHttpData($form::DATA_TEXT, 'topic_delete[]');
        $forum_id     = $form->getHttpData($form::DATA_TEXT, 'forum_id[]');
        $topic_thank  = $form->getHttpData($form::DATA_TEXT, 'topic_thank[]');
        
        /**
         * @var int $count count
         *
         */
        $count    = $this->forumsManager->getCount();
        $group_id = $this->getParameter('id');

        $groups = [];
        $forums = [];

        foreach ($this->forumsManager->getAllCached() as $forum) {
            $forums[$forum->forum_id] = $forum->forum_id;
            $groups[$forum->forum_id] = (int)$group_id;
        }

        $data = [
            'post_add'     => $this->map(array_pad($post_add, $count + 1, 0)),
            'post_edit'    => $this->map(array_pad($post_edit, $count + 1, 0)),
            'post_delete'  => $this->map(array_pad($post_delete, $count + 1, 0)),
            'topic_add'    => $this->map(array_pad($topic_add, $count + 1, 0)),
            'topic_edit'   => $this->map(array_pad($topic_edit, $count + 1, 0)),
            'topic_delete' => $this->map(array_pad($topic_delete, $count + 1, 0)),
            'topic_thank'  => $this->map(array_pad($topic_thank, $count + 1, 0)),
            'forum_id'     => $forums,
            'group_id'     => $groups
        ];

        $this->forums2groupsManager->addForums2group($group_id, $data);
    }    
}
