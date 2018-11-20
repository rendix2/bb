<?php

namespace App\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\IPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\Responses\ForwardResponse;
use Nette\Http\IResponse;
use Nette\Application\Request;
use Nette\SmartObject;
use Tracy\Helpers;
use Tracy\ILogger;

/**
 * Class ErrorPresenter
 *
 * @package App\Presenters
 */
class ErrorPresenter implements IPresenter
{
    use SmartObject;
    
    /**
     * @var ILogger $logger
     */
    private $logger;

    /**
     * ErrorPresenter constructor.
     *
     * @param ILogger $logger
     */
    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->logger = null;
    }

    /**
     * @param Request $request
     *
     * @return IResponse
     */
    public function run(Request $request)
    {
        $e = $request->getParameter('exception');

        if ($e instanceof BadRequestException) {
            $this->logger->log("HTTP code {$e->getCode()}: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", 'access');
            list($module, , $sep) = Helpers::splitName($request->getPresenterName());
            $errorPresenter = $module . $sep . 'Error4xx';

            return new ForwardResponse($request->setPresenterName($errorPresenter));
        }

        $this->logger->log(
            $e,
            ILogger::EXCEPTION
        );

        return new CallbackResponse(
            function () {
            $sep = DIRECTORY_SEPARATOR;
            
                require __DIR__ . $sep . 'templates' . $sep . 'Error' . $sep . '500.phtml';
            }
        );
    }
}
