<?php
namespace Plugins;

use Phalcon\Events\Event,
    Phalcon\Mvc\User\Plugin,
    Phalcon\Mvc\Dispatcher\Exception as DispatcherException,
    Phalcon\Mvc\Dispatcher;

class NotFoundPlugin extends Plugin
{
    public function beforeException(Event $event, Dispatcher $dispatcher, $exception)
    {

        error_log($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());

        if($exception instanceof DispatcherException)
        {
            switch($exception->getCode())
            {
                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $this->di['ErrorService']->show404Action();
            }
        }
        else
            $this->di['ErrorService']->show500Action($exception->getMessage(), $exception->getCode());

        return false;
    }
}
