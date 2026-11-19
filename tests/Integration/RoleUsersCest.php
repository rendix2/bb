<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\RoleEntity;
use App\Model\Entity\UserEntity;
use Tests\Support\IntegrationTester;

/**
 * Roles and users are joined by user_role only. Regression 2026-11-19: cascade 'remove' on the ManyToMany removed the
 * ENTITIES on the other side - deleting a role deleted every user who had it.
 */
class RoleUsersCest
{
    public function deletingARoleKeepsItsUsers(IntegrationTester $I) : void
    {
        $em = $I->em();
        $connection = $em->getConnection();
        $connection->beginTransaction();

        try {
            $user = $I->createUser('RoleUsers');

            $role = new RoleEntity();
            $role->name = 'Test role';
            $role->description = 'Role of RoleUsersCest';
            $user->addRoleEntity($role);
            $em->persist($role);
            $em->flush();

            $userId = $user->id;
            $em->remove($role);
            $em->flush();

            $I->assertNotNull($I->reload(UserEntity::class, $userId), 'Deleting the role deleted its user.');
            $I->assertSame(
                0,
                (int) $connection->createQueryBuilder()
                    ->select('COUNT(*)')
                    ->from('user_role')
                    ->where('user_id = :user')
                    ->setParameter('user', $userId)
                    ->executeQuery()
                    ->fetchOne(),
                'The join row of the deleted role stayed.',
            );
        } finally {
            // nothing of the test stays in the database
            $connection->rollBack();
            $em->clear();
        }
    }

}
