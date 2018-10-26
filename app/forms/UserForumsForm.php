<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\Models\ForumsManager;
use App\Models\Users2ForumsManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of UserForumsForm
 *
 * @author rendix2
 */
class UserForumsForm extends Control
{
    /**
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;
    
    /**
     *
     * @var Users2ForumsManager $users2ForumsManager,
     */
    private $users2ForumsManager;
    
    /**
     *
     * @var ITranslator $translator 
     */
    private $translator;   
    
    /**
     * 
     * @param ForumsManager       $forumsManager
     * @param Users2ForumsManager $users2ForumsManager
     * @param ITranslator         $translator
     */
    public function __construct(
        ForumsManager $forumsManager,
        Users2ForumsManager $users2ForumsManager,
        ITranslator $translator
    ) {        
        $this->forumsManager       = $forumsManager;
        $this->users2ForumsManager = $users2ForumsManager;
        $this->translator          = $translator;
    }
    
    public function __destruct()
    {
        $this->forumsManager       = null;
        $this->users2ForumsManager = null;
        $this->translator          = null;
    }

        public function render()
    {
        $sep = DIRECTORY_SEPARATOR;
        
        $this->template->setFile(__DIR__ . $sep . 'templates' . $sep . 'userForumsForm.latte');
        $this->template->setTranslator($this->translator);

        $id     = $this->presenter->getParameter('id');
        $data   = [];
        $forums = $this->users2ForumsManager->getAllByLeft($id);

        foreach ($forums as $permission) {
            $data[$permission->forum_id]['post_add']     = $permission->post_add;
            $data[$permission->forum_id]['post_update']    = $permission->post_update;
            $data[$permission->forum_id]['post_delete']  = $permission->post_delete;
            $data[$permission->forum_id]['topic_add']    = $permission->topic_add;
            $data[$permission->forum_id]['topic_update']   = $permission->topic_update;
            $data[$permission->forum_id]['topic_delete'] = $permission->topic_delete;
            $data[$permission->forum_id]['topic_thank']  = $permission->topic_thank;
            $data[$permission->forum_id]['topic_fast_reply']  = $permission->topic_fast_reply;
        }

        $this->template->countOfUsers = $this->users2ForumsManager->getCountByRight($id);
        $this->template->permissions  = $data; 
         $this->template->forums      = $this->forumsManager->createForums($this->forumsManager->getAllCached(), 0);
                
        $this->template->render();
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    private function map(array $data)
    {
        $result = [];
        
        foreach ($this->forumsManager->getAllCached() as $value) {
            $result[$value->forum_id] = false;
            
            foreach ($data as $value2) {
                if ($value->forum_id === (int)$value2) {
                    $result[$value->forum_id] = true;
                }
            }
        }

        return $result;
    }    
    
   /**
     * @return BootstrapForm
     */
    public function createComponentForumsForm()
    {
        $form = BootstrapForm::create();

        $form->addSubmit('send_forum', 'Send');
        $form->onSuccess[] = [$this, 'forumsSuccess'];
        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function forumsSuccess(Form $form, ArrayHash $values)
    {
        $forums  = $form->getHttpData($form::DATA_TEXT, 'forums[]');
        $user_id = $this->presenter->getParameter('id');
        
        $post_add         = $form->getHttpData($form::DATA_TEXT, 'post_add[]');
        $post_update      = $form->getHttpData($form::DATA_TEXT, 'post_update[]');
        $post_delete      = $form->getHttpData($form::DATA_TEXT, 'post_delete[]');
        $topic_add        = $form->getHttpData($form::DATA_TEXT, 'topic_add[]');
        $topic_update     = $form->getHttpData($form::DATA_TEXT, 'topic_update[]');
        $topic_delete     = $form->getHttpData($form::DATA_TEXT, 'topic_delete[]');
        $forum_id         = $form->getHttpData($form::DATA_TEXT, 'forum_id[]');
        $topic_thank      = $form->getHttpData($form::DATA_TEXT, 'topic_thank[]');
        $topic_fast_reply = $form->getHttpData($form::DATA_TEXT, 'topic_fast_reply[]');
        
        $count = $this->forumsManager->getCount();

        $users  = [];
        $forums = [];

        foreach ($this->forumsManager->getAllCached() as $forum) {
            $forums[$forum->forum_id] = $forum->forum_id;
            $users[$forum->forum_id] = (int)$user_id;
        }

        $data = [
            'post_add'         => $this->map(array_pad($post_add, $count + 1, 0)),
            'post_update'      => $this->map(array_pad($post_update, $count + 1, 0)),
            'post_delete'      => $this->map(array_pad($post_delete, $count + 1, 0)),
            'topic_add'        => $this->map(array_pad($topic_add, $count + 1, 0)),
            'topic_update'     => $this->map(array_pad($topic_update, $count + 1, 0)),
            'topic_delete'     => $this->map(array_pad($topic_delete, $count + 1, 0)),
            'topic_thank'      => $this->map(array_pad($topic_thank, $count + 1, 0)),
            'topic_fast_reply' => $this->map(array_pad($topic_fast_reply, $count + 1, 0)),
            'forum_id'         => $forums,
            'user_id'          => $users
        ];
        
        $this->users2ForumsManager->deleteByLeft($user_id);
        $this->users2ForumsManager->addNative($data);
        
        $this->presenter->flashMessage('Forum was saved.', \App\Presenters\Base\BasePresenter::FLASH_MESSAGE_SUCCESS);
        $this->presenter->redirect('User:edit', $user_id);
    }    
}
