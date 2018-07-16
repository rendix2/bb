<?php

namespace App\Settings;

/**
 * Description of DatabaseBackupDir
 *
 * @author rendi
 */
class DatabaseBackupDir
{
    /**
     * @var string $databaseBackupDir
     */
    private $databaseBackupDir;
    
    public function __construct($databaseBackupDir)
    {
        $this->databaseBackupDir = $databaseBackupDir;
    }
    
    public function getDatabaseBackupDir()
    {
        return $this->databaseBackupDir;
    }
}
