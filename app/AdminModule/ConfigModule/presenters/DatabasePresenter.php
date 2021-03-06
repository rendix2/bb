<?php

namespace App\AdminModule\ConfigModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Models\UsersManager;
use App\Settings\DatabaseBackupDir;
use Ifsnop\Mysqldump\Mysqldump;
use Nette\Application\Responses\FileResponse;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;

/**
 * Description of ConfigPresenter¨
 *
 * @author rendix2
 * @method UsersManager getManager()
 */
class DatabasePresenter extends AdminPresenter
{
    /**
     *
     * @var Mysqldump
     * @inject
     */
    public $exporter;
    
    /**
     * @var DatabaseBackupDir $databaseDir
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
        return null;
    }

    /**
     *
     * @return null
     */
    protected function createComponentGridFilter()
    {
        return $this->gf;
    }

    /**
     *
     */
    public function renderDumps()
    {
        $sqls = Finder::findFiles('*.sql')->in($this->databaseDir->get());
        
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
        FileSystem::delete($this->databaseDir->get() . DIRECTORY_SEPARATOR . $name);
        $this->redirect(':Admin:Config:Database:dumps');
    }

    /**
     * @param string $name
     */
    public function actionDownloadDump($name)
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
