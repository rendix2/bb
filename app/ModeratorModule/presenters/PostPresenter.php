<?php

namespace App\ModeratorModule;

use App\Models\PostsManager;

/**
 * Description of PostPresenter
 *
 * @author rendi
 */
class PostPresenter extends ModeratorPresenter
{
    /**
     * PostPresenter constructor.
     *
     * @param PostsManager $manager
     */
    public function __construct(PostsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     *
     */
    protected function createComponentEditForm()
    {
    }
}
