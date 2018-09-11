<?php

namespace App\Models;

use Nette\Utils\ArrayHash;

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
