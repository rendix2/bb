<?php

namespace App\ModeratorModule;

/**
 * Description of IndexPresenter
 *
 * @author rendi
 */
class IndexPresenter extends \App\ForumModule\Presenters\Base\ForumPresenter
{
    public function __construct(\App\Models\PostsManager $manager) {
        parent::__construct($manager);
    }
    
}
