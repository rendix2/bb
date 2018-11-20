<?php

namespace App\Models;

/**
 * Description of Tables
 *
 * @author rendix2
 * @packade App\Models
 */
class Tables
{

    const PREFIX = '';
    
    
    const BANS_TABLE = self::PREFIX . 'bans';
    
    const CATEGORIES_TABLE = self::PREFIX . 'categories';
    
    const FAVOURITE_USERS_TABLE = self::PREFIX . 'favourite_users';
    
    const FILES_TABLE = self::PREFIX . 'files';
    
    const FORUM_TABLE = self::PREFIX . 'forums';
    
    const FORUMS2GROUPS_TABLE = self::PREFIX . 'forums2groups';
    
    const GROUPS_TABLE = self::PREFIX . 'groups';
    
    const LANGUAGES_TABLE = self::PREFIX . 'languages';
    
    const MAILS_TABLE = self::PREFIX . 'mails';
    
    const MAILS2USERS_TABLE = self::PREFIX . 'mails2users';
    
    const MODERATORS_TABLE = self::PREFIX . 'moderators';
    
    const PM_TABLE = self::PREFIX . 'pm';
    
    const POLLS_TABLE = self::PREFIX . 'polls';
    
    const POLLS_ANSWERS_TABLE = self::PREFIX . 'polls_answers';
    
    const POLLS_VOTES_TABLE = self::PREFIX . 'polls_votes';
    
    const POSTS_TABLE = self::PREFIX . 'posts';
    
    const POSTS2FILES_TABLE = self::PREFIX . 'posts2files';
    
    const POSTS_HISTORY_TABLE = self::PREFIX . 'posts_history';
    
    const RANKS_TABLE = self::PREFIX . 'ranks';
    
    const REPORTS_TABLE = self::PREFIX . 'reports';
    
    const SESSIONS_TABLE = self::PREFIX . 'sessions';
    
    const SMILES_TABLE = self::PREFIX . 'smiles';
    
    const THANKS_TABLE = self::PREFIX . 'thanks';
    
    const TOPICS_TABLE = self::PREFIX . 'topics';
    
    const TOPIC_WATCH_TABLE = self::PREFIX . 'topics_watch';
    
    const TRANSLATIONS_TABLE = self::PREFIX . 'translations';

    const USERS_TABLE = self::PREFIX . 'users';

    const USERS2FORUMS_TABLE = self::PREFIX . 'users2forums';
    
    const USERS2GROUPS_TABLE = self::PREFIX . 'users2groups';
}
