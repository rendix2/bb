<?php

/**
 * Description of PostPresenter
 *
 * @author rendi
 */
class PostPresenter extends ModeratorPresenter {
    
    public function __construct(App\Models\PostsManager $manager) {
        parent::__construct($manager);
    }

        protected function createComponentEditForm() {
        
    }

}
