<?php

class SystemModel extends ModelBase
{

    protected $table = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function getBets($bId)
    {
        $where = '';
        $arr = [];
        if (!empty($bId)) {
            $where .= ' AND bet_id = ? ';
            array_push($arr, $bId);
        }

        $this->query('SELECT * FROM le_bets_base_conf WHERE bet_isenable <> 5 '.$where .'LIMIT 1',[$bId]);
        return $this->getRow();
    }

    public function getBetsType()
    {
        $this->query('SELECT bet_id, bet_name FROM le_bets_base_conf WHERE bet_isenable <> 5');
        return $this->getAll();
    }

    public function getBetRules($bId)
    {
        $this->query('SELECT br_id, br_type, br_base_type, br_bonus FROM le_bets_rules WHERE br_status = 1 AND bet_id = ?', [$bId]);
        return $this->getAll();
    }

    public function getBetRulesBybrid($brId)
    {
        $this->query('SELECT br_id, bet_id, br_type, br_base_type FROM le_bets_rules WHERE br_status = 1 AND br_id IN ( ' . $brId .')');
        return $this->getAll();
    }

    public function editBetRule($brId, $bonus)
    {
         $this->table = 'le_bets_rules';
         $this->update(['br_bonus' => $bonus], ['condition' => 'br_status = 1 AND br_id = ?' , 'values' => [$brId]]);

        return $this->affectRow();
    }

    public function editBetRules($brIds, $bonus)
    {
        $this->table = 'le_bets_rules';
        $this->query('update le_bets_rules set br_bonus = ? WHERE br_status = 1 AND br_id IN ( ' . $brIds .')', [$bonus]);
        return $this->affectRow();
    }

    public function doConfSet($key,$date)
    {
        $this->table = 'le_bets_base_conf';
        $this->update(
            $date, ['condition' => 'bet_id = ?' , 'values' => [$key]]
        );
        return $this->affectRow();
    }

     public function getAcNum()
    {
        $this->query('SELECT count(pa_id) as total FROM le_pay_activity WHERE pa_status = 1');
        return $this->getOne();
    }
    public function getAcLimit($start, $limit)
    {

        $this->query('SELECT pa_id, pa_title, pa_content, pa_img, pa_starttime, pa_endtime FROM le_pay_activity WHERE pa_status = 1 ORDER BY pa_id DESC LIMIT '. $start . ',' . $limit);
        return $this->getAll();
    }

    public function activeAdd($date)
    {
        $this->table = 'le_pay_activity';
        $this->insert(
           $date
        );
        return $this->lastInsertId();
    }

    public function activeEdit($aId, $atitle, $acontent, $img)
    {
        $this->table = 'le_pay_activity';
        $date['pa_title'] = $atitle;
        $date['pa_content'] = $acontent;
        if (!empty($img))
            $date['pa_img'] = $img;

         $res=$this->update(
            $date,['condition' => 'pa_id = ? AND pa_status = 1', 'values'=>[$aId]]);
        return $res;
    }

    public function getActive($aId)
    {
        $this->query('SELECT pa_id, pa_type, pa_title, pa_content, pa_img, pa_starttime, pa_endtime,pa_history_money, pa_money, pa_gift_money FROM le_pay_activity WHERE pa_id = ? AND pa_status = 1 LIMIT 1', [$aId]);
        return $this->getRow();
    }

    public function delActive($aId)
    {
        $this->table = 'le_pay_activity';
        $res=$this->update([
                'pa_status'=> 3,
            ],['condition' => 'pa_id = ? AND pa_status = 1', 'values'=>[$aId]]);
        return $res;
    }

    public function getNtNum()
    {
        $this->query('SELECT count(n_id) as total FROM le_notice WHERE n_is_display = 1');
        return $this->getOne();
    }
    public function getNtLimit($start, $limit)
    {
        $this->query('SELECT n_id, n_title, n_content, n_created_time FROM le_notice WHERE n_is_display = 1 ORDER BY n_id DESC LIMIT '. $start . ',' . $limit);
        return $this->getAll();
    }

    public function delNotice($ntId)
    {
        $this->table = 'le_notice';
        $res = $this->update([
                'n_is_display'=> 3,
            ],['condition' => 'n_id = ? AND n_is_display = 1', 'values' => [$ntId]]);
        return $res;
    }

    public function addNotice($condition)
    {
        $this->table = 'le_notice';
        $this->insert(
           $condition
        );
        return $this->lastInsertId();
    }

