<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Core\UsernameAndPasswordAuthenticator;
use App\Model\Entity\UserEntity;
use App\UI\Web\User\ForgetPassword\Request\UserPasswordRequestAttemptCheckService;
use App\UI\Web\User\Login\UserLoginAttemptCheckService;
use InvalidArgumentException;
use Nette\Security\AuthenticationException;
use Tests\Support\IntegrationTester;

/**
 * The services behind the account pages: the repository of the users (case-insensitive lookups), the throttling of the password
 * requests and of the logins.
 *
 * bb: the two tests of the GeoIP lookup of web-project are not here, bb still has its older GeoIpService (http, no fallback).
 */
class AccountServicesCest
{
    public function theRepositoryFindsUsersByIdUuidAndCaseInsensitively(IntegrationTester $I) : void
    {
        $user = $I->createUser('inrepo');
        $repository = $I->em()->getRepository(UserEntity::class);

        $I->assertSame($user->id, $repository->findOneById((string) $user->id)?->id);
        $I->assertSame($user->id, $repository->findOneByUuid((string) $user->uuid)?->id);
        $I->assertSame($user->id, $repository->findOneByEmail('  ' . strtoupper($user->email) . ' ')?->id);
        $I->assertSame($user->id, $repository->findOneByUsername(strtoupper($user->username))?->id);
        $I->assertNull($repository->findOneByEmail('nobody-' . $user->email));
    }

    public function thePasswordRequestsOfAnAddressAndOfAnIpAreLimited(IntegrationTester $I) : void
    {
        $service = $I->grabService(UserPasswordRequestAttemptCheckService::class);
        $email = 'InThrottle' . random_int(100000, 999999) . '@example.com';
        $ip = '198.18.' . random_int(0, 255) . '.' . random_int(1, 254);

        $I->assertFalse($service->isEmailBlocked($email));
        $I->assertFalse($service->isIpBlocked($ip));

        for ($i = 0; $i < 5; $i++) {
            $service->logAttempt($email, $ip);
        }

        $I->assertTrue($service->isEmailBlocked(strtolower($email)));
        $I->assertTrue($service->isIpBlocked($ip));

        $I->expectThrowable(InvalidArgumentException::class, static fn () => $service->logAttempt($email, 'not-an-ip'));
        $I->expectThrowable(InvalidArgumentException::class, static fn () => $service->isIpBlocked('not-an-ip'));
    }

    public function theLoginOfBlockedUsernameIsRefusedBeforeThePasswordIsChecked(IntegrationTester $I) : void
    {
        $user = $I->createUser('inblocked');
        $attempts = $I->grabService(UserLoginAttemptCheckService::class);

        for ($i = 0; $i < 5; $i++) {
            $attempts->logAttempt(strtoupper($user->username), '198.18.0.1');
        }

        $authenticator = $I->grabService(UsernameAndPasswordAuthenticator::class);

        try {
            $authenticator->authenticate($user->username, IntegrationTester::PASSWORD);
            $I->fail('A blocked username logged in.');
        } catch (AuthenticationException $exception) {
            $I->assertSame('web-user-login.error.userBlocked', $exception->getMessage());
        }

        $attempts->clearAttempts($user->username);
        $I->assertSame($user->id, $authenticator->authenticate($user->username, IntegrationTester::PASSWORD)->getId());
    }
}
