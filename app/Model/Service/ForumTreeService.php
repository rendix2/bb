<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;

/**
 * The tree of forums: a forum belongs to a category, a subforum (no category) to its parent forum.
 */
class ForumTreeService
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
    ) {
    }

    /**
     * The category of the topics of the forum: a subforum takes the category of its root forum.
     */
    public function categoryOf(ForumEntity $forum) : ?CategoryEntity
    {
        $seen = [];

        while ($forum->category === null && $forum->parent !== null && !isset($seen[$forum->id])) {
            $seen[$forum->id] = true;
            $forum = $forum->parent;
        }

        return $forum->category;
    }

    /**
     * The forum and all its subforums (depth first).
     *
     * @return list<ForumEntity>
     */
    public function withDescendants(ForumEntity $forum) : array
    {
        $result = [$forum];

        /** @var list<ForumEntity> $children */
        $children = $this->em->getRepository(ForumEntity::class)->findBy(['parent' => $forum], ['sortOrder' => 'ASC', 'id' => 'ASC']);

        foreach ($children as $child) {
            if ($child->id !== $forum->id) {
                $result = [...$result, ...$this->withDescendants($child)];
            }
        }

        return $result;
    }

    /**
     * Is $candidate the forum itself or one of its subforums (it cannot become its parent)?
     */
    public function isInSubtree(ForumEntity $forum, ForumEntity $candidate) : bool
    {
        $node = $candidate;
        $seen = [];

        while ($node !== null && !isset($seen[$node->id])) {
            if ($node->id === $forum->id) {
                return true;
            }

            $seen[$node->id] = true;
            $node = $node->parent;
        }

        return false;
    }
}
