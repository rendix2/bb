<?php

use App\Models\Entity\Base\Entity;
use App\Models\Entity\User;
use Dibi\Row;
use Nette\Utils\ArrayHash;

namespace App\Models\Entity;

/**
 * Description of User
 *
 * @author rendix2
 */
class User extends \App\Models\Entity\Base\Entity
{

    /**
     *
     * @var int $user_id
     */
    private $user_id;
    
    /**
     *
     * @var string $user_name
     */
    private $user_name;
    
    /**
     *
     * @var string $user_password
     */
    private $user_password;
    
    /**
     *
     * @var string $user_email 
     */
    private $user_email;
    
    /**
     *
     * @var string $user_signature 
     */
    private $user_signature;
    
    /**
     *
     * @var string $user_active
     */
    private $user_active;
    
    /**
     *
     * @var int $user_post_count
     */
    private $user_post_count;
    
    /**
     * @var int $user_topic_count
     */
    private $user_topic_count;
    
    /**
     *
     * @var int $user_thank_count
     */
    private $user_thank_count;
    
    /**
     *
     * @var int $user_watch_count 
     */
    private $user_watch_count;
    
    /**
     *
     * @var int $user_lang_id 
     */
    private $user_lang_id;
    
    /**
     *
     * @var int $user_role_id 
     */
    private $user_role_id;
    
    /**
     *
     * @var string $user_avatar
     */
    private $user_avatar;
    
    /**
     *
     * @var int $user_register_time
     */
    private $user_register_time;
    
    /**
     *
     * @var int $user_last_login_time
     */
    private $user_last_login_time;
    
    /**
     *
     * @var int $user_last_post_time
     */
    private $user_last_post_time;
    
    /**
     *
     * @var string $user_activation_key 
     */
    private $user_activation_key;

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function getUser_name()
    {
        return $this->user_name;
    }

    public function getUser_password()
    {
        return $this->user_password;
    }

    public function getUser_email()
    {
        return $this->user_email;
    }

    public function getUser_signature()
    {
        return $this->user_signature;
    }

    public function getUser_active()
    {
        return $this->user_active;
    }

    public function getUser_post_count()
    {
        return $this->user_post_count;
    }

    public function getUser_topic_count()
    {
        return $this->user_topic_count;
    }

    public function getUser_thank_count()
    {
        return $this->user_thank_count;
    }

    public function getUser_watch_count()
    {
        return $this->user_watch_count;
    }

    public function getUser_lang_id()
    {
        return $this->user_lang_id;
    }

    public function getUser_role_id()
    {
        return $this->user_role_id;
    }

    public function getUser_avatar()
    {
        return $this->user_avatar;
    }

    public function getUser_register_time()
    {
        return $this->user_register_time;
    }

    public function getUser_last_login_time()
    {
        return $this->user_last_login_time;
    }

    public function getUser_last_post_time()
    {
        return $this->user_last_post_time;
    }

    public function getUser_activation_key()
    {
        return $this->user_activation_key;
    }

    public function setUser_id($user_id)
    {
        $this->user_id = self::makeInt($user_id);
        return $this;
    }

    public function setUser_name($user_name)
    {
        $this->user_name = $user_name;
        return $this;
    }

    public function setUser_password($user_password)
    {
        $this->user_password = $user_password;
        return $this;
    }

    public function setUser_email($user_email)
    {
        $this->user_email = $user_email;
        return $this;
    }

    public function setUser_signature($user_signature)
    {
        $this->user_signature = $user_signature;
        return $this;
    }

    public function setUser_active($user_active)
    {
        $this->user_active = self::makeBool($user_active);
        return $this;
    }

    public function setUser_post_count($user_post_count)
    {
        $this->user_post_count = self::makeInt($user_post_count);
        return $this;
    }

    public function setUser_topic_count($user_topic_count)
    {
        $this->user_topic_count = self::makeInt($user_topic_count);
        return $this;
    }

    public function setUser_thank_count($user_thank_count)
    {
        $this->user_thank_count = self::makeInt($user_thank_count);
        return $this;
    }

    public function setUser_watch_count($user_watch_count)
    {
        $this->user_watch_count = self::makeInt($user_watch_count);
        return $this;
    }

    public function setUser_lang_id($user_lang_id)
    {
        $this->user_lang_id = self::makeInt($user_lang_id);
        return $this;
    }

    public function setUser_role_id($user_role_id)
    {
        $this->user_role_id = self::makeInt($user_role_id);
        return $this;
    }

    public function setUser_avatar($user_avatar)
    {
        $this->user_avatar = $user_avatar;
        return $this;
    }

    public function setUser_register_time($user_register_time)
    {
        $this->user_register_time = self::makeInt($user_register_time);
        return $this;
    }

    public function setUser_last_login_time($user_last_login_time)
    {
        $this->user_last_login_time = self::makeInt($user_last_login_time);
        return $this;
    }

    public function setUser_last_post_time($user_last_post_time)
    {
        $this->user_last_post_time = self::makeInt($user_last_post_time);
        return $this;
    }

    public function setUser_activation_key($user_activation_key)
    {
        $this->user_activation_key = $user_activation_key;
        return $this;
    }

