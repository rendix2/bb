<?php

namespace App\Controls;

/**
 * Description of JumpToForumControl
 *
 * @author rendi
 */
class JumpToForumControl extends \Nette\Application\UI\Control{
    
    private $forumManager;
    
    public function __construct(\App\Models\ForumsManager $forumManager) {
        parent::__construct();
        
        $this->forumManager = $forumManager;
    }
    
    public function render(){
        $this->template->setFile(__DIR__.'/templates/jumpToForum/jumpToForum.latte');
        
        $this->template->render();
    }

    protected function createComponentJumpToForum(){
        $form = new BootstrapForm();
        
        $form->addSelect('forum_id', null, $this->forumManager->getAllPairsCached('forum_name'));
        $form->addSubmit('send', 'Redirect');
        
        $form->onSuccess[] = [$this, 'jumpToForumSuccess'];
        return $form;
    }
    
    public function jumpToForumSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values){
        $this->getPresenter()->redirect(':Forum:Forum:default', $values->forum_id);
    }   
        
}
