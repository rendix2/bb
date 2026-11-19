<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\GroupEntity;
use App\Model\Entity\UserGroupMemberEntity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Tests\Support\IntegrationTester;

/**
 * Membership of a user in a group (table group_member): the primary key (user_id, group_id) makes it unique.
 * Regression 2026-11-19: the table had its own id and nothing stopped the same membership from being stored twice.
 */
class GroupMemberCest
{
    public function theSameMembershipCannotBeStoredTwice(IntegrationTester $I) : void
    {
        $em = $I->em();
        $connection = $em->getConnection();
        $connection->beginTransaction();

        try {
            $user = $I->createUser('GroupMember');

            $group = new GroupEntity();
            $group->name = 'Test group';
            $group->moderator = $user;
            $em->persist($group);

            $first = new UserGroupMemberEntity();
            $first->user = $user;
            $first->group = $group;
            $em->persist($first);
            $em->flush();

            $I->assertNotNull($I->reload(UserGroupMemberEntity::class, ['user' => $user->id, 'group' => $group->id]));

            $duplicate = $em->getConnection()->createQueryBuilder()
                ->insert('group_member')
                ->values(['user_id' => ':user', 'group_id' => ':group', 'created_at' => 'now()'])
                ->setParameter('user', $user->id)
                ->setParameter('group', $group->id);

            $I->expectThrowable(
                UniqueConstraintViolationException::class,
                fn () => $duplicate->executeStatement(),
            );
        } finally {
            // nothing of the test stays in the database
            $connection->rollBack();
            $em->clear();
        }
    }

}
