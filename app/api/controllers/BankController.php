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

        $this->logic = new BankLogic();
        $this->walletLogic = new WalletLogic();
    }

    //银行列表,返回银行id及名称
    public function listAction()
    {
        $bank = $this->di['config']['bank'];
        $data = [];
        foreach ($bank as $key => $value) {
            $tmp['id'] = $key;
            $tmp['name'] = $value;
            $data[] = $tmp;
        }

        return $this->di['helper']->resRet($data);
    }

    //用户银行卡信息列表
    public function  showAction()
    {
        $lists = $this->logic->getLists($this->uId);

        return $this->di['helper']->resRet($lists);
    }

    /**
     * 添加银行卡记录
     */
    public function addAction()
    {
        $pwd = trim($this->request->getPost('pwd'));

        if(!$wPwd = $this->walletLogic->pass($this->uId))
            return $this->di['helper']->resRet("未设置资金密码", 501);

        if(!$this->security->checkHash($pwd, $wPwd))
            return $this->di['helper']->resRet("资金密码错误", 500);

        $name = trim($this->request->getPost('name'));
        if ($err = $this->di['check']->name($name))
            return $this->di['helper']->resRet($err, 500);

        $phone = trim($this->request->getPost('phone'));
        if ($err = $this->di['check']->phone($phone))
            return $this->di['helper']->resRet($err, 500);

        $bankId = trim($this->request->getPost('bankId'));

        if (!array_key_exists($bankId, $this->di['config']['bank']))
            return $this->di['helper']->resRet('银行参数错误', 500);

        $number = trim($this->request->getPost('number'));
        if ($err = $this->di['check']->account($number))
            return $this->di['helper']->resRet($err, 500);

        $province = trim($this->request->getPost('province'));
        $city = trim($this->request->getPost('city'));

        $uname = trim($this->request->getPost('uname'));//开户行
        if(!empty($uname))
        {
            if ($err = $this->di['check']->bank($uname))
                return $this->di['helper']->resRet($err, 500);
        }

        $bankAdd = $this->logic->addBankInfo($this->uId, $bankId, $uname, $province, $city, $number, $name, $phone);
        if(!$bankAdd)
            return $this->di['helper']->resRet('添加失败', 500);
        else
            return $this->di['helper']->resRet('添加成功', 200);
    }

    /**
     * 删除记录
     * @return [type] [description]
     */
    public function delAction()
    {
        if (!$ubcId = intval($this->request->getPost('ubc_id')))
            return $this->di['helper']->resRet('Invalid Data', 500);

        if(!$this->logic->delBank($ubcId))
            return $this->di['helper']->resRet('删除失败', 500);

        return $this->di['helper']->resRet('删除成功', 200);
    }
}
