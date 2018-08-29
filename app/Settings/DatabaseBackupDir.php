<?php

namespace App\Settings;

/**
 * Description of DatabaseBackupDir
 *
 * @author rendix2
 */
class DatabaseBackupDir
{
    /**
     * @var string $databaseBackupDir
     */
    private $databaseBackupDir;

    /**
     * DatabaseBackupDir constructor.
     *
     * @param string $databaseBackupDir
     */
    public function __construct($databaseBackupDir)
    {
        $this->databaseBackupDir = $databaseBackupDir;
    }

    /**
     * @return string
     */
    public function getDatabaseBackupDir()
    {
        return $this->databaseBackupDir;
    }
}
