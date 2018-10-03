<?php

namespace App\Entity;

/**
 * Description of User
 *
 * @author rendi
 */
class User
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
        $this->user_id              = $user_id;
        $this->user_name            = $user_name;
        $this->user_password        = $user_password;
        $this->user_email           = $user_email;
        $this->user_signature       = $user_signature;
        $this->user_active          = $user_active;
        $this->user_post_count      = $user_post_count;
        $this->user_topic_count     = $user_topic_count;
        $this->user_thank_count     = $user_thank_count;
        $this->user_watch_count     = $user_watch_count;
        $this->user_lang_id         = $user_lang_id;
        $this->user_role_id         = $user_role_id;
        $this->user_avatar          = $user_avatar;
        $this->user_register_time   = $user_register_time;
        $this->user_last_login_time = $user_last_login_time;
        $this->user_last_post_time  = $user_post_count;
        $this->user_activation_key  = $user_activation_key;
    }
    
}
