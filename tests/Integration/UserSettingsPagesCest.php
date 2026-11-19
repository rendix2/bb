<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\LanguageEntity;
use App\Model\Entity\UserEntity;
use App\Settings\Setting;
use App\Settings\Users;
use ReflectionProperty;
use Tests\Support\IntegrationTester;

/**
 * The settings of the forum on Web:User:Edit (slice 4d): language, Markdown signature with a length limit, and the username only
 * when users.canChangeUserName allows it (unique case-insensitively). Every test runs in a transaction that is rolled back.
 */
class UserSettingsPagesCest
{
    /** @var array<string, mixed>|null the users parameters before a test changed them */
    private ?array $usersBefore = null;

    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
    }

    public function _after(IntegrationTester $I) : void
    {
        if ($this->usersBefore !== null) {
            $this->setUsers($I, $this->usersBefore);
            $this->usersBefore = null;
        }

        $I->em()->getConnection()->rollBack();
        $I->em()->clear();
    }

    public function theLanguageAndTheSignatureAreSaved(IntegrationTester $I) : void
    {
        $user = $I->createUser('Settings', role: 'Normal');
        $other = $this->otherLanguage($I, $user->language);
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.edit/');
        $I->seeResponseCodeIs(200);
        $I->dontSeeElement('#frm-editForm-username');

        $I->submitForm('#frm-editForm', [
            'name' => 'In',
            'surname' => 'Process',
            'language' => (string) $other->id,
            'signature' => 'My **bold** signature',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeInSource('<strong>bold</strong>');

        $saved = $I->reload(UserEntity::class, $user->id);
        $I->assertNotNull($saved);
        $I->assertSame($other->id, $saved->language->id);
        $I->assertSame('My **bold** signature', $saved->signature);

        // an empty signature is no signature
        $I->amOnPage('/web.user.edit/');
        $I->submitForm('#frm-editForm', ['signature' => '   ']);
        $I->assertNull($I->reload(UserEntity::class, $user->id)?->signature);
    }

    public function aTooLongSignatureIsRefused(IntegrationTester $I) : void
    {
        $user = $I->createUser('Long', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);
        $limit = (int) $I->grabService(Users::class)->get()['signatureMaxLength'];

        $I->amOnPage('/web.user.edit/');
        // characters, not bytes: the limit itself in "č" passes, one more does not
        $I->submitForm('#frm-editForm', ['signature' => str_repeat('č', $limit + 1)]);
        $I->assertNull($I->reload(UserEntity::class, $user->id)?->signature);

        $I->amOnPage('/web.user.edit/');
        $I->submitForm('#frm-editForm', ['signature' => str_repeat('č', $limit)]);
        $I->assertSame($limit, mb_strlen((string) $I->reload(UserEntity::class, $user->id)?->signature));
    }

    /**
     * Without users.canChangeUserName a posted username changes nothing (the control is not in the form at all).
     */
    public function theUsernameStaysWhenTheConfigurationForbidsTheChange(IntegrationTester $I) : void
    {
        $this->allowUsernameChange($I, false);
        $user = $I->createUser('Fixed', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.edit/');
        $I->dontSeeElement('#frm-editForm-username');
        $I->submitForm('#frm-editForm', ['username' => 'Changed' . random_int(100000, 999999)]);

        $I->assertSame($user->username, $I->reload(UserEntity::class, $user->id)?->username);
    }

    public function theUsernameChangesWhenAllowedAndStaysUniqueCaseInsensitively(IntegrationTester $I) : void
    {
        $this->allowUsernameChange($I, true);
        $taken = $I->createUser('Taken', role: 'Normal');
        $user = $I->createUser('Renamed', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.edit/');
        $I->assertSame($user->username, $I->grabValueFrom('#frm-editForm-username'));

        // taken by another account in other case
        $I->submitForm('#frm-editForm', ['username' => strtoupper($taken->username)]);
        $I->assertSame($user->username, $I->reload(UserEntity::class, $user->id)?->username);

        // too short
        $I->amOnPage('/web.user.edit/');
        $I->submitForm('#frm-editForm', ['username' => 'ab']);
        $I->assertSame($user->username, $I->reload(UserEntity::class, $user->id)?->username);

        // his own in other case is his
        $I->amOnPage('/web.user.edit/');
        $I->submitForm('#frm-editForm', ['username' => strtolower($user->username)]);
        $I->assertSame(strtolower($user->username), $I->reload(UserEntity::class, $user->id)?->username);

        $new = 'NewName' . random_int(100000, 999999);
        $I->amOnPage('/web.user.edit/');
        $I->submitForm('#frm-editForm', ['username' => $new]);
        $I->assertSame($new, $I->reload(UserEntity::class, $user->id)?->username);

        // the layout of the forum shows the username of the identity
        $I->amOnPage('/forum.index/');
        $I->see($new, '.own-profile-link');
    }

    private function allowUsernameChange(IntegrationTester $I, bool $allow) : void
    {
        $users = $I->grabService(Users::class)->get();
        assert(is_array($users));
        $this->usersBefore ??= $users;
        $users['canChangeUserName'] = $allow;
        $this->setUsers($I, $users);
    }

    /**
     * @param array<string, mixed> $users
     */
    private function setUsers(IntegrationTester $I, array $users) : void
    {
        (new ReflectionProperty(Setting::class, 'setting'))->setValue($I->grabService(Users::class), $users);
    }

    private function otherLanguage(IntegrationTester $I, LanguageEntity $current) : LanguageEntity
    {
        foreach ($I->em()->getRepository(LanguageEntity::class)->findAll() as $language) {
            if ($language->id !== $current->id) {
                return $language;
            }
        }

        $I->fail('The fixtures have one language only.');
    }

}
