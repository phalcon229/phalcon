<?php
use \Components\Bets;
class AbnormalController extends ControllerBase
{
    public function initialize()
    {
        $this->logic = new BetOrdersLogic();
        $this->lotterylogic = new BetsResultLogic();
        $this->configlogic = new BetsConfigLogic();
    }

    public function indexAction()
    {
        $res = $this->lotterylogic->getAbnormal();

        $this->di['view']->setVars([
            'list' => $res,
        ]);
    }
    
    public function haveAction()
    {
        $res = $this->lotterylogic->getAbnormal();
        if(!empty($res))
        {
            return $this->di['helper']->resRet('', 200);
        }
        else
        {
            return $this->di['helper']->resRet('', 500);  
        }
            
    }

    public function detailAction()
    {
        //数据betid,issue
        if (!$betid = intval($this->request->getQuery('betid')))
            return $this->showMsg('彩种数据不正确', true, '/abnormal');

        if (!$issue = $this->request->getQuery('issue'))
            return $this->showMsg('期数数据不正确', true, '/abnormal');

        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? $this->request->getQuery('limit') : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $currentPage = 1;
        else
            $currentPage = !empty($this->request->getQuery('page')) ? $this->request->getQuery('page') : 1;

        $total = $this->logic->getAbnormalTotal($betid, $issue);
        $res = $this->logic->getAbnormalDetail($currentPage, $limit, $betid, $issue );

        $this->di['view']->setVars([
            'list' => $res,
            'info' => $this->lotterylogic->getAbnormalDetailByBetid($betid),
            'result' => $this->lotterylogic->getResultNum($betid, $issue)['bres_result'],
            'game' => $this->di['config']['game'],
            'total' => $total,
            'limit' => ceil($total/$limit),
            'perpage' => $perpage,
            'betnum' => $this->configlogic->getInfoByIdAllStatus($betid)['bet_ball_num']
        ]);
    }

    //type=1设置异常  3设置未开奖
    public function setabnorAction()
    {
        $this->view->disable();
        //数据betid,issue
        if (!$betid = intval($this->request->getPost('betid')))
            return $this->di['helper']->resRet('彩种数据不正确', 500);

        if (!$issue = $this->request->getPost('issue'))
            return $this->di['helper']->resRet('期数数据不正确', 500);

        if (!$type = $this->request->getPost('type'))
            return $this->di['helper']->resRet('类型不对', 500);

        $betsResultModel = new BetsResultModel;
        //设置开奖状态
       
        if($type == '1')
        {
            $expect = $betsResultModel->getAbnormalExpectInfo($betid,$issue);
            $res = $betsResultModel->setAbnorById($expect['bres_id']);//设置为异常状态
            if(!$res)
            {
                return $this->di['helper']->resRet('设置失败', 500);
            }
            $this->logContent = '修改彩种id:'. $betid .',期数'.$issue.',设置为异常';
        }
        else
        {
            $expect = $betsResultModel->getAbnormalExpectInfo($betid,$issue);
            $res = $betsResultModel->setNoOpen($expect['bres_id']);
            if(!$res)
            {
                return $this->di['helper']->resRet('设置失败', 500);
            }
            $this->logContent = '修改彩种id:'. $betid .',期数'.$issue.',设置为官方未开奖';
        }

        return $this->di['helper']->resRet();
    }

