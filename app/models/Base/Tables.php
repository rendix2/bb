<?php

namespace App\Models;

/**
 * List of database tables
 *
 * @author rendix2
 * @packade App\Models
 */
#[\JetBrains\PhpStorm\Deprecated]

class Tables
{

    const PREFIX = '';

    
    const FAVOURITE_USERS_TABLE = self::PREFIX . 'favourite_users';

    
    const FORUM_TABLE = self::PREFIX . 'forums';
    
    const FORUMS2GROUPS_TABLE = self::PREFIX . 'forums2groups';
    

    
    const MODERATORS_TABLE = self::PREFIX . 'moderator';
    

    
    const TOPICS_TABLE = self::PREFIX . 'topic';
    
    const TOPIC_WATCH_TABLE = self::PREFIX . 'topics_watch';


    const USERS_TABLE = self::PREFIX . 'users';

}
