<?php
use Phalcon\Mvc\View;

class ResultController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->logic = new BetsResultLogic();
        $this->betsConfigLogic = new BetsConfigLogic;
    }

    public function indexAction()
    {
        $lotteryType = $this->betsConfigLogic->getBet();
        //获取所有采种信息的最新一条信息
        $res = array();
        for($i = 0; $i < count($lotteryType); $i++){
            $res[$i] = $this->logic->getResultByType($lotteryType[$i]['bet_id']);
        }

        //处理中奖号码
        for($i = 0; $i < count($lotteryType); $i++)
        {
            if($res[$i] === false)
            {
                unset($res[$i]);
            }
            else
            {
                $res[$i]['bres_result'] = explode(',', $res[$i]['bres_result']);
                $betNum = $res[$i]['bres_result'];

            }
        }
        $ballColor = ['mbg-yellow', 'mbg-dblue', 'mbg-dgray', 'mbg-orange', 'mbg-blue', 'mbg-purple', 'mbg-gray', 'mbg-red', 'mbg-dred', 'mbg-green'];

        $betImg = [1=>'/img/game-ssc.png',
                   2=>'/img/game-pcegg.png', 
                   3=>'/img/ship.png', 
                   4=>'/img/game-happy10.png', 
                   5=>'/img/game-pk10.png', 
                   6=>'/img/cakeno.png', 
                   7=>'/img/kuai3.png', 
                   8=>'/img/kuai3.png', 
                   9=>'/img/kuai3.png', 
                   10=>'/img/11xuan5-guangdong.png', 
                   11=>'/img/11xuan5-zhejiang.png', 
                   12=>'/img/11xuan5-sandong.png', 
                   13=>'/img/11xuan5-liaoning.png',
                   14=>'/img/kuai3.png', 
                   15=>'/img/kuai3.png', 
                   16=>'/img/kuai3.png',  
                   17=>'/img/11xuan5-guangdong.png',
                   18=>'/img/11xuan5-zhejiang.png', 
                   19=>'/img/11xuan5-sandong.png', 
                   20=>'/img/11xuan5-liaoning.png'
                   ];
        $this->view->setVar('title', '开奖结果');
        $this->view->setVar('res', $res);
        $this->view->setVar('ballColor', $ballColor);
        $this->view->setVar('betImg', $betImg);
    }

    public function detailAction()
    {
        $lotteryType = $this->betsConfigLogic->getBet();
        if (!$betId = $this->request->getQuery('lotId'))
            return $this->showmsg('参数错误', '/result/index');

        switch ($betId)
        {
            case '1':
                $title = '重庆时时彩';
                break;

            case '2':
                $title = '北京28';
                break;
            
            case '3':
                $title = '幸运飞艇';
                break;
            
            case '4':
                $title = '广东快乐十分';
                break;
            
            case '5':
                $title = '北京赛车PK10';
                break;
            
            case '6':
                $title = '加拿大28';
                break;
            case '7':
                $title = '福建快3信';
                break;

            case '8':
                $title = '北京快3信';
                break;
            
            case '9':
                $title = '浙江快3信';
                break;
            
            case '10':
                $title = '广东11选5信';
                break;
            
            case '11':
                $title = '浙江11选5信';
                break;
            
            case '12':
                $title = '山东11选5信';
                break;

            case '13':
                $title = '辽宁11选5信';
                break;

            case '14':
                $title = '福建快3官';
                break;

            case '15':
                $title = '北京快3官';
                break;
            
            case '16':
                $title = '江苏快3官';
                break;
            
            case '17':
                $title = '广东11选5官';
                break;
            
            case '18':
                $title = '浙江11选5官';
                break;
            
            case '19':
                $title = '山东11选5官';
                break;

            case '20':
                $title = '辽宁11选5官';
                break;

            case '21':
                $title = '腾讯分分彩';
                break;
        }
        $this->view->setVars([
            'title' => $title,
            'betId' => $betId,
            'bet' => $lotteryType,
            'nowBet' => $betId,
        ]);
    }

    public function addMoreAction()
    {
        $nums = $this->request->get('num') ?: 10;
        $betId=$this->request->get('bet_id');
        $page=$this->request->get('page');

        // 获取开奖列表
        $list = $this->logic->listinfo($betId, $page, $nums);

        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        )->enable();

        $ballColor = ['mbg-yellow', 'mbg-dblue', 'mbg-dgray', 'mbg-orange', 'mbg-blue', 'mbg-purple', 'mbg-gray', 'mbg-red', 'mbg-dred', 'mbg-green'];
        
        switch ($betId)
        {
            case '1':
                $this->view->pick('result/detail/cqssc');
                break;

            case '2':
                $this->view->pick('result/detail/pcdd');
                break;
            
            case '3':
                $this->view->pick('result/detail/xyft');
                break;
            
            case '4':
                $this->view->pick('result/detail/gdkl10');
                break;
            
            case '5':
                $this->view->pick('result/detail/bjpk10');
                break;
            
            case '6':
                $this->view->pick('result/detail/cakeno');
                break;

            case '7':
                $this->view->pick('result/detail/fjks');
                break;

            case '8':
                $this->view->pick('result/detail/bjks');
                break;
            
            case '9':
                $this->view->pick('result/detail/jsks');
                break;
            
            case '10':
                $this->view->pick('result/detail/gd11x5');
                break;
            
            case '11':
                $this->view->pick('result/detail/zj11x5');
                break;
            
            case '12':
                $this->view->pick('result/detail/sd11x5');
                break;

            case '13':
                $this->view->pick('result/detail/ln11x5');
                break;

            case '14':
                $this->view->pick('result/detail/ks');
                break;

            case '15':
                $this->view->pick('result/detail/ks');
                break;
            
            case '16':
                $this->view->pick('result/detail/ks');
                break;
            
            case '17':
                $this->view->pick('result/detail/11x5');
                break;
            
            case '18':
                $this->view->pick('result/detail/11x5');
                break;
            
            case '19':
                $this->view->pick('result/detail/11x5');
                break;

            case '20':
                $this->view->pick('result/detail/11x5');
                break;

            case '21':
                $this->view->pick('result/detail/txffc');
                break;

            default:
                echo '';
                break;
        }

        $this->view->setVar('ballColor', $ballColor);
        $this->view->setVar('list', $list);
        $this->view->setVar('ruleCfg', $this->di['config']['game']);
    }

}
