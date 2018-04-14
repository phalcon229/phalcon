<?php
class BankLogic extends LogicBase
{

    private $bankModel;

    public function __construct()
    {
        $this->bankModel = new BankModel;
        $this->usersModel = new UsersModel;
    }
    /**
     * 添加银行卡信息
     * @param type $uId
     * @param type $bankId
     * @param type $uname
     * @param type $province
     * @param type $city
     * @param type $number
     * @param type $name
     * @return type
     */
    public function addBankInfo($uId,$bankId ,$uname,$province,$city,$number,$name,$phone)
    {
        return $this->bankModel->addBank($uId,$bankId, $uname,$province,$city,$number,$name,$phone);
    }
    /**
     * 删除银行卡
     * @param type $ubcId
     * @return type
     */
    public function delBank($ubcId)
    {
        return $this->bankModel->delBank($ubcId);
    }

    /**
     * 更新银行卡
     * @param type $uId
     * @param type $bankId
     * @param type $name
     * @param type $province
     * @param type $city
     * @param type $account
     * @param type $ubcId
     * @return type
     */
    public function updateBank($uId,$bankId ,$name,$province,$city,$account,$ubcId)
    {
        return $this->bankModel->updateBank($uId,$bankId ,$name,$province,$city,$account,$ubcId);
    }
    /**
     * 通过uid获取银行卡信息
     * @param type $uId
     * @return string
     */
    public function getBankInfoByUserId($uId)
    {
        $config = $this->di['config'];
        $bank = $config['bank'];
        $info=$this->bankModel->getInfoByUserId($uId);
        for($i=0;$i<count($info);$i++)
        {
            $info[$i]['ubc_bank_name'] = $bank[$info[$i]['ubc_bank_id']];
        }
        return $info;
    }

    /**
     * 更改银行卡信息
     * @param type $ubcId
     * @return string
     */
    public function getUpdateBankInfo($ubcId)
    {
        $config = $this->di['config'];
        $bank = $config['bank'];

        $info=$this->bankModel->getUpdateInfo($ubcId);
        for($i=0;$i<count($info);$i++)
        {
            $info[$i]['ubc_bank_name'] = $bank[$info[$i]['ubc_bank_id']];
        }
        return $info;
   }

   /**
    * 获取用户名
    * @param type $uId
    * @return type
    */
    public function getUserName($uId)
    {
        return $this->usersModel->getUserName($uId);
    }

    /**
     * 通过uid获取银行卡信息
     * @param type $uId
     * @return type
     */
    public function getBank($uId)
    {
        return $this->bankModel->getBank($uId);
    }

    /**
     * 通过uid和银行卡id获取银行信息
     * @param type $uId
     * @param type $bankId
     * @return type
     */
    public function getBankInfo($uId,$bankId)
    {
        return $this->bankModel->getBankInfo($uId,$bankId);
    }

    public function getBankById($bankId)
    {
        return $this->bankModel->getBankById($bankId);
    }

    /**
     * API接口获取用户银行卡列表
     * @param  [type] $uId [description]
     * @return [type]      [description]
     */
    public function getLists($uId)
    {
        $fields = 'ubc_id, ubc_uname, ubc_bank_id, ubc_number, ubc_province, ubc_status';
        $lists = $this->bankModel->getInfoByUserId($uId, $fields);
        $bank = $this->di['config']['bank'];
        $res = [];
        foreach ($lists as $value) {
            $tmp['ubc_id'] = $value['ubc_id'];
            $tmp['ubc_province'] = $value['ubc_province'];
            $tmp['ubc_uname'] = substr($value['ubc_uname'],0,3) . '**';
            $tmp['ubc_bank_name'] = array_key_exists($value['ubc_bank_id'], $bank) ? $bank[$value['ubc_bank_id']] : '';
            $tmp['ubc_number'] =  substr($value['ubc_number'], 0, 3) . '**********' . substr($value['ubc_number'], -3);
            $tmp['ubc_status'] = $value['ubc_status'];
            $res[] = $tmp;
        }

        return $res;
    }
}
