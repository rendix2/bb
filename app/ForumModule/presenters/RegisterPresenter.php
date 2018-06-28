<?php

namespace App\ForumModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\LanguagesManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of RegisterPresenter
 *
 * @author rendi
 */
class RegisterPresenter extends BasePresenter
{
    private $translator;   
    
    private $languageManager;

    /**
     * RegisterPresenter constructor.
     *
     * @param LanguagesManager $languageManger
     */
    public function __construct(LanguagesManager $languageManger)
    {
        parent::__construct();
        
        $this->languageManager = $languageManger;
    }
    
    public function startup()
    {
        parent::startup();
        
        $this->translator = $this->translatorFactory->forumTranslatorFactory();
                
        $this->template->setTranslator($this->translator);
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentRegisterUser()
    {
        $form = new BootstrapForm();
        $form->addText('user_name', 'User name:');
        $form->addPassword('user_password', 'User password:');
        $form->addPassword('user_password2', 'User password for check:');
        $form->addEmail('user_email', 'User email:');
        $form->addSelect('user_lang_id', 'User lang:', $this->languageManager->getAllPairsCached('lang_name'));
        $form->addSubmit('send', 'User register');
        
        $form->onValidate[] = [$this, 'registerOnValidate'];
        $form->onSuccess[]  = [$this, 'registerUserSuccess'];
        
        return $form;
    }

    /**
     *
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function registerOnValidate(Form $form, ArrayHash $values)
    {
    }

    /**
     *
     * @param Form $form
     * @param ArrayHash $values
     */
    public function registerUserSuccess(Form $form, ArrayHash $values)
    {
    }

    public function renderDefault()
    {
    }
}
