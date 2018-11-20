<?php

namespace App\Models;

use Nette\Utils\ArrayHash;

/**
 * Description of FaqFacade
 *
 * @author rendix2
 * @package App\Models
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
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->faqManager        = null;
        $this->faqAnswersManager = null;
    }

    /**
     * @param ArrayHash $item_data
     */
    public function add(ArrayHash $item_data)
    {
        
    }

    /**
     * @param $item_id
     */
    public function delete($item_id)
    {
        
    }
}
