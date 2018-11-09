<?php

namespace App\Presenters;

use Nette;
use Nette\Application\Helpers;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses;
use Tracy\ILogger;

/**
 * Class ErrorPresenter
 *
 * @package App\Presenters
 */
class ErrorPresenter implements Nette\Application\IPresenter
{
    use Nette\SmartObject;
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

        if ($e instanceof Nette\Application\BadRequestException) {
            $this->logger->log("HTTP code {$e->getCode()}: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", 'access');
            list($module, , $sep) = Helpers::splitName($request->getPresenterName());
            $errorPresenter = $module . $sep . 'Error4xx';

            return new Responses\ForwardResponse($request->setPresenterName($errorPresenter));
        }

        $this->logger->log(
            $e,
            ILogger::EXCEPTION
        );

        return new Responses\CallbackResponse(
            function () {
            $sep = DIRECTORY_SEPARATOR;
            
                require __DIR__ . $sep . 'templates' . $sep . 'Error' . $sep . '500.phtml';
            }
        );
    }
}
