<?php
namespace Plugins;

use Phalcon\Events\Event,
    Phalcon\Mvc\User\Plugin,
    Phalcon\Mvc\Dispatcher,
    Phalcon\Security\Random;

class CallShieldPlugin extends Plugin
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $shieldUUID = $this->di['session']->get('shield-uuid');
        $callName = $dispatcher->getControllerName() . '-' . $dispatcher->getActionName();
        $callSet = $this->di['config']['callShield'];

        if($shieldUUID)
        {
            $callDataName = $shieldUUID . ':' . $callName;
            $curNums = intval($this->di['redis']->get($callDataName));
            if($curNums)
            {
                if($curNums  >= $callSet['maxNums'])
                {
                    $this->di['helper']->resRet($callSet['msg'], 403);
                    return false;
                }
                else
                    $this->di['redis']->incr($callDataName);
            }
            else
                $this->di['redis']->pSetEx($callDataName, $callSet['timeRange'], 1);
        }
        else
        {
//            $random = new Random();
//            $shieldUUID = $random->base64Safe(12);
//            $this->di['session']->set('shield-uuid', $shieldUUID);
//            $this->di['redis']->pSetEx($shieldUUID . ':' . $callName, $callSet['timeRange'], 1);
        }
    }
}
