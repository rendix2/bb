<?php

namespace App\ModeratorModule;

use App\ForumModule\Presenters\Base\ForumPresenter;
use App\Models\PostsManager;

/**
 * Description of IndexPresenter
 *
 * @author rendi
 */
class IndexPresenter extends ForumPresenter
{
    /**
     * IndexPresenter constructor.
     *
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
    {
        parent::__construct($manager);
    }
}
