<?php

namespace App\Services;

/**
 * Description of BBLogger
 *
 * @author rendi
 */
class BBLogger implements \Tracy\ILogger
{
    const ACTION = 'action';
    
    const PRESENTER = 'presenter';
    
    const MESSAGE = 'message';
    
    const PARAMS = 'params';
    
    /**
     *
     * @var \Nette\Security\User $user
     * @inject
     */
    public $user;
    
    /**
     *
     * @var \App\Models\LogsManager $logsManager
     * @inject
     */
    public $logsManager;
    
    public static function create()
    {
        return new BBLogger($this->user, $this->logsManager);
    }

    public function __construct(\Nette\Security\User $user, \App\Models\LogsManager $logsManager)
    {
        $this->user        = $user;
        $this->logsManager = $logsManager;
    }

    public function log($value, $priority = self::INFO)
    {
        $log_data = [
            'log_user_id' => $this->user->getId(),
            'log_time'    => time(),
            'log_priority' => $priority,
            'log_presenter' => $value[self::PRESENTER],
            'log_action' => $value[self::ACTION],
            'log_message' => $value[self::MESSAGE],
            'log_params' => $value[self::PARAMS],
        ];
        
        return $this->logsManager->add(\Nette\Utils\ArrayHash::from($log_data));   
    }

}
