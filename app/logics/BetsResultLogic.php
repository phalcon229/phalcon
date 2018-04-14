<?php
use Phalcon\Security;
use Components\Bets;

class BetsResultLogic extends LogicBase
{

    public function __construct()
    {
        $this->model = new BetsResultModel();
        $this->configModel = new BetsConfigModel();
        $this->betUtils = new Bets\Utils;
    }
    /**
     * 根据采种id和期号查开奖结果
     * @param type $betId
     * @param type $npre
     * @return type
     */
    public function getResultInfo($betId, $npre)
    {
        $info = $this->model->getResultInfo( $betId, $npre );
        return $info;
    }

    /**
     * 根据时间段获取开奖信息
     * @param type $begintime
     * @param type $endtime
     * @return type
     */
    public function getDayResult($begintime, $endtime)
    {
        return $this->model->getDayResult($begintime, $endtime);
    }
/**
 * 按betId获取开奖信息
 * @param type $betId
 * @return type
 */
    public function getResultByType($betId, $fields = '*')
    {
        return $this->model->getResultByType($betId, $fields);
    }
/**
 * 获取开奖结果
 * @param type $betId
 * @return type
 */
    public function getResult($betId, $num)
    {
        return $this->model->getResult($betId, $num);
    }
/**
 * 获取采种开奖总条数
 * @param type $betId
 * @return type
 */
    public function getAllResult($betId)
    {
        return $this->model->getAllResult($betId);
    }

    /**
     * 根据彩种id和期数获取开奖结果
     * @param type $betId
     * @param type $issue
     * @return type
     */
    public function getResultNum($betId, $issue)
    {
        return $this->model->getResultNum($betId, $issue);
    }

    /**
     * 获取最近100条的彩种期数
     * @param  [type]  $betId [description]
     * @param  integer $nums  [description]
     * @return [type]         [description]
     */
    public function getIssueByBetId($betId, $startTime)
    {
        $endTime = $_SERVER['REQUEST_TIME'];
        return $this->model->getIssueByBetId($betId, $startTime, $endTime);
    }

    /**
     * 将彩种期数转换为一维数组
     * @param  [type]  $betId [description]
     * @param  integer $date  [description]
     * @return [type]         [description]
     */
    public function getIssuesLimit($betId, $startTime)
    {
        $res = $this->getIssueByBetId($betId, $startTime);
        $issues = [];
        foreach ($res as $value) {
            $issues[] = $value['bres_periods'];
        }

        return $issues;
    }

    /**
     * 获取最新一期开奖信息
     * @param  [type]  $betId  [description]
     * @param  boolean $status [description]
     * @return [type]          [description]
     */
    public function getLastResult($betId, $status = false)
    {
        return $this->model->getLastResult($betId, $status);
    }

    public function lists($betId, $nums = 10)
    {
        return $this->model->lists($betId, $nums);
    }

    public function tolists($betId, $bresid, $nums = 10)
    {
        return $this->model->tolists($betId, $bresid, $nums);
    }

    public function getBresId($betId, $issue)
    {
        return $this->model->getBresId($betId, $issue);
    }

    /**
     * 设置开奖记录
     *
     *
     */
    public function addBetResultByStatus($betId, $status, $periods, $res)
    {
        return $this->model->insert([
            'bet_id' => $betId,
            'bres_periods' => $periods,
            'bres_result' => $res,
            'bres_created_time' => $_SERVER['REQUEST_TIME'],
            'bres_plat_isopen' => $status,
            'bres_plat_open_time' => $_SERVER['REQUEST_TIME'],
            'bres_memo' => json_encode($this->betUtils->analyzeFromResByType($betId, explode(',', $res))),
        ]);
    }

    public function setBetResultStatus($bresId, $betsResultStatus, $openTime = 0)
    {
        return $this->model->update(['bres_plat_isopen' => $betsResultStatus, 'bres_plat_open_time' => $openTime], ['condition' => 'bres_id = ?', 'values' => [$bresId]]);
    }

    /**
     * 把时间戳转化为星期一，二....
     * @param type $time
     * @param type $i
     * @return type
     */
    public function getTimeWeek($time)
    {
        $weekarray = array("一", "二", "三", "四", "五", "六", "日");
        $oneD = 24 * 60 * 60+6*60;
        return "星期" . $weekarray[date("w", $time - $oneD)];
    }

    public function getNextPerids($betId, $nums = 1, $perid = 0)
    {
        return $this->model->getNextPerids($betId, $nums, $perid);
    }
    //追号只能追今天内的期数
    public function getNextTrackPerids($betId, $nums = 1, $perid = 0)
    {
        return $this->model->getNextTrackPerids($betId, $nums, $perid);
    }

    public function getNextAllPerids($betId, $nums = 1, $perid = 0, $intval)
    {
        return $this->model->getNextAllPerids($betId, $nums, $perid,$intval);
    }

    public function listinfo($betId, $page, $nums = 10)
    {
        return $this->model->listinfo($betId, $page, $nums);
    }

    public function getAbnormal()
    {
        $new = $this->model->getAbnormal();

        $bet =[];
        foreach ($new as $value) {
            $res = $this->configModel->getInfoByIdAllStatus($value['bet_id']);
            $value['bet_name'] = $res['bet_name'];
            array_push($bet, $value);
        }
        return $bet ;
    }

    public function getAbnormalDetailByBetid($betid)
    {
        $new = $this->model->getAbnormalDetailByBetid($betid);

        $bet =[];
        foreach ($new as $value) {
            $res = $this->configModel->getInfoByIdAllStatus($value['bet_id']);
            $value['bet_name'] = $res['bet_name'];
            array_push($bet, $value);
        }
        return $bet ;
    }

    public function unOpenExpect($betId,$issue)
    {
        return $this->model->unOpenExpect($betId,$issue);
    }

    public function setUpdateResultById($bresId)
    {
        return $this->model->setUpdateResultById($bresId);
    }

    public function getOpeningExpect($betId)
    {
        return $this->model->getOpeningExpect($betId);
    }

    /**
     * 获取各彩种最近一期开奖时间
     * @param  [type] $ids    [description]
     * @param  int $interval  截止时间
     * @param  string $fields [description]
     * @return [type]         [description]
     */
    public function getBetsNext($ids, $interval, $fields = '*')
    {
        return $this->model->getBetsNext($ids, $interval, $fields);
    }
}
