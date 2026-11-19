<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\PostHistoryEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumPermissionService;
use App\Model\Security\ForumPrivilege;
use App\Settings\PostSetting;
use DateTimeImmutable;
use Nette\Utils\Strings;

/**
 * Writes replies and new topics: the permission is checked HERE (when written, not only when a form is shown - the old
 * code could be bypassed), then the length of the text (posts.minLength / maxLength), the flood interval of the user
 * (posts.minUserTimeInterval) and the same text in the same topic again (posts.minDoublePostTimeInterval).
 */
class PostWriteService
{
    public const string ERROR_NOT_ALLOWED = 'forum-post-write.error.notAllowed';
    public const string ERROR_TOO_SHORT = 'forum-post-write.error.tooShort';
    public const string ERROR_TOO_LONG = 'forum-post-write.error.tooLong';
    public const string ERROR_FLOOD = 'forum-post-write.error.flood';
    public const string ERROR_DOUBLE_POST = 'forum-post-write.error.doublePost';
    public const string ERROR_TOPIC_NAME = 'forum-post-write.error.topicName';
    public const string ERROR_FIRST_POST = 'forum-post-write.error.firstPost';

    private const int TOPIC_NAME_MAX_LENGTH = 1024;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
        private readonly PostSetting $postSetting,
    ) {
    }

    public function reply(UserEntity $user, TopicEntity $topic, string $text, ?string $title, string $ipAddress, bool $fastReply = false) : PostEntity
    {
        $privilege = $fastReply ? ForumPrivilege::FAST_REPLY : ForumPrivilege::REPLY;

        if (!$this->permissions->isAllowed($user, $topic, $privilege)) {
            throw new PostWriteException(self::ERROR_NOT_ALLOWED);
        }

        $text = $this->checkText($text);
        $this->checkFlood($user);
        $this->checkDoublePost($user, $topic, $text);

        $post = $this->newPost($user, $topic, $text, $title, $ipAddress);
        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    public function createTopic(UserEntity $user, ForumEntity $forum, string $name, string $text, string $ipAddress) : TopicEntity
    {
        if (!$this->permissions->isAllowed($user, $forum, ForumPrivilege::TOPIC_ADD)) {
            throw new PostWriteException(self::ERROR_NOT_ALLOWED);
        }

        $name = Strings::trim($name);

        if ($name === '' || Strings::length($name) > self::TOPIC_NAME_MAX_LENGTH) {
            throw new PostWriteException(self::ERROR_TOPIC_NAME);
        }

        $text = $this->checkText($text);
        $this->checkFlood($user);

        $topic = new TopicEntity();
        $topic->name = $name;
        $topic->user = $user;
        $topic->forum = $forum;
        // a forum without a category is a subforum: the topic belongs to the category of its root forum
        $topic->category = $forum->category ?? $this->rootForum($forum)->category
            ?? throw new PostWriteException(self::ERROR_NOT_ALLOWED);

        $this->em->wrapInTransaction(function () use ($user, $topic, $text, $ipAddress) : void {
            $this->em->persist($topic);
            $this->em->persist($this->newPost($user, $topic, $text, null, $ipAddress));
        });

        return $topic;
    }

    /**
     * Edits the post: the previous version goes to post_history (who changed it and when), the post gets the new title
     * and text, the time and IP address of the edit.
     */
    public function edit(UserEntity $editor, PostEntity $post, ?string $title, string $text, string $ipAddress) : void
    {
        if (!$this->permissions->isAllowed($editor, $post, ForumPrivilege::EDIT)) {
            throw new PostWriteException(self::ERROR_NOT_ALLOWED);
        }

        $text = $this->checkText($text);
        $title = $title !== null && Strings::trim($title) !== '' ? Strings::trim($title) : null;

        if ($title === $post->title && $text === $post->text) {
            return;
        }

        $history = new PostHistoryEntity();
        $history->post = $post;
        $history->user = $editor;
        $history->category = $post->category;
        $history->forum = $post->forum;
        $history->topic = $post->topic;
        $history->title = $post->title;
        $history->text = $post->text;

        $post->title = $title;
        $post->text = $text;
        $post->editIpAddress = $ipAddress;
        $post->updatedAt = new DateTimeImmutable();

        $this->em->wrapInTransaction(function () use ($history) : void {
            $this->em->persist($history);
        });
    }

    public function isFirstPost(PostEntity $post) : bool
    {
        $first = $this->em->getRepository(PostEntity::class)
            ->createQueryBuilder('_p')
            ->select('MIN(_p.id)')
            ->where('_p.topic = :topic')
            ->setParameter('topic', $post->topic)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $first === $post->id;
    }

    /**
     * Deletes a reply (with its history, thanks and files by the foreign keys); the first post goes only with its topic.
     */
    public function deletePost(UserEntity $user, PostEntity $post) : void
    {
        if (!$this->permissions->isAllowed($user, $post, ForumPrivilege::DELETE)) {
            throw new PostWriteException(self::ERROR_NOT_ALLOWED);
        }

        if ($this->isFirstPost($post)) {
            throw new PostWriteException(self::ERROR_FIRST_POST);
        }

        $this->em->remove($post);
        $this->em->flush();
    }

    /**
     * Deletes a topic with all its posts (and what hangs on them, by the foreign keys).
     */
    public function deleteTopic(UserEntity $user, TopicEntity $topic) : void
    {
        if (!$this->permissions->isAllowed($user, $topic, ForumPrivilege::DELETE)) {
            throw new PostWriteException(self::ERROR_NOT_ALLOWED);
        }

        $this->em->remove($topic);
        $this->em->flush();
    }

    private function checkText(string $text) : string
    {
        $text = Strings::trim(Strings::unixNewLines($text));
        $settings = $this->postSetting->get();
        $length = Strings::length($text);

        if ($length === 0 || ($settings['minLength'] !== null && $length < (int) $settings['minLength'])) {
            throw new PostWriteException(self::ERROR_TOO_SHORT);
        }

        if ($settings['maxLength'] !== null && $length > (int) $settings['maxLength']) {
            throw new PostWriteException(self::ERROR_TOO_LONG);
        }

        return $text;
    }

    private function checkFlood(UserEntity $user) : void
    {
        $since = new DateTimeImmutable('-' . (int) $this->postSetting->get()['minUserTimeInterval'] . ' seconds');

        $recent = $this->em->getRepository(PostEntity::class)
            ->createQueryBuilder('_p')
            ->select('COUNT(_p.id)')
            ->where('_p.user = :user')
            ->andWhere('_p.createdAt > :since')
            ->setParameter('user', $user)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();

        if ((int) $recent > 0) {
            throw new PostWriteException(self::ERROR_FLOOD);
        }
    }

    private function checkDoublePost(UserEntity $user, TopicEntity $topic, string $text) : void
    {
        $since = new DateTimeImmutable('-' . (int) $this->postSetting->get()['minDoublePostTimeInterval'] . ' seconds');

        $same = $this->em->getRepository(PostEntity::class)
            ->createQueryBuilder('_p')
            ->select('COUNT(_p.id)')
            ->where('_p.user = :user')
            ->andWhere('_p.topic = :topic')
            ->andWhere('_p.text = :text')
            ->andWhere('_p.createdAt > :since')
            ->setParameter('user', $user)
            ->setParameter('topic', $topic)
            ->setParameter('text', $text)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();

        if ((int) $same > 0) {
            throw new PostWriteException(self::ERROR_DOUBLE_POST);
        }
    }

    private function newPost(UserEntity $user, TopicEntity $topic, string $text, ?string $title, string $ipAddress) : PostEntity
    {
        $post = new PostEntity();
        $post->user = $user;
        $post->topic = $topic;
        $post->forum = $topic->forum;
        $post->category = $topic->category;
        $post->title = $title !== null && Strings::trim($title) !== '' ? Strings::trim($title) : null;
        $post->text = $text;
        $post->addIpAddress = $ipAddress;
        $post->editIpAddress = null;

        return $post;
    }

    private function rootForum(ForumEntity $forum) : ForumEntity
    {
        while ($forum->parent !== null) {
            $forum = $forum->parent;
        }

        return $forum;
    }
}
