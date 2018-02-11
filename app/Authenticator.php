<?php

namespace App;

/**
 * Description of Authenticator
 *
 * @author rendi
 */
class Authenticator implements \Nette\Security\IAuthenticator {

    const ROLES = [1 => 'geust', 2 => 'registered', 3 => 'moderator', 4 => 'juniorAdmin', 5 => 'admin'];

    private $userManager;
    private $languageManager;

    public function __construct(\App\Models\UsersManager $userManger, \App\Models\LanguagesManager $languageManager) {
        $this->userManager = $userManger;
        $this->languageManager = $languageManager;
    }

    /**
     * 
     * @param array $credentials
     * @return \Nette\Security\Identity
     * @throws \Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($userName, $userPassword) = $credentials;

        $userData = $this->userManager->getByName($userName);
        $langData = $this->languageManager->getById($userData->user_lang_id);

        if (!$userData) {
            throw new \Nette\Security\AuthenticationException('User name not found.');
        }

        if (!$userData->user_active) {
            throw new \Nette\Security\AuthenticationException('User account is not active.');
        }

        if (!\Nette\Security\Passwords::verify($userPassword, $userData->user_password)) {
            throw new \Nette\Security\AuthenticationException('Pasword is incorrect.');
        }

        return new \Nette\Security\Identity($userData->user_id, self::ROLES[$userData->user_role_id], ['user_name' => $userData->user_name, 'lang_file_name' => $langData->lang_file_name]);
    }

}
