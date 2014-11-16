<?php

namespace Panda\Core\Component\Debug;

use Logger;
use Panda\Core\Application;
use Whoops\Handler\Handler;

class ProdHandler extends Handler
{
    private $logger = null;
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->logger = Logger::getLogger(__CLASS__);
    }

    /**
     * @return int|null A handler may return nothing, or a Handler::HANDLE_* constant
     */
    public function handle()
    {
        $exception = $this->getException();
        $this->logger->error('An error occured', $exception);
        $this->app->exitFailure(500);
    }
}