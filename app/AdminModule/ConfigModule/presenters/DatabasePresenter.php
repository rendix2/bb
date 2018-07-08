<?php

namespace App\AdminModule\ConfigModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Models\UsersManager;
use Nette\Application\Responses\FileResponse;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;

/**
 * Description of ConfigPresenter¨
 *
 * @author rendi
 * @method UsersManager getManager()
 */
class DatabasePresenter extends AdminPresenter
{
    /**
     *
     * @var \Ifsnop\Mysqldump\Mysqldump
     * @inject
     */
    public $exporter;
    
    /**
     * @var \App\Config\DatabaseBackupDir $databaseDir
     * @inject
     */
    public $databaseDir;

    /**
     * DatabasePresenter constructor.
     *
     * @param UsersManager $manager
     */
    public function __construct(UsersManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return mixed|void
     */
    protected function createComponentEditForm()
    {
    }

    /**
     *
     */
    public function renderDumps()
    {
        $sqls = Finder::findFiles('*.sql')->in($this->databaseDir->getDatabaseBackupDir());
        
        if (!count($sqls)) {
            $this->flashMessage('No files to download.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->sqls = $sqls;
    }

    /**
     * @param string $name
     */
    public function actionDeleteDump($name)
    {
        FileSystem::delete($this->databaseDir . DIRECTORY_SEPARATOR . $name);
        $this->redirect(':Admin:Config:Database:dumps');
    }

    /**
     * @param $name
     */
    public function actionDownloadDump($name)
    {
        $this->sendResponse(new FileResponse($this->databaseDir->getDatabaseBackupDir() . DIRECTORY_SEPARATOR . $name));
    }

    /**
     *
     */
    public function actionExportDatabase()
    {              
        $time = time();   
        $path = $this->databaseDir->getDatabaseBackupDir() . DIRECTORY_SEPARATOR . 'dump-'.$time.'.sql';
                
        $this->exporter->start($path);

        $this->sendResponse(new FileResponse($path));
    }
}
