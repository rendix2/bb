<?php

namespace App\AdminModule\ConfigModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\TempDir;
use App\Models\UsersManager;
use Ifsnop\Mysqldump\Mysqldump;
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
     * @var TempDir $tempDir7
     * @inject 
     */
    public $tempDir;
    
    public function __construct(UsersManager $manager)
    {
        parent::__construct($manager);
    }

    protected function createComponentEditForm()
    {
    }
    
    public function renderDumps()
    {
        $sqls = Finder::findFiles('*.sql')->in($this->tempDir->tempDir.'/dumps');
        
        if (!count($sqls)) {
            $this->flashMessage('No files to download.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->sqls = $sqls;
    }
    
    public function actionDeleteDump($name)
    {
        FileSystem::delete($this->tempDir->tempDir.'/dumps/'.$name);
        $this->redirect(':Admin:Config:Database:dumps');
    }
    
    public function actionDownloadDump($name)
    {
        $this->sendResponse(new FileResponse($this->tempDir->tempDir.'/dumps/'.$name));
    }

    public function actionExportDatabase()
    {
        $config = $this->getManager()->getDibi()->getConfig();
        
        if ($config['host'] === null) {
            $config['host'] = 'localhost';
        }
               
        $time = time();
        
        $exporter = new Mysqldump(
            'mysql:host='.$config['host'].';dbname='.$config['database'],
            $config['username'],
            $config['password']
        );
        $exporter->start($this->tempDir->tempDir.'/dumps/dump-'.$time.'.sql');
        $exporter = null;
        
        $this->sendResponse(new FileResponse($this->tempDir->tempDir.'/dumps/dump-'.$time.'.sql'));
    }
}
