<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of ReportEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class ReportEntity extends Entity
{
    /**
     *
     * @var int $report_id
     */
    private $report_id;
    
    /**
     *
     * @var int $report_user_id
     */
    private $report_user_id;
    
    /**
     *
     * @var int $report_forum_id
     */
    private $report_forum_id;
    
    /**
     *
     * @var int $report_topic_id
     */
    private $report_topic_id;
    
    /**
     *
     * @var int $report_post_id
     */
    private $report_post_id;

    /**
     *
     * @var int $report_reported_user_id
     */
    private $report_reported_user_id;
    
    /**
     *
     * @var int $report_pm_id
     */
    private $report_pm_id;
    
    /**
     *
     * @var string $report_text
     */
    private $report_text;
    
    /**
     *
     * @var int $report_time
     */
    private $report_time;
    
    /**
     *
     * @var string $report_status
     */
    private $report_status;
    
    public function getReport_id()
    {
        return $this->report_id;
    }

    public function getReport_user_id()
    {
        return $this->report_user_id;
    }

    public function getReport_forum_id()
    {
        return $this->report_forum_id;
    }

    public function getReport_topic_id()
    {
        return $this->report_topic_id;
    }

    public function getReport_post_id()
    {
        return $this->report_post_id;
    }

    public function getReport_reported_user_id()
    {
        return $this->report_reported_user_id;
    }

    public function getReport_pm_id()
    {
        return $this->report_pm_id;
    }

    public function getReport_text()
    {
        return $this->report_text;
    }

    public function getReport_time()
    {
        return $this->report_time;
    }

    public function getReport_status()
    {
        return $this->report_status;
    }

    public function setReport_id($report_id)
    {
        $this->report_id = self::makeInt($report_id);
        return $this;
    }

    public function setReport_user_id($report_user_id)
    {
        $this->report_user_id = self::makeInt($report_user_id);
        return $this;
    }

    public function setReport_forum_id($report_forum_id)
    {
        $this->report_forum_id = self::makeInt($report_forum_id);
        return $this;
    }

    public function setReport_topic_id($report_topic_id)
    {
        $this->report_topic_id = self::makeInt($report_topic_id);
        return $this;
    }

    public function setReport_post_id($report_post_id)
    {
        $this->report_post_id = self::makeInt($report_post_id);
        return $this;
    }

    public function setReport_reported_user_id($report_reported_user_id)
    {
        $this->report_reported_user_id = self::makeInt($report_reported_user_id);
        return $this;
    }

    public function setReport_pm_id($report_pm_id)
    {
        $this->report_pm_id = self::makeInt($report_pm_id);
        return $this;
    }

    public function setReport_text($report_text)
    {
        $this->report_text = $report_text;
        return $this;
    }

    public function setReport_time($report_time)
    {
        $this->report_time = self::makeInt($report_time);
        return $this;
    }

    public function setReport_status($report_status)
    {
        $this->report_status = $report_status;
        return $this;
    }

    /**
     *
     * @param Row $values
     *
     * @return ReportEntity
     */
    public static function setFromRow(Row $values)
    {
        $reportEntity = new ReportEntity();

        if (isset($values->report_id)) {
            $reportEntity->setReport_id($values->report_id);
        }

        if (isset($values->report_user_id)) {
            $reportEntity->setReport_user_id($values->report_user_id);
        }

        if (isset($values->report_forum_id)) {
            $reportEntity->setReport_forum_id($values->report_forum_id);
        }

        if (isset($values->report_topic_id)) {
            $reportEntity->setReport_topic_id($values->report_topic_id);
        }

        if (isset($values->report_post_id)) {
            $reportEntity->setReport_post_id($values->report_post_id);
        }

        if (isset($values->report_reported_user_id)) {
            $reportEntity->setReport_reported_user_id($values->report_reported_user_id);
        }

        if (isset($values->report_pm_id)) {
            $reportEntity->setReport_pm_id($values->report_pm_id);
        }

        if (isset($values->report_text)) {
            $reportEntity->setReport_text($values->report_text);
        }

        if (isset($values->report_time)) {
            $reportEntity->setReport_time($values->report_time);
        }

        if (isset($values->report_status)) {
            $reportEntity->setReport_status($values->report_status);
        }

        return $reportEntity;
    }

    /**
     *
     * @param ArrayHash $values
     *
     * @return ReportEntity
     */
    public function setFromArrayHash(ArrayHash $values)
    {
        $reportEntity = new ReportEntity();

        if (isset($values->report_id)) {
            $reportEntity->setReport_id($values->report_id);
        }

        if (isset($values->report_user_id)) {
            $reportEntity->setReport_user_id($values->report_user_id);
        }

        if (isset($values->report_forum_id)) {
            $reportEntity->setReport_forum_id($values->report_forum_id);
        }

        if (isset($values->report_topic_id)) {
            $reportEntity->setReport_topic_id($values->report_topic_id);
        }

        if (isset($values->report_post_id)) {
            $reportEntity->setReport_post_id($values->report_post_id);
        }

        if (isset($values->report_reported_user_id)) {
            $reportEntity->setReport_reported_user_id($values->report_reported_user_id);
        }

        if (isset($values->report_pm_id)) {
            $reportEntity->setReport_pm_id($values->report_pm_id);
        }

        if (isset($values->report_text)) {
            $reportEntity->setReport_text($values->report_text);
        }

        if (isset($values->report_time)) {
            $reportEntity->setReport_time($values->report_time);
        }

        if (isset($values->report_status)) {
            $reportEntity->setReport_status($values->report_status);
        }

        return $reportEntity;
    }

    /**
     *
     * @return array
     */
    public function getArray()
    {
        $res = [];

        if (isset($this->report_id)) {
            $res['report_id'] = $this->report_id;
        }

        if (isset($this->report_user_id)) {
            $res['report_user_id'] = $this->report_user_id;
        }

        if (isset($this->report_forum_id)) {
            $res['report_forum_id'] = $this->report_forum_id;
        }

        if (isset($this->report_topic_id)) {
            $res['report_topic_id'] = $this->report_topic_id;
        }

        if (isset($this->report_post_id)) {
            $res['report_post_id'] = $this->report_post_id;
        }

        if (isset($this->report_reported_user_id)) {
            $res['report_reported_user_id'] = $this->report_reported_user_id;
        }

        if (isset($this->report_pm_id)) {
            $res['report_pm_id'] = $this->report_pm_id;
        }

        if (isset($this->report_text)) {
            $res['report_text'] = $this->report_text;
        }

        if (isset($this->report_time)) {
            $res['report_time'] = $this->report_time;
        }

        if (isset($this->report_status)) {
            $res['report_status'] = $this->report_status;
        }

        return $res;
    }
}
