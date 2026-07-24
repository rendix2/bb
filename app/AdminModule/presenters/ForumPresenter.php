<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Database\EntityManagerDecorator;
use App\Models\CategoryManager;
use App\Models\ForumFacade;
use App\Models\ForumManager;
use App\Models\ModeratorManager;
use App\Models\PostManager;
use App\Models\TopicManager;
use App\Models\UsersManager;
use Doctrine\DBAL\Exception as DbalException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @method ForumManager getManager()
 * @package App\AdminModule\Presenters
 */
class ForumPresenter extends AdminPresenter
{

    /**
     *
     * @var CategoryManager $categoriesManager
     * @inject
     */
    public $categoriesManager;

    /**
     *
     * @var TopicManager $topicsManager
     * @inject
     */
    public $topicsManager;

    /**
     *
     * @var PostManager $postsManager
     * @inject
     */
    public $postsManager;

    /**
     * @var ModeratorManager $moderatorsManager
     * @inject
     */
    public $moderatorsManager;

    /**
     *
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;

    /**
     *
     * @var ForumFacade $forumFacade
     * @inject
     */
    public $forumFacade;

    /**
     * ForumPresenter constructor.
     *
     * @param ForumManager $manager
     */
    public function __construct(
        private readonly EntityManagerDecorator $em,
        ForumManager                            $manager
    )
    {
        parent::__construct($manager);
    }

    /**
     * ForumPresenter destructor.
     */
    public function __destruct()
    {
        $this->categoriesManager = null;
        $this->topicsManager = null;
        $this->postsManager = null;
        $this->moderatorsManager = null;
        $this->usersManager = null;
        $this->forumFacade = null;

        parent::__destruct();
    }

    /**
     *
     */
    public function handleReorder()
    {
        // todo
    }

    /**
     *
     * @param int $page
     */
    public function renderDefault($page = 1)
    {
        parent::renderDefault($page);

        $allForums = $this->em
            ->getRepository(\App\Model\Entity\ForumEntity::class)
            ->findAll();

        $rootForums = array_filter($allForums, fn($f) => $f->getParent() === null);

        $this->template->tree = $rootForums;
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
                ->createForums($this->getManager()->getAllByParent($id), (int)$id);

            if (!$subForums) {
                $this->flashMessage('No sub forums.', self::FLASH_MESSAGE_WARNING);
            }

            $lastTopic = $this->topicsManager->getLastByForum($id);

            if (!$lastTopic) {
                $this->flashMessage('No last topic.', self::FLASH_MESSAGE_WARNING);
            }

            $lastPost = $this->postsManager->getLastByForum($id);

            if ($lastPost) {
                $userData = $this->usersManager->getById($lastPost->post_user_id);
            } else {
                $userData = false;
            }

            $moderators = $this->moderatorsManager->getAllByRightJoined($id);

            $this->template->topicData = $lastTopic;
            $this->template->lastPost = $lastPost;
            $this->template->userData = $userData;
            $this->template->item = $item;
            $this->template->title = $this->getTitleOnEdit();
            $this->template->forums = $subForums;
            $this->template->moderators = $moderators;
        } else {
            $this->template->title = $this->getTitleOnAdd();
            $this->template->forums = [];
            $this->template->moderators = [];
            $this[self::FORM_NAME]->setDefaults([]);
        }
    }

    /**
     * @param int $id
     */
    public function actionDelete($id): void
    {
        try {
            $forumEntity = $this->em
                ->getRepository(\App\Model\Entity\ForumEntity::class)
                ->find($id);

            $this->em->remove($forumEntity);
            $this->em->flush();

            $this->flashMessage('Item was deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redrawControl('flashes');
        } catch(DbalException $exception) {
            $this->flashMessage('Item was not deleted.', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect(':' . $this->getName() . ':default');
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
        $form->addSelect(
            'forum_parent_id',
            'Forum parent:',
            [0 => '-'] + $this->getManager()->getAllPairs('forum_name')
        )->setTranslator(null);

        $form->addText('forum_description', 'Forum description:')
            ->setRequired(true);

        $form->addSelect(
            'forum_category_id',
            'Forum category:',
            $this->categoriesManager->getAllPairsCached('category_name')
        )
            ->setRequired(true)
            ->setTranslator(null);

        $form->addTextArea('forum_rules', 'Forum rules:');
        $form->addCheckbox('forum_active', 'Forum active:');

        $form->addGroup('user');

        $form->addCheckbox('forum_thank', 'Forum thank:');
        $form->addCheckbox('forum_fast_reply', 'Forum enable fast reply:');
        $form->addCheckbox('forum_post_add', 'Forum add post:');
        $form->addCheckbox('forum_post_delete', 'Forum post delete:');
        $form->addCheckbox('forum_post_update', 'Forum post update:');
        $form->addCheckbox('forum_topic_add', 'Forum topic add:');
        $form->addCheckbox('forum_topic_update', 'Forum topic update:');
        $form->addCheckbox('forum_topic_delete', 'Forum delete topic:');

        return $this->addSubmitB($form);
    }

    /**
     * @param Form $form form
     * @param ArrayHash $values values
     */
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $id = $this->getParameter('id');

        try {
            if ($id) {
                $result = $this->forumFacade->update($id, $values);
            } else {
                $forumEntity = new \App\Model\Entity\ForumEntity();
                $forumEntity->name = $values->forum_name;

                $this->em->persist($forumEntity);
                $this->em->flush();
                $this->flashMessage($this->getTitle() . ' was saved.', self::FLASH_MESSAGE_SUCCESS);
                $this->redrawControl('flashes');
            }
        } catch (DbalException $exception) {
            $this->flashMessage(
                'There was some problem during saving into database. Form was NOT saved.',
                self::FLASH_MESSAGE_DANGER
            );

            Debugger::log($exception->getMessage(), ILogger::CRITICAL);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('forum_id', 'forum_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('forum_name', 'forum_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);

        return $this->gf;
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_forums']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Forum:default', 'text' => 'menu_forums'],
            2 => ['link' => 'Forum:edit', 'text' => 'menu_forum'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
