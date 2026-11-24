<?php declare(strict_types=1);

namespace App\UI\Admin\Accessory;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Service\AdminException;
use App\Model\Service\ForumAdminService;
use App\Model\Service\ForumTreeService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * The form of a forum for Admin:Forum:Create and Admin:Forum:Edit. The place is one select: a category ("c<id>") or a parent
 * forum ("f<id>", a subforum), so a forum never gets both; the forum itself and its subforums are not offered as a parent.
 * The permissions are those of the logged-in users in the forum (ForumPermissions), the groups and users get theirs in 7b.
 */
class ForumFormFactory
{
    public const array PERMISSIONS = ['topicAdd', 'topicUpdate', 'topicDelete', 'postAdd', 'postUpdate', 'postDelete', 'topicThank', 'topicFastReply'];

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumAdminService $forumAdmin,
        private readonly ForumTreeService $forumTree,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param callable(ForumEntity) : void $onSaved
     */
    public function create(?ForumEntity $forum, callable $onSaved) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        $form->addText('name', 'admin-forum.forum.name')
            ->setRequired('admin-forum.error.name')
            ->setMaxLength(255);
        $form->addTextArea('description', 'admin-forum.forum.description', rows: 2);
        // the names of the categories and forums are not translated
        $form->addSelect('place', $this->translator->translate('admin-forum.forum.place'), $this->places($forum))
            ->setTranslator(null)
            ->setPrompt($this->translator->translate('admin-forum.forum.placePrompt'))
            ->setRequired($this->translator->translate('admin-forum.error.place'));
        $form->addInteger('sortOrder', 'admin-forum.sortOrder')
            ->setRequired()
            ->setDefaultValue(0);
        $form->addCheckbox('active', 'admin-forum.active')
            ->setDefaultValue(true);
        $form->addTextArea('rules', 'admin-forum.forum.rules', rows: 4)
            ->setOption('description', $this->translator->translate('admin-forum.forum.rulesDescription'));

        $permissions = $form->addContainer('permissions');
        $defaults = $forum?->permissions ?? $this->newPermissions();

        foreach (self::PERMISSIONS as $permission) {
            $permissions->addCheckbox($permission, 'admin-forum.permission.' . $permission)
                ->setDefaultValue($defaults->$permission);
        }

        $form->addSubmit('send', 'admin-forum.save');

        if ($forum !== null) {
            $form->setDefaults([
                'name' => $forum->name,
                'description' => $forum->description,
                'place' => $forum->parent !== null ? 'f' . $forum->parent->id : 'c' . $forum->category?->id,
                'sortOrder' => $forum->sortOrder,
                'active' => $forum->active,
                'rules' => $forum->rules,
            ]);
        }

        $form->onSuccess[] = function (Form $form) use ($forum, $onSaved) : void {
            /** @var array{name: string, description: string, place: string, sortOrder: int, active: bool, rules: string, permissions: array<string, bool>} $values */
            $values = $form->getValues('array');
            $category = str_starts_with($values['place'], 'c') ? $this->em->find(CategoryEntity::class, (int) substr($values['place'], 1)) : null;
            $parent = str_starts_with($values['place'], 'f') ? $this->em->find(ForumEntity::class, (int) substr($values['place'], 1)) : null;
            $permissions = new ForumPermissions();

            foreach (self::PERMISSIONS as $permission) {
                $permissions->$permission = $values['permissions'][$permission];
            }

            try {
                $saved = $this->forumAdmin->saveForum($forum, $values['name'], $values['description'], $category, $parent, $values['active'], $values['rules'], $values['sortOrder'], $permissions);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $onSaved($saved);
        };

        return $form;
    }

    /**
     * Categories and forums (all, also inactive) the forum may belong to.
     *
     * @return array<string, array<string, string>> group => value => name
     */
    private function places(?ForumEntity $forum) : array
    {
        $categories = [];

        foreach ($this->em->getRepository(CategoryEntity::class)->findBy([], ['sortOrder' => 'ASC', 'id' => 'ASC']) as $category) {
            $categories['c' . $category->id] = $category->name;
        }

        $forums = [];

        foreach ($this->em->getRepository(ForumEntity::class)->findBy([], ['sortOrder' => 'ASC', 'id' => 'ASC']) as $candidate) {
            if ($forum === null || !$this->forumTree->isInSubtree($forum, $candidate)) {
                $forums['f' . $candidate->id] = $candidate->name;
            }
        }

        return [
            $this->translator->translate('admin-forum.forum.placeCategories') => $categories,
            $this->translator->translate('admin-forum.forum.placeForums') => $forums,
        ];
    }

    private function newPermissions() : ForumPermissions
    {
        $permissions = new ForumPermissions();
        $permissions->grantDefaultsOfANewForum();

        return $permissions;
    }
}
