<?php

namespace App\ForumModule\Presenters\Base;

/**
 * Description of ForumPresenter
 *
 * @author rendi
 */
abstract class ForumPresenter extends \App\Presenters\Base\ManagerPresenter {

    /**
     *
     * @var App\Translator $transaltor
     */
    private $forumTranslator;
    private $form;
    private $bootStrapForm;
    private $authorizator;

    public function __construct(\App\Models\Manager $manager) {
        parent::__construct($manager);

        $this->form = new \Nette\Application\UI\Form();
        $this->bootStrapForm = new \App\Controls\BootstrapForm();
    }

    public function injectAuthorizator(\App\Authorizator $authorizator) {
        $this->authorizator = $authorizator;
    }

    /**
     * 
     * @return \Nette\Application\UI\Form
     */
    public function getForm() {
        return $this->form;
    }

    /**
     * 
     * @return \App\Controls\BootstrapForm
     */
    public function getBootStrapForm() {
        return $this->bootStrapForm;
    }

    public function startup() {
        parent::startup();

        $this->forumTranslator = new \App\Translator('Forum', $this->getUser()->getIdentity()->getData()['lang_file_name']);
        $this->getUser()->setAuthorizator($this->authorizator->getAcl());
    }

    public function beforeRender() {
        parent::beforeRender();

        $this->template->setTranslator($this->forumTranslator);
    }

    public function getForumTranslator() {
        return $this->forumTranslator;
    }

}