    //整期开奖
    public function doeditAction()
    {
        $this->view->disable();
        //数据betid,issue
        if (!$betid = intval($this->request->getPost('betid')))
            return $this->di['helper']->resRet('彩种数据不正确', 500);

        if (!$issue = $this->request->getPost('issue'))
            return $this->di['helper']->resRet('期数数据不正确', 500);

        //判断开奖数组是否正确
        $lottery = $this->request->getPost('lottery');
        if (count($lottery) <= 0 || (count($lottery) != $this->configlogic->getInfoByIdAllStatus($betid)['bet_ball_num']))
            return $this->di['helper']->resRet('开奖结果不能为空或不符合开奖数目', 500);
        
        $expect = $this->lotterylogic->unOpenExpect($betid,$issue);

        $utilsObj = new Bets\Utils;
        $betsResultModel = new BetsResultModel;
        if($betid==2 || $betid == 6)
        {
            $res = $betsResultModel->setResultById($expect['bres_id'], implode(',', $lottery), json_encode($utilsObj->analyzeFromResByType($betid*111, $lottery)));//设置开奖结果
            if(!$res)
            {
                return $this->di['helper']->resRet('设置失败', 500);
            }
        }
        else
        {
            $res = $betsResultModel->setResultById($expect['bres_id'], implode(',', $lottery), json_encode($utilsObj->analyzeFromResByType($betid, $lottery)));//设置开奖结果
            if(!$res)
            {
                return $this->di['helper']->resRet('设置失败', 500);
            }
        }
        
        $affect = $betsResultModel->setOpenedById($expect['bres_id']);//设置为开奖中状态
        if(!$affect)
        {
            return $this->di['helper']->resRet('设置失败', 500);
        }

        // todo 写入开奖结果
        $this->di['redis']->rpush('open:queue', $expect);

        $this->logContent = '修改彩种id:'. $betid .',期数'.$issue.',整期开奖';
        return $this->di['helper']->resRet();
    }

    //单个开奖
    public function lotteryAction()
    {
        $this->view->disable();
        if (!$boid = intval($this->request->getPost('boid')))
            return $this->di['helper']->resRet('订单数据不正确', 500);

        //判断异常订单是否存在,并且未开奖退款
        if(!$order = $this->logic->getOrderByBoid($boid))
            return $this->di['helper']->resRet('异常订单不存在', 500);
        
        //todo 对此订单开奖
        $orderdetail = $this->logic->getOrderDetail($boid);
        $result = $this->lotterylogic->getResultInfo($orderdetail['bet_id'], $orderdetail['bo_issue']);

        if(!$result[0]['bres_result'])
            return $this->di['helper']->resRet('请先设置开奖结果', 500);
        $resultmemo = json_decode($result[0]['bres_memo'])->detail;

        $res = $this->logic->openBets($orderdetail['bet_id'],$orderdetail['bo_issue'], $result[0]['bres_result'],$resultmemo,$orderdetail);
        $res = $this->logic->getAbnormalDetail(1, 25, $orderdetail['bet_id'], $orderdetail['bo_issue'] );
        if(empty($res))
        {
            $this->lotterylogic->setUpdateResultById($result[0]['bres_id']);
        }

        $this->logContent = '修改异常订单id:'. $boid .',开奖成功';
        return $this->di['helper']->resRet();

    }

    //单个退款
    public function backAction()
    {
        $this->view->disable();
        if (!$boid = intval($this->request->getPost('boid')))
            return $this->di['helper']->resRet('订单数据不正确', 500);

        //判断异常订单是否存在
        if(!$data = $this->logic->getOrderByBoid($boid))
            return $this->di['helper']->resRet('异常订单不存在', 500);

        //进行退款操作
        if(!$this->logic->backOne($data)){
            return $this->di['helper']->resRet('退款失败', 500);
        }

        $this->logContent = '修改异常订单id:'. $boid .',退款成功';
        return $this->di['helper']->resRet();
    }

    //整期退款
    public function allbackAction()
    {
        $this->view->disable();
        //数据betid,issue
        if (!$betid = intval($this->request->getPost('betid')))
            return $this->di['helper']->resRet('彩种数据不正确', 500);

        if (!$issue = intval($this->request->getPost('issue')))
            return $this->di['helper']->resRet('期数数据不正确', 500);

        //判断彩球期数异常是否存在
        if(!$this->logic->getAbnormalDetail(1, 10, $betid, $issue ))
            return $this->di['helper']->resRet('没有相应的订单信息', 500);

        //进行整期退款操作
        if(!$this->logic->allback($betid, $issue))
            return $this->di['helper']->resRet('整期退款失败', 500);

        $this->logContent = '修改彩种id:'. $betid .',期数'.$issue.',整期退款';
        return $this->di['helper']->resRet();
    }
}