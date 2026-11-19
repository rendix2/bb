<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\UserEntity;
use App\Model\Facade\UserFacade;
use App\UI\Web\User\Registration\RegistrationValues;
use Tests\Support\IntegrationTester;

/**
 * Regression 2026-11-19: users.language_id is NOT NULL and neither the registration nor the admin "create user" set a
 * language - every new account failed with a NOT NULL violation. A new user gets the language of the parameter
 * defaultLanguage (DefaultLanguageService).
 */
class RegistrationCest
{
    public function aRegisteredUserGetsTheDefaultLanguage(IntegrationTester $I) : void
    {
        $em = $I->em();
        $connection = $em->getConnection();
        $connection->beginTransaction();

        try {
            $username = 'Registration' . random_int(100000, 999999);

            $values = new RegistrationValues();
            $values->name = 'Reg';
            $values->surname = 'Istration';
            $values->username = $username;
            $values->email = strtolower($username) . '@example.com';
            $values->password = IntegrationTester::PASSWORD;

            $I->grabService(UserFacade::class)->register($values);

            $em->clear();
            $user = $em->getRepository(UserEntity::class)->findOneBy(['username' => $username]);

            $I->assertNotNull($user, 'The registration stored no user.');
            $I->assertSame('czech', $user->language->fileName);
            $I->assertFalse($user->isActive, 'A registered account waits for its activation.');
        } finally {
            // nothing of the test stays in the database
            $connection->rollBack();
            $em->clear();
        }
    }

}
