<?php

namespace App\Models;

/**
 * Description of Tables
 *
 * @author rendi
 */
class Tables {

    const PREFIX = '';
       
    const USERS_TABLE = self::PREFIX . 'users';
    
    const FORUM_TABLE = self::PREFIX . 'forums';
    
    const CATEGORIES_TABLE = self::PREFIX . 'categories';
    
    const TOPICS_TABLE = self::PREFIX. 'topics';
    
    const POSTS_TABLES = self::PREFIX . 'posts';
    
    const ROLES_TABLE = self::PREFIX . 'roles';
    
    const THANKS_TABLE = self::PREFIX . 'thanks';
    
    const USERS2ROLES_TABLE = self::PREFIX . 'users2roles';
    
    const USERS2GROUPS_TABLE = self::PREFIX . 'users2groups';
    
    const GROUPS_TABLE = self::PREFIX . 'groups';
    
    const LANGUAGES_TABLE = self::PREFIX . 'languages';
    
    const FORUMS2GROUPS_TABLE = self::PREFIX . 'forums2groups';
    
    const TOPIC_WATCH_TABLE = self::PREFIX . 'topics_watch';
    
}
