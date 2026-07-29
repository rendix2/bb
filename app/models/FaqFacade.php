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
     * FaqFacade constructor.
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
     * @param int $item_id
     */
    public function delete($item_id)
    {
    }
}
