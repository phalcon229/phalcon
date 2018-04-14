<?php

class HomeController extends ControllerBase
{

    public function initialize()
    {

    }

    public function indexAction()
    {
        print_r($this->di['session']->get('uInfo'));exit;
        echo $this->di['cookie']->get('auth')->__toString();exit;
        $this->view->setVar('title', '首页');
    }
}
