<?php

namespace App;

use App\Models\LanguagesManager;
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
 */
class Authenticator implements IAuthenticator
{
    /**
     * @var array
     */
    const ROLES
        = [
            1 => 'guest',
            2 => 'registered',
            3 => 'moderator',
            4 => 'juniorAdmin',
            5 => 'admin'
        ];
    
    /**
     * @var UsersManager $userManager
     */
    private $userManager;

    /**
     * @var LanguagesManager $languageManager
     */
    private $languageManager;

    /**
     * Authenticator constructor.
     *
     * @param UsersManager     $userManger
     * @param LanguagesManager $languageManager
     */
    public function __construct(UsersManager $userManger, LanguagesManager $languageManager)
    {
        $this->userManager     = $userManger;
        $this->languageManager = $languageManager;
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
        list($userName, $userPassword) = $credentials;

        $userData = $this->userManager->getByName($userName);

        if (!$userData) {
            throw new AuthenticationException('User name not found.', IAuthenticator::IDENTITY_NOT_FOUND);
        }
        
        $langData = $this->languageManager->getById($userData->user_lang_id);
        
        if (!$langData) {
            throw new AuthenticationException('User account has set unknow language.', IAuthenticator::INVALID_CREDENTIAL);
        }

        if (!$userData->user_active) {
            throw new AuthenticationException('User account is not active.', IAuthenticator::INVALID_CREDENTIAL);
        }

        if (!Passwords::verify($userPassword, $userData->user_password)) {
            throw new AuthenticationException('Password is incorrect.', IAuthenticator::INVALID_CREDENTIAL);
        }
               
        $this->userManager->update($userData->user_id, ArrayHash::from(['user_last_login_time' => time()]));
        
        $data =
            [
                'user_name'            => $userData->user_name,
                'lang_file_name'       => $langData->lang_file_name,
                'user_last_login_time' => $userData->user_last_login_time,
                'user_email'           => $userData->user_email
            ];

        return new Identity($userData->user_id, self::ROLES[$userData->user_role_id], $data);
    }
}
