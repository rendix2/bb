<?php

/**
 * Description of TopicPresenter
 *
 * @author rendi
 */
class TopicPresenter extends ModeratorPresenter {
    
    public function __construct(\App\Models\TopicsManager $manager) {
        parent::__construct($manager);
    }

        protected function createComponentEditForm() {
        
    }

}
