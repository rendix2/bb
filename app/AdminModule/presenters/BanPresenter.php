<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule\Presenters;

use App\Controls\BootstrapForm;
use App\Models\BanManager;

/**
 * Description of BanPresenter
 *
 * @author rendi
 */
class BanPresenter extends Base\AdminPresenter
{
    
    public function __construct(BanManager $manager)
    {
        parent::__construct($manager);
    }

    protected function createComponentEditForm()
    {
        $form = new BootstrapForm();
        
        return $form;
    }

}
