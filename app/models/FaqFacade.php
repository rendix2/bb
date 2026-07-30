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
     * FaqFacade constructor.
     *
     * @param FaqManager        $faqManager
     * @param FaqAnswersManager $faqAnswersManager
     */
    public function __construct(
        private readonly FaqManager        $faqManager,
        private readonly FaqAnswersManager $faqAnswersManager
    )
    {
    }

    public function add(ArrayHash $item_data)
    {
    }

    public function delete($item_id)
    {
    }
}
