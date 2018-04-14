<?php

class IndexController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];
        $this->img = $this->di['config']['game']['gameImg'];//彩种图标

        $this->noticeLogic = new NoticeLogic;
        $this->bannerLogic = new BannerLogic;
        $this->betsConfigLogic = new BetsConfigLogic;
        $this->sysLogic = new SystemLogic;
        $this->usersLogic = new UsersLogic;
    }

    public function indexAction()
    {
        // if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') == false )
        // {
            $redis = $this->di['redis'];
            $havekey = $this->uId.'have';
            $addkey = $this->uId.'add';
            $delkey = $this->uId.'del';

            $haveGkey = $this->uId.'haveG';
            $addGkey = $this->uId.'addG';
            $delGkey = $this->uId.'delG';
            //需求改变，删除缓存测试专用
            // $redis->del($havekey,$addkey,$delkey,$haveGkey,$addGkey,$delGkey);
            $haveId = $redis->smembers($havekey);
            $haveGId = $redis->smembers($haveGkey);
            $all = $this->betsConfigLogic->getOpenBetsId(1);
            $allG = $this->betsConfigLogic->getOpenBetsId(3);
            //所有采种id
            $allId = [];
            $allGId = [];
            foreach ($all as $key=>$value)
            {
                $allId[$key] = intval($value['bet_id']);
            }

            foreach ($allG as $key=>$value)
            {
                $allGId[$key] = intval($value['bet_id']);
            }
            $resId = $allId;
            $resGId = $allGId;
            $ids = implode(',', $resId);
            $idsG = implode(',', $resGId);
            $res = $del = $resG = $delG = $addG = $add = [];
            //第一次
            if(empty($haveId))
            {
                if ($ids)
                {
                    //have
                    $res = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);
                    $del = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);
                }

                foreach ($resId as $key=>$value)
                {
                    $redis->sadd($havekey,intval($value));
                    $redis->sadd($delkey,intval($value));
                }
                //add
                $addId = array_diff($allId, $resId);//数组
                $ids = implode(',', $addId);
                foreach ($addId as $key=>$value)
                {
                    $redis->sadd($addkey,intval($value));
                }
                $add = [];
            }
            else
            {
                $ids = implode(',', $haveId);
                $res = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);//有的
                $addId = $redis->smembers($addkey);
                if(empty($addId))
                {
                    $add = [];
                }
                else
                {
                    $ids = implode(',', $addId);
                    $add = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);
                }

                $delId = $redis->smembers($delkey);
                if(empty($delId[0])){
                    $del = [];
                }
                else
                {
                    $ids = implode(',', $delId);
                    $del = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);
                }
            }
            //官方
            if(empty($haveGId))
            {
                if ($idsG)
                {
                    //have
                    $resG = $this->betsConfigLogic->getBetsInfoByIds($idsG, 3);
                    $delG = $this->betsConfigLogic->getBetsInfoByIds($idsG, 3);
                }

                foreach ($resGId as $key=>$value)
                {
                    $redis->sadd($haveGkey,intval($value));
                    $redis->sadd($delGkey,intval($value));
                }
                //add
                $addGId = array_diff($allGId, $resGId);//数组
                $idsG = implode(',', $addGId);
                foreach ($addGId as $key=>$value)
                {
                    $redis->sadd($addGkey,intval($value));
                }
                $addG = [];
            }
            else
            {
                $idsG = implode(',', $haveGId);
                $resG = $this->betsConfigLogic->getBetsInfoByIds($idsG, 3);//有的

                $addGId = $redis->smembers($addGkey);
                if(empty($addGId))
                {
                    $addG = [];
                }
                else
                {
                    $idsG = implode(',', $addGId);
                    $addG = $this->betsConfigLogic->getBetsInfoByIds($idsG, 3);
                }

                $delGId = $redis->smembers($delGkey);
                if(empty($delGId[0])){
                    $delG = [];
                }
                else
                {
                    $idsG = implode(',', $delGId);
                    $delG = $this->betsConfigLogic->getBetsInfoByIds($idsG, 3);
                }
            }
        // }
        // else
        // {
        //     //所有采种id
        //     $allId = [5,3,6,1];
        //     $allGId = [16,17];
        //     $ids = implode(',', $allId);
        //     $idsG = implode(',', $allGId);
        //     $res = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);
        //     $resG = $this->betsConfigLogic->getBetsInfoByIds($idsG, 3);
        //     $del = [];
        //     $add = [];
        //     $delG = [];
        //     $addG = [];
        // }
        //首页banner活动
        $bannerInfo = $this->bannerLogic->getBannerInfo();
        //首页公告
        $noticeInfo = $this->noticeLogic->getNotice();
        $url = $this->sysLogic->getUrl();
        $this->view->setVar('url', $url);
        $this->view->setVar('bannerInfo', $bannerInfo);
        $this->view->setVar('noticeInfo', $noticeInfo);
        $this->view->setVar('img', $this->img);
        $this->view->setVar('del', $del);
        $this->view->setVar('add', $add);
        $this->view->setVar('res', $res);
        $this->view->setVar('delG', $delG);
        $this->view->setVar('addG', $addG);
        $this->view->setVar('resG', $resG);
        $this->view->setVar('uId', $this->uId);
        $this->view->setVar('title', '彩票大厅');
        $this->view->setVar('uName', $this->uName);
        $this->view->setVar('uId', $this->uId);
        $this->view->setVar('type', $this->usersLogic->getType($this->uId)['u_type']);
        $this->view->setVar('ctrl', $this->dispatcher->getControllerName());
        $this->view->setVar('action', $this->dispatcher->getActionName());

    }

    public function addBetAction()
    {
        $betId = intval($this->request->getPost('betId'));
        $redis = $this->di['redis'];
        $havekey = $this->uId.'have';
        $addkey = $this->uId.'add';
        $delkey = $this->uId.'del';
        //have
        $redis->sadd($havekey,$betId);
        $haveId = $redis->smembers($havekey);
        $ids = implode(',', $haveId);
        $res = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);
        //add
        $redis->srem($addkey,$betId);
        $addId = $redis->smembers($addkey);
        if(!empty($addId))
        {
            $ids = implode(',', $addId);
            $add = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);
        }
        else
        {
            $add = [];
        }
        //del
        $redis->sadd($delkey,$betId);
        $delId = $redis->smembers($delkey);
        $ids = implode(',', $delId);
        $del = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);

        $data = ['res' => $res, 'add' => $add, 'img' => $this->img, 'del'=>$del];
        return $this->di['helper']->resRet($data, 200);

    }

    public function delBetAction()
    {
        $betId = intval($this->request->getPost('betId'));
        $redis = $this->di['redis'];
        $havekey = $this->uId.'have';
        $addkey = $this->uId.'add';
        $delkey = $this->uId.'del';
        //have
        $haveId = $redis->smembers($havekey);
        $len = count($haveId);
        if($len >3)
        {
            $redis->srem($havekey,$betId);
            $redis->sadd($addkey,$betId);
            $redis->srem($delkey,$betId);
        }
        $haveId = $redis->smembers($havekey);
        $ids = implode(',', $haveId);
        $res = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);
        //add
        $addId = $redis->smembers($addkey);
        $ids = implode(',', $addId);
        $add = $this->betsConfigLogic->getBetsInfoByIds($ids, 1);
        //del

        $delId = $redis->smembers($delkey);
        if(!empty($delId))
        {
            $ids = implode(',', $delId);
            $del = $this->betsConfigLogic->getBetsInfoByIds($ids, 1 );
        }
        else
        {
            $del = [];
        }

        $data = ['res' => $res, 'add' => $add, 'img' => $this->img, 'del' => $del];
        return $this->di['helper']->resRet($data, 200);
    }

    public function addGBetAction()
    {
        $betId = intval($this->request->getPost('betId'));
        $redis = $this->di['redis'];
        $havekey = $this->uId.'haveG';
        $addkey = $this->uId.'addG';
        $delkey = $this->uId.'delG';
        //have
        $redis->sadd($havekey,$betId);
        $haveId = $redis->smembers($havekey);
        $ids = implode(',', $haveId);
        $res = $this->betsConfigLogic->getBetsInfoByIds($ids, 3);
        //add
        $redis->srem($addkey,$betId);
        $addId = $redis->smembers($addkey);
        if(!empty($addId))
        {
            $ids = implode(',', $addId);
            $add = $this->betsConfigLogic->getBetsInfoByIds($ids, 3);
        }
        else
        {
            $add = [];
        }
        //del
        $redis->sadd($delkey,$betId);
        $delId = $redis->smembers($delkey);
        $ids = implode(',', $delId);
        $del = $this->betsConfigLogic->getBetsInfoByIds($ids, 3);

        $data = ['resG' => $res, 'addG' => $add, 'img' => $this->img, 'delG'=>$del];
        return $this->di['helper']->resRet($data, 200);

    }

    public function delGBetAction()
    {
        $betId = intval($this->request->getPost('betId'));
        $redis = $this->di['redis'];
        $havekey = $this->uId.'haveG';
        $addkey = $this->uId.'addG';
        $delkey = $this->uId.'delG';
        //have
        $haveId = $redis->smembers($havekey);
        $len = count($haveId);
        if($len >3)
        {
            $redis->srem($havekey,$betId);
            $redis->sadd($addkey,$betId);
            $redis->srem($delkey,$betId);
        }
        $haveId = $redis->smembers($havekey);
        $ids = implode(',', $haveId);
        $res = $this->betsConfigLogic->getBetsInfoByIds($ids, 3);
        //add

        $addId = $redis->smembers($addkey);
        $ids = implode(',', $addId);
        $add = $this->betsConfigLogic->getBetsInfoByIds($ids, 3);
        //del

        $delId = $redis->smembers($delkey);
        if(!empty($delId))
        {
            $ids = implode(',', $delId);
            $del = $this->betsConfigLogic->getBetsInfoByIds($ids, 3);
        }
        else
        {
            $del = [];
        }

        $data = ['resG' => $res, 'addG' => $add, 'img' => $this->img, 'delG' => $del];
        return $this->di['helper']->resRet($data, 200);
    }
}