    public function getNotice($ntId)
    {
        $this->query('SELECT n_id, n_title, n_content FROM le_notice WHERE n_id = ? AND n_is_display = 1 LIMIT 1', [$ntId]);
        return $this->getRow();
    }

    public function getBase()
    {
        $this->query('SELECT * FROM le_sys_conf' );
        return $this->getAll();
    }

    public function editBase($info)
    {
        $ids = implode(',', array_keys($info));
        $sql = "UPDATE le_sys_conf SET sc_value = CASE sc_id ";
        foreach ($info as $id => $ordinal)
            $sql .= sprintf("WHEN %d THEN '%s' ", $id, $ordinal);

        $sql .= "END WHERE sc_id IN ($ids)";
        return $this->query($sql);
    }

    public function noticeEdit($ntId, $ntitle, $ncontent)
    {
        $this->table = 'le_notice';
        $res = $this->update([
                'n_title'=> $ntitle,
                'n_content' => $ncontent
            ],['condition' => 'n_id = ? AND n_is_display = 1', 'values'=>[$ntId]]);
        return $res;
    }

    public function getPayNum()
    {
        $this->query('SELECT count( ubc_id) as total FROM le_user_bank_cards where ubc_status <> 5');
        return $this->getOne();
    }

    public function getPayLimit($start, $limit)
    {
        $this->query('SELECT a.ubc_id, a.ubc_name, a.ubc_uname, a.ubc_status, b.u_name FROM le_user_bank_cards as a LEFT JOIN le_users as b ON a.u_id = b.u_id where ubc_status <> 5 ORDER BY a.ubc_id DESC LIMIT '. $start . ',' . $limit);
        return $this->getAll();
    }

    public function DoChangestatus($id, $status)
    {
        $type = 1;
        $status == 1 AND $type=3;
        $this->table = 'le_pay_channel_conf';
        $res = $this->update([
                'pcc_status'=> $type,
            ],['condition' => 'pcc_id = ? AND pcc_status = ?', 'values' => [$id, $status]]);
        return $res;
    }

    public function payReg($id, $min, $max, $json)
    {
        $this->table = 'le_pay_channel_conf';
        $res = $this->update([
                'pcc_min'=> $min,
                'pcc_max' => $max,
                'pcc_memo' => $json,
            ],['condition' => 'pcc_id = ?', 'values' => [$id]]);
        return $res;
    }


    public function getBannerNum()
    {
        $this->query('SELECT count( ib_id) as total FROM le_index_banner where ib_status = 1');
        return $this->getOne();
    }

    public function getBannerLimit($start, $limit)
    {
        $this->query('SELECT ib_id, ib_desc, ib_img, ib_url, ib_status, ib_created_time,ib_sort FROM le_index_banner WHERE ib_status = 1 ORDER BY ib_sort DESC, ib_id DESC LIMIT '. $start . ',' . $limit);
        return $this->getAll();
    }

    public function getUrl()
    {
        $this->query('SELECT sc_value FROM le_sys_conf WHERE sc_id = 10');
        return $this->getOne();
    }

    public function addBanner($date)
    {
        $this->table = 'le_index_banner';
        $this->insert(
           $date
        );
        return $this->lastInsertId();
    }

    public function delBanner($ibId)
    {
        $this->table = 'le_index_banner';
        $res = $this->update([
                'ib_status'=> 3,
            ],['condition' => 'ib_id = ? AND ib_status = 1', 'values' => [$ibId]]);
        return $res;
    }

    public function getBanner($ibId)
    {
        $this->query('SELECT ib_id, ib_desc, ib_img, ib_url,ib_sort FROM le_index_banner WHERE ib_id = ? AND ib_status = 1 LIMIT 1', [$ibId]);
        return $this->getRow();
    }

    public function editBanner($ibId, $date)
    {
        $this->table = 'le_index_banner';
        $res = $this->update(
                $date
            ,['condition' => 'ib_id = ? AND ib_status = 1', 'values' => [$ibId]]);
        return $res;
    }

    public function getValue($scId)
    {
        $this->query('SELECT sc_name, sc_value FROM le_sys_conf WHERE sc_id = ? LIMIT 1', [$scId]);
        return $this->getRow();
    }

    public function getPayConf($type)
    {
        $this->query('SELECT * FROM le_pay_channel_conf WHERE pcc_type = ?', [$type]);
        return $this->getAll();
    }

}