    /**
     * 
     * @param Row $values
     * 
     * @return User
     */
    public static function setFromRow(\Dibi\Row $values)
    {
        $user = new User();

        if (isset($values->user_id)) {
            $user->setUser_id($values->user_id);
        }

        if (isset($values->user_name)) {
            $user->setUser_name($values->user_name);
        }

        if (isset($values->user_password)) {
            $user->setUser_password($values->user_password);
        }

        if (isset($values->user_email)) {
            $user->setUser_email($values->user_email);
        }

        if (isset($values->user_signature)) {
            $user->setUser_signature($values->user_signature);
        }

        if (isset($values->user_active)) {
            $user->setUser_active($values->user_active);
        }

        if (isset($values->user_post_count)) {
            $user->setUser_post_count($values->user_post_count);
        }

        if (isset($values->user_topic_count)) {
            $user->setUser_topic_count($values->user_topic_count);
        }

        if (isset($values->user_thank_count)) {
            $user->setUser_thank_count($values->user_thank_count);
        }

        if (isset($values->user_watch_count)) {
            $user->setUser_watch_count($values->user_watch_count);
        }

        if (isset($values->user_lang_id)) {
            $user->setUser_lang_id($values->user_lang_id);
        }

        if (isset($values->user_role_id)) {
            $user->setUser_role_id($values->user_role_id);
        }

        if (isset($values->user_avatar)) {
            $user->setUser_avatar($values->user_avatar);
        }

        if (isset($values->user_register_time)) {
            $user->setUser_register_time($values->user_register_time);
        }

        if (isset($values->user_last_login_time)) {
            $user->setUser_last_login_time($values->user_last_login_time);
        }

        if (isset($values->user_last_post_time)) {
            $user->setUser_last_post_time($values->user_last_post_time);
        }

        if (isset($values->user_activation_key)) {
            $user->setUser_activation_key($values->user_activation_key);
        }

        return $user;
    }

    public static function setFromArrayHash(ArrayHash $values)
    {
        $user = new User();

        if (isset($values->user_id)) {
            $user->setUser_id($values->user_id);
        }

        if (isset($values->user_name)) {
            $user->setUser_name($values->user_name);
        }

        if (isset($values->user_password)) {
            $user->setUser_password($values->user_password);
        }

        if (isset($values->user_email)) {
            $user->setUser_email($values->user_email);
        }

        if (isset($values->user_signature)) {
            $user->setUser_signature($values->user_signature);
        }

        if (isset($values->user_active)) {
            $user->setUser_active($values->user_active);
        }

        if (isset($values->user_post_count)) {
            $user->setUser_post_count($values->user_post_count);
        }

        if (isset($values->user_topic_count)) {
            $user->setUser_topic_count($values->user_topic_count);
        }

        if (isset($values->user_thank_count)) {
            $user->setUser_thank_count($values->user_thank_count);
        }

        if (isset($values->user_watch_count)) {
            $user->setUser_watch_count($values->user_watch_count);
        }

        if (isset($values->user_lang_id)) {
            $user->setUser_lang_id($values->user_lang_id);
        }

        if (isset($values->user_role_id)) {
            $user->setUser_role_id($values->user_role_id);
        }

        if (isset($values->user_avatar)) {
            $user->setUser_avatar($values->user_avatar);
        }

        if (isset($values->user_register_time)) {
            $user->setUser_register_time($values->user_register_time);
        }

        if (isset($values->user_last_login_time)) {
            $user->setUser_last_login_time($values->user_last_login_time);
        }

        if (isset($values->user_last_post_time)) {
            $user->setUser_last_post_time($values->user_last_post_time);
        }

        if (isset($values->user_activation_key)) {
            $user->setUser_activation_key($values->user_activation_key);
        }

        return $user;
    }

    /**
     * 
     * @return array
     */
    public function getArray()
    {
        $res = [];

        if (isset($this->user_id)) {
            $res['user_id'] = $this->user_id;
        }

        if (isset($this->user_name)) {
            $res['user_name'] = $this->user_name;
        }

        if (isset($this->user_password)) {
            $res['user_password'] = $this->user_password;
        }

        if (isset($this->user_email)) {
            $res['user_email'] = $this->user_email;
        }

        if (isset($this->user_signature)) {
            $res['user_signature'] = $this->user_signature;
        }

        if (isset($this->user_active)) {
            $res['user_active'] = $this->user_active;
        }

        if (isset($this->user_post_count)) {
            $res['user_post_count'] = $this->user_post_count;
        }

        if (isset($this->user_topic_count)) {
            $res['user_topic_count'] = $this->user_topic_count;
        }

        if (isset($this->user_thank_count)) {
            $res['user_thank_count'] = $this->user_thank_count;
        }

        if (isset($this->user_watch_count)) {
            $res['user_watch_count'] = $this->user_watch_count;
        }

        if (isset($this->user_lang_id)) {
            $res['user_lang_id'] = $this->user_lang_id;
        }

        if (isset($this->user_role_id)) {
            $res['user_role_id'] = $this->user_role_id;
        }

        if (isset($this->user_avatar)) {
            $res['user_avatar'] = $this->user_avatar;
        }

        if (isset($this->user_register_time)) {
            $res['user_register_time'] = $this->user_register_time;
        }

        if (isset($this->user_last_login_time)) {
            $res['user_last_login_time'] = $this->user_last_login_time;
        }

        if (isset($this->user_last_post_time)) {
            $res['user_last_post_time'] = $this->user_last_post_time;
        }

        if (isset($this->user_activation_key)) {
            $res['user_activation_key'] = $this->user_activation_key;
        }

        return $res;
    }
}
