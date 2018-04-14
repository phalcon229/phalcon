<?php
use Components\Utils\Helper;

class ErrorsController extends ControllerBase
{

    public function show404Action()
    {
        return $this->di['helper']->resRet('Not Found', 404);
    }

    public function show500Action($msg = NULL, $code = NULL)
    {
        // return $this->di['helper']->resRet(empty($msg) || $code !== 2300 ? 'Server Error' : $msg, 500);
        return $this->di['helper']->resRet($msg, 500);
    }
}
