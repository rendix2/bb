<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\PostHistoryEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;

/**
 * The edit history of a post (slice 6c): every edit keeps the previous version in post_history (PostWriteService::edit()).
 * It is seen by the author of the post, the moderators of its forum and the admin, everyone only while he sees the post.
 */
class PostHistoryService
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
    ) {
    }

    public function mayView(?UserEntity $user, PostEntity $post) : bool
    {
        if ($user === null || !$this->permissions->isAllowed($user, $post, ForumPrivilege::VIEW)) {
            return false;
        }

        return $post->user->id === $user->id
            || $this->permissions->isAllowed($user, $post->topic->forum, ForumPrivilege::MODERATE);
    }

    /**
     * The previous versions, the last replaced first; each with who replaced it and when.
     *
     * @return list<PostHistoryEntity>
     */
    public function versions(PostEntity $post) : array
    {
        /** @var list<PostHistoryEntity> */
        return $this->em->getRepository(PostHistoryEntity::class)
            ->createQueryBuilder('_h')
            ->select('_h', '_u')
            ->innerJoin('_h.user', '_u')
            ->where('_h.post = :post')
            ->setParameter('post', $post)
            ->orderBy('_h.createdAt', 'DESC')
            ->addOrderBy('_h.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
