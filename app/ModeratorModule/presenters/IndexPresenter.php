<?php

namespace App\ModeratorModule\Presenters;

use App\ForumModule\Presenters\Base\ForumPresenter;
use App\Models\PostsManager;

/**
 * Description of IndexPresenter
 *
 * @author rendi
 */
class IndexPresenter extends \App\Presenters\Base\BasePresenter
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
