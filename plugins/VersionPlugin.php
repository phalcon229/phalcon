<?php
namespace Plugins;

use Phalcon\Mvc\User\Plugin,
    Phalcon\Events\Event,
    Phalcon\Mvc\Dispatcher;

class VersionPlugin extends Plugin
{
    protected $verInfo;

    public function __construct($verInfo)
    {
        $this->verInfo = $verInfo;
    }

    public function beforeDispatchLoop(Event $event, Dispatcher $dispatcher)
    {

        $apiVer = $this->di['request']->getHeader('Apiversion');
        if(!empty($apiVer))
        {
            $verKey = $this->verInfo['verKey'];

            if(in_array($apiVer, $verKey))
            {
                $ctrlName = ucfirst($dispatcher->getControllerName());
                $ctrlName ? '' : $ctrlName = 'Index';

                $newCtrlName = $ctrlName . $this->verInfo['verName'][$apiVer] . 'Controller';
                if(!class_exists($newCtrlName))
                {
                    $key = array_search($apiVer, $verKey);
                    while($key > 0)
                    {
                        $newCtrlName = $ctrlName . $this->verInfo['verName'][$verKey[$key - 1]] . 'Controller';
                        if(class_exists($newCtrlName)) break;
                        $key--;
                    }
                }

                $dispatcher->setControllerName(lcfirst(str_replace('Controller', '', $newCtrlName)));
            }
        }
    }
}
