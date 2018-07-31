<?php

namespace App\ModeratorModule\Presenters;

use App\Models\PostsManager;
use App\Presenters\Base\BasePresenter;

/**
 * Description of IndexPresenter
 *
 * @author rendi
 */
class IndexPresenter extends BasePresenter
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
