<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Entity\TopicEntity;
use DateTimeImmutable;
use Nette\Utils\Strings;

/**
 * The administration of categories and forums (slice 7a). A forum belongs either to a category or to a parent forum
 * (a subforum), never both, and never into its own subtree. topic.category follows the forum: when the category of a forum
 * changes, the topics of the forum and of its subforums get the new one. Only an empty category or forum may be deleted
 * (the foreign keys would delete the topics with it).
 */
class ForumAdminService
{
    public const string ERROR_NAME = 'admin-forum.error.name';
    public const string ERROR_PLACE = 'admin-forum.error.place';
    public const string ERROR_CYCLE = 'admin-forum.error.cycle';
    public const string ERROR_CATEGORY_NOT_EMPTY = 'admin-forum.error.categoryNotEmpty';
    public const string ERROR_FORUM_NOT_EMPTY = 'admin-forum.error.forumNotEmpty';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumTreeService $forumTree,
    ) {
    }

    public function saveCategory(?CategoryEntity $category, string $name, int $sortOrder, bool $active) : CategoryEntity
    {
        $name = $this->name($name);

        if ($category === null) {
            $category = new CategoryEntity();
            $category->parent = null;
            $this->em->persist($category);
        } else {
            $category->updatedAt = new DateTimeImmutable();
        }

        $category->name = $name;
        $category->sortOrder = $sortOrder;
        $category->active = $active;
        $this->em->flush();

        return $category;
    }

    public function deleteCategory(CategoryEntity $category) : void
    {
        $forums = $this->em->getRepository(ForumEntity::class)->count(['category' => $category]);
        $children = $this->em->getRepository(CategoryEntity::class)->count(['parent' => $category]);

        if ($forums > 0 || $children > 0) {
            throw new AdminException(self::ERROR_CATEGORY_NOT_EMPTY);
        }

        $this->em->remove($category);
        $this->em->flush();
    }

    /**
     * Creates ($forum null) or edits a forum; exactly one of $category and $parent.
     */
    public function saveForum(
        ?ForumEntity $forum,
        string $name,
        ?string $description,
        ?CategoryEntity $category,
        ?ForumEntity $parent,
        bool $active,
        ?string $rules,
        int $sortOrder,
        ForumPermissions $permissions,
    ) : ForumEntity {
        $name = $this->name($name);

        if (($category === null) === ($parent === null)) {
            throw new AdminException(self::ERROR_PLACE);
        }

        if ($forum !== null && $parent !== null && $this->forumTree->isInSubtree($forum, $parent)) {
            throw new AdminException(self::ERROR_CYCLE);
        }

        if ($forum === null) {
            $forum = new ForumEntity();
            $this->em->persist($forum);
        } else {
            $forum->updatedAt = new DateTimeImmutable();
        }

        $forum->name = $name;
        $forum->description = $this->optional($description);
        $forum->category = $category;
        $forum->parent = $parent;
        $forum->active = $active;
        $forum->rules = $this->optional($rules);
        $forum->sortOrder = $sortOrder;
        $forum->permissions = $permissions;

        $this->em->wrapInTransaction(function () use ($forum) : void {
            $this->em->flush();
            $this->syncTopicCategories($forum);
        });

        return $forum;
    }

    public function deleteForum(ForumEntity $forum) : void
    {
        $topics = $this->em->getRepository(TopicEntity::class)->count(['forum' => $forum]);
        $children = $this->em->getRepository(ForumEntity::class)->count(['parent' => $forum]);

        if ($topics > 0 || $children > 0) {
            throw new AdminException(self::ERROR_FORUM_NOT_EMPTY);
        }

        $this->em->remove($forum);
        $this->em->flush();
    }

    /**
     * topic.category of the forum and its subforums after the forum moved to another category or parent.
     */
    private function syncTopicCategories(ForumEntity $forum) : void
    {
        foreach ($this->forumTree->withDescendants($forum) as $node) {
            $category = $this->forumTree->categoryOf($node);

            if ($category === null) {
                continue;
            }

            $this->em->createQueryBuilder()
                ->update(TopicEntity::class, '_t')
                ->set('_t.category', ':category')
                ->where('_t.forum = :forum')
                ->andWhere('_t.category != :category')
                ->setParameter('category', $category)
                ->setParameter('forum', $node)
                ->getQuery()
                ->execute();
        }
    }

    private function name(string $name) : string
    {
        $name = Strings::trim($name);

        if ($name === '' || Strings::length($name) > 255) {
            throw new AdminException(self::ERROR_NAME);
        }

        return $name;
    }

    private function optional(?string $text) : ?string
    {
        $text = $text !== null ? Strings::trim($text) : '';

        return $text !== '' ? $text : null;
    }
}
