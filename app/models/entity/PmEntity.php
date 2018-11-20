<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of PmEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
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
     *
     * @var int $pm_time_read
     */    
    private $pm_time_read;
    
    public function getPm_id()
    {
        return $this->pm_id;
    }

    public function getPm_user_id_from()
    {
        return $this->pm_user_id_from;
    }

    public function getPm_user_id_to()
    {
        return $this->pm_user_id_to;
    }

    public function getPm_subject()
    {
        return $this->pm_subject;
    }

    public function getPm_text()
    {
        return $this->pm_text;
    }

    public function getPm_status()
    {
        return $this->pm_status;
    }

    public function getPm_time_sent()
    {
        return $this->pm_time_sent;
    }

    public function getPm_time_read()
    {
        return $this->pm_time_read;
    }

    public function setPm_id($pm_id)
    {
        $this->pm_id = $pm_id;
        return $this;
    }

    public function setPm_user_id_from($pm_user_id_from)
    {
        $this->pm_user_id_from = self::makeInt($pm_user_id_from);
        return $this;
    }

    public function setPm_user_id_to($pm_user_id_to)
    {
        $this->pm_user_id_to = self::makeInt($pm_user_id_to);
        return $this;
    }

    public function setPm_subject($pm_subject)
    {
        $this->pm_subject = $pm_subject;
        return $this;
    }

    public function setPm_text($pm_text)
    {
        $this->pm_text = $pm_text;
        return $this;
    }

    public function setPm_status($pm_status)
    {
        $this->pm_status = $pm_status;
        return $this;
    }

    public function setPm_time_sent($pm_time_sent)
    {
        $this->pm_time_sent = self::makeInt($pm_time_sent);
        return $this;
    }

    public function setPm_time_read($pm_time_read)
    {
        $this->pm_time_read = self::makeInt($pm_time_read);
        return $this;
    }    
    
    /**
     * 
     * @param Row $values
     * 
     * @return PmEntity
     */
    public function setFromRow(Row $values)
    {
        $pm = new PmEntity();
        
        if (isset($values->pm_id)) {
            $pm->setPm_id($values->pm_id);
        }
        
        if (isset($values->pm_user_id_from)) {
            $pm->setPm_user_id_from($values->pm_user_id_from);
        }
        
        if (isset($values->pm_user_id_to)) {
            $pm->setPm_user_id_to($values->pm_user_id_to);
        }
        
        if (isset($values->pm_subject)) {
            $pm->setPm_subject($values->pm_subject);
        }
        
        if (isset($values->pm_tex)) {
            $pm->setPm_text_from($values->pm_tex);
        }
        
        if (isset($values->pm_status)) {
            $pm->setPm_status($values->pm_status);
        }
        
        if (isset($values->pm_time_read)) {
            $pm->setPm_time_read($values->pm_time_read);           
        }
        
        if (isset($values->pm_time_sent)) {
            $pm->setPm_time_sent($values->pm_time_sent);
        }
                   
        return $pm;
    }
    
    /**
     * 
     * @param ArrayHash $values
     * 
     * @return PmEntity
     */
    public function setFromArrayHash(ArrayHash $values)
    {
        $pm = new PmEntity();
        
        if (isset($values->pm_id)) {
            $pm->setPm_id($values->pm_id);
        }
        
        if (isset($values->pm_user_id_from)) {
            $pm->setPm_user_id_from($values->pm_user_id_from);
        }
        
        if (isset($values->pm_user_id_to)) {
            $pm->setPm_user_id_to($values->pm_user_id_to);
        }
        
        if (isset($values->pm_subject)) {
            $pm->setPm_subject($values->pm_subject);
        }
        
        if (isset($values->pm_tex)) {
            $pm->setPm_text_from($values->pm_tex);
        }
        
        if (isset($values->pm_status)) {
            $pm->setPm_status($values->pm_status);
        }
        
        if (isset($values->pm_time_read)) {
            $pm->setPm_time_read($values->pm_time_read);           
        }
        
        if (isset($values->pm_time_sent)) {
            $pm->setPm_time_sent($values->pm_time_sent);
        }
                   
        return $pm;      
    }

        /**
     * 
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->pm_id)) {
            $res['pm_id'] = $this->pm_id;
        }
        
        if (isset($this->pm_user_id_from)) {
            $res['pm_user_id_from'] = $this->pm_user_id_from;
        }

        if (isset($this->pm_user_id_to)) {
            $res['pm_user_id_to'] = $this->pm_user_id_to;
        }

        if (isset($this->pm_subject)) {
            $res['pm_subject'] = $this->pm_subject;
        }

        if (isset($this->pm_text)) {
            $res['pm_text'] = $this->pm_text;
        }

        if (isset($this->pm_status)) {
            $res['pm_status'] = $this->pm_status;
        }   
        
        if (isset($this->pm_time_sent)) {
            $res['pm_time_sent'] = $this->pm_time_sent;
        }      

        if (isset($this->pm_status)) {
            $res['pm_time_read'] = $this->pm_time_read;
        }              
        
        return $res;
    }
}
