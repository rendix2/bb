<?php

namespace App;

use App\Models\LanguagesManager;
use App\Models\ModeratorsManager;
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
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     * @var LanguagesManager $languagesManager
     */
    private $languagesManager;
    
    /**
     *
     * @var ModeratorsManager $moderatorsManager
     */
    private $moderatorsManager;

    /**
     * Authenticator constructor.
     *
     * @param UsersManager     $usersManger
     * @param LanguagesManager $languageManager
     */
    public function __construct(
            UsersManager $usersManger,
            LanguagesManager $languageManager,
            ModeratorsManager $moderatorsManager
    ) {
        $this->usersManager      = $usersManger;
        $this->languagesManager  = $languageManager;
        $this->moderatorsManager = $moderatorsManager;
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->usersManager      = null;
        $this->languagesManager  = null;
        $this->moderatorsManager = null;
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

        $userData = $this->usersManager->getByName($userName);

        if (!$userData) {
            throw new AuthenticationException('User name not found.', IAuthenticator::IDENTITY_NOT_FOUND);
        }
        
        $langData = $this->languagesManager->getById($userData->user_lang_id);
        
        if (!$langData) {
            throw new AuthenticationException('User account has set unknown language.', IAuthenticator::INVALID_CREDENTIAL);
        }

        if (!$userData->user_active) {
            throw new AuthenticationException('User account is not active.', IAuthenticator::INVALID_CREDENTIAL);
        }

        if (!Passwords::verify($userPassword, $userData->user_password)) {
            throw new AuthenticationException('Password is incorrect.', IAuthenticator::INVALID_CREDENTIAL);
        }
               
        $this->usersManager->update($userData->user_id, ArrayHash::from(['user_last_login_time' => time()]));
        
        $moderators = $this->moderatorsManager->getPairsByLeft($userData->user_id);
        
        $data =
            [
                'user_name'            => $userData->user_name,
                'lang_file_name'       => $langData->lang_file_name,
                'user_last_login_time' => $userData->user_last_login_time,
                'user_email'           => $userData->user_email,
                'moderator'            => $moderators
            ];

        return new Identity($userData->user_id, Authorization\Authorizator::ROLES[$userData->user_role_id], $data);
    }
}
