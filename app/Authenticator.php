<?php

namespace App;

use App\Authorization\Authorizator;
use App\Model\Repository\LanguageRepository;
use App\Model\Repository\UserRepository;
use App\Models\LanguageManager;
use App\Models\ModeratorManager;
use App\Models\UsersManager;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Utils\ArrayHash;

/**
 * Description of Authenticator
 *
 * @author rendix2
 * @package App
 */
class Authenticator implements IAuthenticator
{
    /**
     * @var UsersManager $usersManager
     */
    private UsersManager $usersManager;

    /**
     * @var LanguageManager $languagesManager
     */
    private LanguageManager $languagesManager;

    /**
     *
     * @var ModeratorManager $moderatorsManager
     */
    private ModeratorManager $moderatorsManager;

    /**
     * Authenticator constructor.
     *
     * @param UsersManager $usersManger
     * @param LanguageManager $languageManager
     * @param ModeratorManager $moderatorsManager
     */
    public function __construct(
        UsersManager                        $usersManger,
        LanguageManager                     $languageManager,
        ModeratorManager                    $moderatorsManager,

        private readonly LanguageRepository $languageRepository,
        private readonly UserRepository     $userRepository,

        private readonly Passwords          $passwords,
    )
    {
        $this->usersManager = $usersManger;
        $this->languagesManager = $languageManager;
        $this->moderatorsManager = $moderatorsManager;
    }

    /**
     *
     * @param array $credentials
     *
     * @return Identity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        [$userName, $userPassword] = $credentials;

        $userEntity = $this->userRepository->findOneByUsername($userName);

        if (!$userEntity) {
            throw new AuthenticationException('Incorrect credentials.', IAuthenticator::IDENTITY_NOT_FOUND);
        }

        $languageEntity = $this->languageRepository
            ->findOneBy(
                [
                    'id' => $userEntity->language->id,
                ]
            );

        if (!$languageEntity) {
            throw new AuthenticationException(
                'User account has set unknown language.',
                IAuthenticator::INVALID_CREDENTIAL
            );
        }

        if (!$userEntity->isActive) {
            throw new AuthenticationException('User account is not active.', IAuthenticator::INVALID_CREDENTIAL);
        }

        if (!$this->passwords->verify($userPassword, $userEntity->password)) {
            throw new AuthenticationException('Incorrect credentials.', IAuthenticator::INVALID_CREDENTIAL);
        }

        $this->usersManager->update($userEntity->id, ArrayHash::from(['user_last_login_time' => time()]));

        $moderators = $this->moderatorsManager->getPairsByLeft($userEntity->id);

        $data =
            [
                'user_name' => $userEntity->username,
                'lang_file_name' => $languageEntity->lang_file_name,
                'user_last_login_time' => $userEntity->user_last_login_time,
                'user_email' => $userEntity->email,
                'moderator' => $moderators
            ];

        return new Identity($userEntity->id, Authorizator::ROLES[$userEntity->user_role_id], $data);
    }
}
