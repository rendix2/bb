<?php

namespace App\AdminModule\ConfigModule\Presenters;

/**
 * Description of ConfigPresenter¨
 *
 * @author rendi
 * @method \App\Models\UsersManager getManager()
 */
class DatabasePresenter extends \App\AdminModule\Presenters\Base\AdminPresenter
{    
    private $temmpDir;
    
    public function __construct(\App\Models\UsersManager $manager)
    {                
        parent::__construct($manager);
    }
    
    public function injectTempDir(\App\Controls\TempDir $tempDir)
    {
        $this->temmpDir = $tempDir;
    }

    protected function createComponentEditForm()
    {
        
    }
    
    public function renderDumps()
    {
        $sqls = \Nette\Utils\Finder::findFiles('*.sql')->in($this->temmpDir->tempDir.'/dumps');
        
        if (!count($sqls)) {
            $this->flashMessage('No files to download.', self::FLASH_MESSAGE_WARNING);
        }
        
        $this->template->sqls = $sqls;
    }
    
    public function actionDeleteDump($name)
    {
        \Nette\Utils\FileSystem::delete($this->temmpDir->tempDir.'/dumps/'.$name);
        $this->redirect(':Admin:Config:Database:dumps');
    }
    
    public function actionDownloadDump($name)
    {
        $this->sendResponse(new \Nette\Application\Responses\FileResponse($this->temmpDir->tempDir.'/dumps/'.$name));    
    }

    public function actionExportDatabase()
    {
        $config = $this->getManager()->getDibi()->getConfig();
        
        if ($config['host'] === null){
            $config['host'] = 'localhost';
        }
               
        $time = time();
        
        $exporter = new \Ifsnop\Mysqldump\Mysqldump('mysql:host='.$config['host'].';dbname='.$config['database'], $config['username'], $config['password']);
        $exporter->start($this->temmpDir->tempDir.'/dumps/dump-'.$time.'.sql');
        
        $exporter = null;
        
        $this->sendResponse(new \Nette\Application\Responses\FileResponse($this->temmpDir->tempDir.'/dumps/dump-'.$time.'.sql'));       
    }
}
