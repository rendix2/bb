<?php declare(strict_types=1);

namespace App\Model\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

/**
 * The permission flags of a forum: the defaults of logged-in users on the forum, and the grants of a group (group_forum)
 * or of one user (user_forum), which only add to the defaults. Evaluated by ForumPermissionService.
 */
#[Embeddable]
class ForumPermissions
{
    /** The flags, in the order of the forms of the administration. */
    public const array FLAGS = ['topicAdd', 'topicUpdate', 'topicDelete', 'postAdd', 'postUpdate', 'postDelete', 'topicThank', 'topicFastReply'];
    #[Column(type: Types::BOOLEAN, options: ['comment' => 'May start a topic'])]
    public bool $topicAdd = false;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'May edit his topic'])]
    public bool $topicUpdate = false;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'May delete his topic'])]
    public bool $topicDelete = false;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'May reply'])]
    public bool $postAdd = false;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'May edit his post'])]
    public bool $postUpdate = false;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'May delete his post'])]
    public bool $postDelete = false;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'May thank'])]
    public bool $topicThank = false;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'May use the fast reply'])]
    public bool $topicFastReply = false;

    /**
     * The usual defaults of a new forum for logged-in users: start topics, write, edit and delete own posts, thank.
     */
    public function grantDefaultsOfANewForum() : void
    {
        $this->topicAdd = true;
        $this->postAdd = true;
        $this->postUpdate = true;
        $this->postDelete = true;
        $this->topicThank = true;
        $this->topicFastReply = true;
    }

    /**
     * A new set with every flag that is set here or in the other one (grants only add).
     */
    public function union(self $other) : self
    {
        $union = new self();

        $union->topicAdd = $this->topicAdd || $other->topicAdd;
        $union->topicUpdate = $this->topicUpdate || $other->topicUpdate;
        $union->topicDelete = $this->topicDelete || $other->topicDelete;
        $union->postAdd = $this->postAdd || $other->postAdd;
        $union->postUpdate = $this->postUpdate || $other->postUpdate;
        $union->postDelete = $this->postDelete || $other->postDelete;
        $union->topicThank = $this->topicThank || $other->topicThank;
        $union->topicFastReply = $this->topicFastReply || $other->topicFastReply;

        return $union;
    }

    /**
     * No flag set: a grant like this adds nothing (the administration deletes it).
     */
    public function isEmpty() : bool
    {
        foreach (self::FLAGS as $flag) {
            if ($this->$flag) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets every flag (a missing one to false).
     *
     * @param array<string, bool> $flags flag => value
     */
    public function setFlags(array $flags) : void
    {
        foreach (self::FLAGS as $flag) {
            $this->$flag = $flags[$flag] ?? false;
        }
    }

}
