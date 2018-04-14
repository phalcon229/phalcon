<?php
use Phalcon\Security;
use Phalcon\Security\Random;

class BankController extends ControllerBase
{
    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->logic = new  BankLogic();
        $this->walletLogic = new WalletLogic();
    }

    public function addAction()
    {
        $this->assets
            ->addJs('js/distpicker.data.js')
            ->addJs('js/distpicker.js');

        $config = $this->di['config'];
        $wPwd = $this->walletLogic->pass($this->uId);

        $this->view->setVar('title', '银行卡绑定');
        $this->view->setVar('bank', $config['bank']);
        $this->view->setVar('wPwd', $wPwd);
    }
    public function doAddAction()
    {
        $config = $this->di['config'];
        $pwd = trim($this->request->getPost('pwd'));
        $wPwd = $this->walletLogic->pass($this->uId);
        if(empty($wPwd))
            return $this->di['helper']->resRet("未设置资金密码", 501);

        $res = $this->checkPass($this->uId, $pwd,$wPwd);
        if($res == -1)
            return $this->di['helper']->resRet("输错密码已达本日最大次数", 500);
        if($res == -2)
            return $this->di['helper']->resRet("资金密码错误", 499);

        $name = trim($this->request->getPost('name'));
        if ($err = $this->di['check']->name($name))
            return $this->di['helper']->resRet($err, 500);

        $phone = trim($this->request->getPost('phone'));
        if ($err = $this->di['check']->phone($phone))
            return $this->di['helper']->resRet($err, 500);

        $bankId = trim($this->request->getPost('bankId'));

        $number = trim($this->request->getPost('number'));
        if ($err = $this->di['check']->account($number))
            return $this->di['helper']->resRet($err, 500);

        $province = trim($this->request->getPost('province'));
        $city = trim($this->request->getPost('city'));

        $uname = trim($this->request->getPost('uname'));

        if(!empty($uname))
        {
            if ($err = $this->di['check']->bank($uname))
                return $this->di['helper']->resRet($err, 500);
        }
        $uname = $config['bank'][$bankId].$uname;

        $bankAdd = $this->logic->addBankInfo($this->uId,$bankId, $uname,$province,$city,$number,$name,$phone);
        if(!$bankAdd){
            return $this->di['helper']->resRet('添加失败', 500);
        }else{
            return $this->di['helper']->resRet('添加成功', 200);
        }
    }
    public function delAction()
    {
        $ubcId = $this->request->getPost('ubcId');
        $del = $this->logic->delBank($ubcId);
        if(! $del)
        {
            return $this->di['helper']->resRet('删除失败', 500);
        }
        return $this->di['helper']->resRet('添加成功', 200);
    }
    public function updateAction()
    {
        $config = $this->di['config'];

        $ubcId = $this -> dispatcher -> getParam(1);
        $info = $this->logic->getUpdateBankInfo($ubcId);

        $this->view->setVar('info',$info);
        $this->view->setVar('bank', $config['bank']);
        $this->view->setVar('title', '银行卡更新');
    }
    public function doUpdateAction()
    {
        $this->view->setVar('title', '银行卡修改');

        $ubcId = trim($this->request->getPost('ubcId'));

        $name = trim($this->request->getPost('name'));

        $bankId = trim($this->request->getPost('bankId'));

        $account = trim($this->request->getPost('account'));

        $province = trim($this->request->getPost('province'));

        $city = trim($this->request->getPost('city'));

        $bankUpdate = $this->logic->updateBank($this->uId, $bankId, $name,$province,$city,$account,$ubcId);
        if(!$bankUpdate)
        {
            return "更新失败";
        }
        else
        {
            return "成功";
        }
    }

    public function  showAction()
    {
        $info = $this->logic->getBankInfoByUserId($this->uId);
        for($i = 0;$i < count($info) ; $i++)
        {
            if($info[$i]['ubc_status'] == 1)
            {
                $info[$i]['ubc_status'] = "正常";
            }
            else
            {
                $info[$i]['ubc_status'] = "异常";
            }
        }

        $this->view->setTemplateAfter('user');
        $this->view->setVar('info',$info);
        $this->view->setVar('title','银行卡管理');
        $this->view->setVar('hideBack', true);
    }

}
