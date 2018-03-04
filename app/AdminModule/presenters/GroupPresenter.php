<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\Forums2GroupsManager;
use App\Models\ForumsManager;
use App\Models\GroupsManager;
use App\Models\Users2GroupsManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of GroupPresenter
 *
 * @author rendi
 */
class GroupPresenter extends Base\AdminPresenter {

    /**
     * @var Users2GroupsManager $users2Groups
     */
    private $users2Groups;

    /**
     * @var Forums2GroupsManager $forums2groups
     */
    private $forums2groups;

    /**
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;

    /**
     * GroupPresenter constructor.
     *
     * @param GroupsManager $manager
     */
    public function __construct(GroupsManager $manager) {
        parent::__construct($manager);
    }

    public function startup() {
        parent::startup();

        if ($this->getAction() == 'default') {
            $this->gf->addFilter('group_id', 'Group ID', \App\Controls\GridFilter::INT_EQUAL);
            $this->gf->addFilter('group_name', 'Group name', \App\Controls\GridFilter::TEXT_LIKE);
            $this->gf->addFilter('', '', \App\Controls\GridFilter::NOTHING);

            $this->addComponent($this->gf, 'gridFilter');
        }
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function forumsSuccess(Form $form, ArrayHash $values) {
        $post_add = $form->getHttpData($form::DATA_TEXT, 'post_add[]');
        $post_edit = $form->getHttpData($form::DATA_TEXT, 'post_edit[]');
        $post_delete = $form->getHttpData($form::DATA_TEXT, 'post_delete[]');
        $topic_add = $form->getHttpData($form::DATA_TEXT, 'topic_add[]');
        $topic_edit = $form->getHttpData($form::DATA_TEXT, 'topic_edit[]');
        $topic_delete = $form->getHttpData($form::DATA_TEXT, 'topic_delete[]');
        $forum_id = $form->getHttpData($form::DATA_TEXT, 'forum_id[]');
        $topic_thank = $form->getHttpData($form::DATA_TEXT, 'topic_thank[]');

        $count = $this->forumsManager->getCount();
        $group_id = $this->getParameter('id');

        $groups = [];
        $forums = [];

        foreach ($this->forumsManager->getAllCached() as $forum) {
            $forums[$forum->forum_id] = $forum->forum_id;
            $groups[$forum->forum_id] = $group_id;
        }

        $data = [
            'post_add' => $this->map(array_pad($post_add, $count + 1, 0)),
            'post_edit' => $this->map(array_pad($post_edit, $count + 1, 0)),
            'post_delete' => $this->map(array_pad($post_delete, $count + 1, 0)),
            'topic_add' => $this->map(array_pad($topic_add, $count + 1, 0)),
            'topic_edit' => $this->map(array_pad($topic_edit, $count + 1, 0)),
            'topic_delete' => $this->map(array_pad($topic_delete, $count + 1, 0)),
            'topic_thank' => $this->map(array_pad($topic_thank, $count + 1, 0)),
            'forum_id' => $forums,
            'group_id' => $groups
        ];

        $this->forums2groups->addForums2group($group_id, $data);
    }

    /**
     * @param ForumsManager $forumsManager
     */
    public function injectForumsManager(ForumsManager $forumsManager) {
        $this->forumsManager = $forumsManager;
    }

    /**
     * @param Forums2GroupsManager $forums2Groups
     */
    public function injectForums2Groups(Forums2GroupsManager $forums2Groups) {
        $this->forums2groups = $forums2Groups;
    }

    /**
     * @param Users2GroupsManager $users2Groups
     */
    public function injectUsers2Groups(Users2GroupsManager $users2Groups) {
        $this->users2Groups = $users2Groups;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function map(array $data) {
        $result = [];

        foreach ($this->forumsManager->getAllCached() as $value) {
            $result[$value->forum_id] = false;
            foreach ($data as $value2) {
                if ($value->forum_id == $value2) {
                    $result[$value->forum_id] = true;
                }
            }
        }

        return $result;
    }

    /**
     * @param null $id
     */
    public function renderEdit($id = null) {
        parent::renderEdit($id);

        $this->template->countOfUsers = $this->users2Groups->getCountByRight($id);
        $this->template->forums = $this->forumsManager->createForums($this->forumsManager->getAll(), 0);

        $data = [];
        $forums = $this->forums2groups->getByRightAll($id);

        foreach ($forums as $permission) {
            $data[$permission->forum_id]['post_add'] = $permission->post_add;
            $data[$permission->forum_id]['post_edit'] = $permission->post_edit;
            $data[$permission->forum_id]['post_delete'] = $permission->post_delete;
            $data[$permission->forum_id]['topic_add'] = $permission->topic_add;
            $data[$permission->forum_id]['topic_edit'] = $permission->topic_edit;
            $data[$permission->forum_id]['topic_delete'] = $permission->topic_delete;
            $data[$permission->forum_id]['topic_thank'] = $permission->topic_thank;
        }

        $this->template->permissions = $data;
    }

    /**
     * @return BootStrapForm
     */
    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addText('group_name', 'Group name:')->setRequired(true);

        return $this->addSubmitB($form);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentForumsForm() {
        $form = new BootstrapForm();
        $form->setTranslator($this->getAdminTranslator());
        $form->addSubmit('send', 'Send');
        $form->onSuccess[] = [
            $this,
            'forumsSuccess'
        ];

        return $form;
    }

}
