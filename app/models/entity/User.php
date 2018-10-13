<?php

namespace App\Models\Entity;

/**
 * Description of User
 *
 * @author rendi
 */
class User extends \App\Models\Entity\Base\Entity
{
        public $user_id;
        public $user_name;
        public $user_password;
        public $user_email;
        public $user_signature;
        public $user_active;
        public $user_post_count;
        public $user_topic_count;
        public $user_thank_count;
        public $user_watch_count;
        public $user_lang_id;
        public $user_role_id;
        public $user_avatar;
        public $user_register_time;
        public $user_last_login_time;
        public $user_last_post_time;
        public $user_activation_key;
    
    /**
     * 
     * @param int    $user_id
     * @param string $user_name
     * @param string $user_password
     * @param string $user_email
     * @param string $user_signature
     * @param bool   $user_active
     * @param int    $user_post_count
     * @param int    $user_topic_count
     * @param int    $user_thank_count
     * @param int    $user_watch_count
     * @param int    $user_lang_id
     * @param int    $user_role_id
     * @param string $user_avatar
     * @param int    $user_register_time
     * @param int    $user_last_login_time
     * @param int    $user_last_post_time
     * @param string $user_activation_key
     */
    public function __construct(
        $user_id,
        $user_name,
        $user_password,
        $user_email,
        $user_signature,
        $user_active,
        $user_post_count,
        $user_topic_count,
        $user_thank_count,
        $user_watch_count,
        $user_lang_id,
        $user_role_id,
        $user_avatar,
        $user_register_time,
        $user_last_login_time,
        $user_last_post_time,
        $user_activation_key
            
    ) {
        $this->user_id              = (int)$user_id;
        $this->user_name            = $user_name;
        $this->user_password        = $user_password;
        $this->user_email           = $user_email;
        $this->user_signature       = $user_signature;
        $this->user_active          = $user_active;
        $this->user_post_count      = (int)$user_post_count;
        $this->user_topic_count     = (int)$user_topic_count;
        $this->user_thank_count     = (int)$user_thank_count;
        $this->user_watch_count     = (int)$user_watch_count;
        $this->user_lang_id         = (int)$user_lang_id;
        $this->user_role_id         = (int)$user_role_id;
        $this->user_avatar          = $user_avatar;
        $this->user_register_time   = (int)$user_register_time;
        $this->user_last_login_time = (int)$user_last_login_time;
        $this->user_last_post_time  = (int)$user_last_post_time;
        $this->user_activation_key  = $user_activation_key;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\User
     */
    public static function get(\Dibi\Row $values)
    {
        return new User(
            $values->user_id,
            $values->user_name,
            $values->user_password,
            $values->user_email,
            $values->user_signature,
            $values->user_active,
            $values->user_post_count,
            $values->user_topic_count,
            $values->user_thank_count,
            $values->user_watch_count,
            $values->user_lang_id,
            $values->user_role_id,
            $values->user_avatar,
            $values->user_register_time,
            $values->user_last_login_time,
            $values->user_last_post_time,
            $values->user_activation_key
        );
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
