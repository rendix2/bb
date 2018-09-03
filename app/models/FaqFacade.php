<?php

namespace App\Models;

/**
 * Description of FaqFacade
 *
 * @author rendix2
 */
class FaqFacade
{
    /**
     * 
     * @var FaqManager $faqManager
     */
    private $faqManager;
    
    /**
     *
     * @var FaqAnswersManager $faqAnswersManager
     */
    private $faqAnswersManager;

    /**
     * 
     * @param FaqManager        $faqManager
     * @param FaqAnswersManager $faqAnswersManager
     */
    public function __construct(FaqManager $faqManager, FaqAnswersManager $faqAnswersManager)
    {
        $this->faqManager        = $faqManager;
        $this->faqAnswersManager = $faqAnswersManager;
    }
    
    public function add(\Nette\Utils\ArrayHash $item_data)
    {
        
    }
    
    public function delete($item_id)
    {
        
    }   
    
}
