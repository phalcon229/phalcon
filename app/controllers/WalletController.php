<?php
//交易记录
class WalletController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->logic = new WalletRecordLogic();
        $this->walletLogic = new WalletLogic();

    }

    public function showAction()
    {
        $congif = $this->di['config'];
        $recordType = $congif['type'];

        $money = $this->walletLogic->money($this->uId);
        $info = $this->logic->getWalletRecordInfoByUserId($this->uId);
        $totalInfo = $this->logic->getTotalInfo($this->uId,0);//第一次进去加载所有，参数0

        $num = $totalInfo[0]['num'];
        $subtotal = count($info);

        $numMoney = sprintf("%0.1f",$totalInfo[0]['uwr_money']);
        if($numMoney == '')
        {
            $numMoney = sprintf("%0.1f",$numMoney);
        }

        $subtotalMoney = 0;
        for($i = 0;$i < $subtotal ; $i++)
        {
            $subtotalMoney += $info[$i]['uwr_money'];
        }

        $subtotalMoney = sprintf("%.1f", $subtotalMoney);

        $this->view->settemplateafter('user');
        $this->view->setVar('title', '交易记录');
        $this->view->setVar('info', $info);
        $this->view->setVar('num', $num);
        $this->view->setVar('recordType', $recordType);
        $this->view->setVar('subtotal', $subtotal);
        $this->view->setVar('numMoney', $numMoney);
        $this->view->setVar('subtotalMoney', $subtotalMoney);
        $this->di['view']->setVar('hideBack', true);
    }

    //根据类型加载交易记录
    public function freshAction()
    {
        $type = trim($this->request->getPost('type'));
        $num = 1;//默认初始加载10条
        $info = $this->logic->getInfoByType($this->uId,$type,$num);
        $totalInfo = $this->logic->getTotalInfo($this->uId,$type);

        $num = $totalInfo[0]['num'];
        $subtotal = count($info);
        $numMoney = sprintf("%0.1f",$totalInfo[0]['uwr_money']);

        $subtotalMoney = 0;
        for($i = 0;$i < $subtotal ; $i++)
        {
            $subtotalMoney += $info[$i]['uwr_money'];
        }

        $subtotalMoney = sprintf("%.1f", $subtotalMoney);
        if(empty($info))
        {
            return $this->di['helper']->resRet('无数据', 500);
        }
        $data = ['info' => $info, 'num' => $num, 'subtotal' => $subtotal, 'numMoney' => $numMoney, 'subtotalMoney' => $subtotalMoney];
        return $this->di['helper']->resRet($data, 200);
    }

    public function addMoreAction()
    {
        $this->uId = $this->uId;

        $number = trim($this->request->getPost('num'));
        $type = trim($this->request->getPost('type'));
        $subNum = trim($this->request->getPost('subN'));
        $subMoney = trim($this->request->getPost('subM'));
        $info = $this->logic->getInfoByType($this->uId,$type,$number);
        $totalInfo = $this->logic->getTotalInfo($this->uId,$type);

        $num = $totalInfo[0]['num'];
        $subtotal = count($info);
        $numMoney = sprintf("%0.1f",$totalInfo[0]['uwr_money']);
        if(empty($numMoney))
        {
            $numMoney = sprintf('%0.1f',0);
        }

        $subtotalMoney = 0;
        for($i = 0;$i < $subtotal ; $i++)
        {
            $subtotalMoney += $info[$i]['uwr_money'];
        }
        $subtotal = $subNum + $subtotal;

        $subtotalMoney = sprintf("%.1f", $subtotalMoney+$subMoney);

        $data = ['info' => $info, 'num' => $num, 'subtotal' => $subtotal, 'numMoney' => $numMoney, 'subtotalMoney' => $subtotalMoney];
        return $this->di['helper']->resRet($data, 200);
    }

    public function ajaxDetailAction()
    {
        $uwrId = trim($this->request->get('uid'));

        $detail=$this->logic->getDetail($this->uId,$uwrId);

        if(!empty($detail))
        {
            $memo = json_decode($detail[0]['uwr_memo'], true);
            $detail[0]['uwr_memo'] = is_array($memo) && !empty($memo['bo_sn']) ? $memo['bo_sn'] : $detail[0]['uwr_memo'];
            $data = array('detail' => $detail, 'status' => 1);
                $json = json_encode($data, true);
                echo $json;
                exit;
        }
        else
        {
            $msg="无数据";
            $data = array('msg' => $msg, 'status' => 0);
                $json = json_encode($data, true);
                echo $json;
                exit;
        }
    }

}
