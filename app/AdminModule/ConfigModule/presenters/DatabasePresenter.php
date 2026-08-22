<?php

namespace App\AdminModule\ConfigModule\Presenters;

use App\Settings\DatabaseBackupDir;
use Ifsnop\Mysqldump\Mysqldump;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Presenter;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;

class DatabasePresenter extends Presenter
{
    /**
     *
     * @var Mysqldump
     * @inject
     */
    public Mysqldump $exporter;
    
    /**
     * @var DatabaseBackupDir $databaseDir
     * @inject
     */
    public DatabaseBackupDir $databaseDir;

    public function __construct()
    {
        parent::__construct();
    }


    public function renderDumps(): void
    {
        $sqls = Finder::findFiles('*.sql')->in($this->databaseDir->get());
        
        if (!count($sqls)) {
            $this->flashMessage('No files to download.', 'warning');
        }
        
        $this->template->sqls = $sqls;
    }

    public function actionDeleteDump(string $name): void
    {
        FileSystem::delete($this->databaseDir->get() . DIRECTORY_SEPARATOR . $name);
        $this->redirect(':Admin:Config:Database:dumps');
    }

    /**
     * @param string $name
     */
    public function actionDownloadDump(string $name)
    {
        $this->sendResponse(new FileResponse($this->databaseDir->get() . DIRECTORY_SEPARATOR . $name));
    }

    /**
     *
     */
    public function actionExportDatabase()
    {
        $time = time();
        $path = $this->databaseDir->get() . DIRECTORY_SEPARATOR . 'dump-'.$time.'.sql';
                
        $this->exporter->start($path);

        $this->sendResponse(new FileResponse($path));
    }
}
