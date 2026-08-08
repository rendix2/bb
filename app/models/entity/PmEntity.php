<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Application\Attributes\Deprecated;
use Nette\Utils\ArrayHash;

/**
 * Description of PmEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
#[\JetBrains\PhpStorm\Deprecated]
class PmEntity extends Entity
{
    /**
     *
     * @var int $pm_id
     */
    private $pm_id;

    /**
     *
     * @var int $pm_user_id_from
     */
    private $pm_user_id_from;

    /**
     *
     * @var int $pm_user_id_to
     */
    private $pm_user_id_to;

    /**
     *
     * @var string $pm_subject
     */
    private $pm_subject;

    /**
     *
     * @var int $pm_text
     */
    private $pm_text;

    /**
     *
     * @var string $pm_status
     */
    private $pm_status;

    /**
     *
     * @var int $pm_time_sent
     */
    private $pm_time_sent;





    /**
     * @param $pm_user_id_from
     *
     * @return PmEntity
     */
    public function setPm_user_id_from($pm_user_id_from)
    {
        $this->pm_user_id_from = self::makeInt($pm_user_id_from);
        return $this;
    }

    /**
     * @param $pm_user_id_to
     *
     * @return PmEntity
     */
    public function setPm_user_id_to($pm_user_id_to)
    {
        $this->pm_user_id_to = self::makeInt($pm_user_id_to);
        return $this;
    }

    /**
     * @param $pm_subject
     *
     * @return PmEntity
     */
    public function setPm_subject($pm_subject)
    {
        $this->pm_subject = $pm_subject;
        return $this;
    }

    /**
     * @param $pm_text
     *
     * @return PmEntity
     */
    public function setPm_text($pm_text)
    {
        $this->pm_text = $pm_text;
        return $this;
    }

    /**
     * @param $pm_status
     *
     * @return PmEntity
     */
    public function setPm_status($pm_status)
    {
        $this->pm_status = $pm_status;
        return $this;
    }

    /**
     * @param $pm_time_sent
     *
     * @return PmEntity
     */
    public function setPm_time_sent($pm_time_sent)
    {
        $this->pm_time_sent = self::makeInt($pm_time_sent);
        return $this;
    }

}
