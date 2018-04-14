<?php

class SystemController extends ControllerBase
{

    public function initialize()
    {
        $this->sysLogic = new SystemLogic;
        $name = $this->dispatcher->getActionName();
        $this->di['view']->setVars([
            'name' => $name
        ]);
    }

    public function indexAction()
    {
        $betType =  $this->sysLogic->getBetsType();
        empty($betType) AND $this->show($betType);
        $bId = !empty($this->request->getQuery('type')) ? $this->request->getQuery('type') : $betType[0]['bet_id'];

        $res = $this->sysLogic->getBets($bId);
        $resRule = $this->sysLogic->getBetRules($res['bet_id']);

        $rules = [];
        //整合类型
        foreach ($resRule as $rule) {
            if (empty($rules[$rule['br_type']])) $rules[$rule['br_type']] = [];
            array_push($rules[$rule['br_type']], $rule);
        }

        foreach ($betType as  $value)
            $type[$value['bet_id']] = $value['bet_name'];
        $rule = $this->di['config']['game']['rule_te'];
        foreach ($rule as $key=>$value)
        {
            $rule[$key] = (string)($value);
        }

        $this->di['view']->setVars([
                'te' => $rule,
                'info' => $res,
                'type' => $type,
                'betRule' =>  $rules,
                'game' => $this->di['config']['game'],
                'perpage' => $this->di['config']['admin']['perPage']
            ]);

    }

    public function betSetAction()
    {
        $this->view->disable();
        if (!$this->validFlag)
        return $this->jsonMsg(0, $this->warnMsg);

        $brId = intval($this->request->getPost('br_id'));
        $bonus = trim($this->request->getPost('br_bonus'));
        $bool = $this->sysLogic->editBetRule($brId, $bonus);

        // if (!$bool)
        //     return $this->jsonMsg(0, 'Failed to setting');
        // else {
            $this->logContent = '修改ID：'. $brId .'赔率为：' . $bonus;
            return $this->jsonMsg(1, '设置成功');
        // }
    }

    public function betConfSetAction()
    {
        if (!$this->validFlag)
            return $this->jsonMsg(0, $this->warnMsg);
        $conditions = $this->request->getPost();

        $key =  $conditions['bet_id'];
        unset($conditions['bet_id']) ;

        $json = json_encode($conditions);
        if (!$this->sysLogic->doConfSet($key, $conditions))
            return $this->jsonMsg(0, '没有修改的数据');
        else
            $this->logContent = '彩票设置'.$json;
            return $this->jsonMsg(1, '设置成功');
    }

    public function fastAction()
    {
        $bet_id = !empty($this->request->getPost('bet_id'))?$this->request->getPost('bet_id'):1;
        $select = $this->request->getPost('select');

        $resRule = $this->sysLogic->getBetRules($bet_id);
        $br_base_type = [];
        foreach ($resRule as $key=>$value)
        {
            $br_base_type[0][] = $value['br_base_type'];
            $br_base_type[1][] = $value['br_id'];
        }
        $double = [];
        $te = [];
        $tema = $this->di['config']['game']['rule_te'];
        foreach ($tema as $key=>$value)
        {
            $tema[$key] = (string)($value);
        }

        foreach ($br_base_type[0] as $key=>$value)
        {
            if(!in_array($value,$tema))
            {
                $double[] = $br_base_type[1][$key];
            }
            else
            {
                $te[] = $br_base_type[1][$key];
            }
        }
        $data = ['double'=>$double,'te'=>$te];
        if($select == 1)
        {
            return $this->jsonMsg(1, $data);
        }
        else
        {
            return $this->jsonMsg(2, $data);
        }
    }

    public function saveChangeAction()
    {
        $brids = $this->request->getPost('br_id');
        $bonus = trim($this->request->getPost('odds'));
        if(!empty($brids))
        {
            $brids = implode(',', $brids);
            $bool = $this->sysLogic->editBetRules($brids, $bonus);
        }
        if (!$bool)
            return $this->jsonMsg(0, 'Failed to setting');
        else {
            $this->logContent = '修改ID：'. $brIds .'赔率为：' . $bonus;
            return $this->jsonMsg(1, '设置成功');
        }
    }

    public function acListAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? intval($this->request->getQuery('limit')) : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $page = 1;
        else
            $page = !empty($this->request->getQuery('page')) ? intval($this->request->getQuery('page')) : 1;
        empty($page) AND $page=1;
        $res = $this->sysLogic->getAcList($page, $limit);

        $res['limit'] = ceil($res['total']/$limit);
        $this->di['view']->setVars([
                'info' => $res,
                'perpage' => $perpage
            ]);
    }

    public function acAddAction()
    {
        $this->di['view']->setVars([
                'type' => $this->di['config']['admin']['activeType'],
            ]);
    }

    public function acDoAddAction()
    {
        if (!$this->validFlag)
            return $this->jsonMsg(0, $this->warnMsg);
        $date = $this->request->getPost();

        if($date['pa_type'] ==1) {
            $date['pa_gift_money'] = $date['pa_money1'];

        } else {
            $date['pa_money'] = $date['pa_money3'];
        }
        unset($date['pa_money3']);
        unset($date['pa_money1']);

        $date['pa_starttime'] = strtotime($date['pa_starttime']);
        $date['pa_endtime'] = strtotime($date['pa_endtime']);
        $date['pa_status'] = 1;
        $date['pa_title'] = htmlspecialchars(trim($this->request->getPost('pa_title')));
        $date['pa_content'] = htmlspecialchars(trim($this->request->getPost('pa_content')));

        if($this->sysLogic->stringLen($date['pa_title'],2) > 32)
            return $this->jsonMsg(0, '标题必须小于32个字');

        $upLib = new \Components\Utils\GoodsImgUpload($this->di);

        if (!empty($_FILES['pa_img']['tmp_name']))
        {
            $date['pa_img'] = $upLib->upload($_FILES['pa_img'] , $this->di['config']['admin']['imgViewPath']);
            if (!empty($avatar->errmsg))
                return $this->jsonMsg(2, 'Failed to upload image');
            $date['pa_img'] = $date['pa_img'];
        }

        $bool = $this->sysLogic->activeAdd($date);

        if (!$bool)
            return $this->jsonMsg(2, 'Failed to add');
        else {
            $this->logContent = '添加活动ID：'. $bool ;
            return $this->jsonMsg(1, '添加成功' );
        }
    }

    public function acEditAction()
    {
        $this->validFlag OR $this->jsonMsg('0', $this->warnMsg);
        $aId = intval($this->request->getQuery('a_id'));

        $info = $this->sysLogic->getActive($aId);
        if (empty($info))
            return $this->showMsg('活动不存在', true, '/system/aclist');
        $url = $this->sysLogic->getUrl();

        $this->di['view']->setVars([
                'info' => $info,
                'type' => $this->di['config']['admin']['activeType'],
                'url' => $url,
            ]);
    }

    public function acDoEditAction()
    {
        $this->view->disable();
        if (!$this->validFlag)
            return $this->jsonMsg('0', $this->warnMsg);

        $aId = intval($this->request->getPost('a_id'));
        $atitle = htmlspecialchars(trim($this->request->getPost('a_title')));
        $acontent = htmlspecialchars(trim($this->request->getPost('a_content')));

        $upLib = new \Components\Utils\GoodsImgUpload($this->di);
        $img = '';
        if (!empty($_FILES['img']['tmp_name']))
        {
            $img = $upLib->upload($_FILES['img'] , $this->di['config']['admin']['imgViewPath']);
            if (!empty($avatar->errmsg))
                return $this->jsonMsg(2, 'Failed to upload image');
        }

        $bool = $this->sysLogic->activeEdit($aId, $atitle, $acontent, $img);
        if (!$bool)
            return $this->jsonMsg(2, '没有发生修改');
        $this->logContent = '修改活动ID：'. $aId ;
        return $this->jsonMsg(1, '修改成功');
    }

    public function acDelAction()
    {
        $this->view->disable();
        if (!$this->validFlag)
            return $this->jsonMsg('0', $this->warnMsg);

        $aId = intval($this->request->getPost('a_id'));
        $bool = $this->sysLogic->delActive($aId);
        if (!$bool)
            return $this->jsonMsg(2, 'Deleted failed');
        else {
            $this->logContent = '删除活动ID：'. $aId ;
            return $this->jsonMsg(1, '删除成功');
        }
    }
//active end

//notice begin
    public function ntListAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? intval($this->request->getQuery('limit')) : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $page = 1;
        else
            $page = !empty($this->request->getQuery('page')) ? intval($this->request->getQuery('page')) : 1;
        empty($page) AND $page=1;
        $res = $this->sysLogic->getNtList($page, $limit);
        $res['limit'] = ceil($res['total']/$limit);

        $this->di['view']->setVars([
                'info' => $res,
                'perpage' => $perpage
            ]);

    }

    public function ntEditAction()
    {
        $ntId = intval($this->request->getQuery('n_id'));
        $info = $this->sysLogic->getNotice($ntId);
        if (empty($info))
            return $this->showMsg('通告不存在', true, '/system/ntlist');

        $this->di['view']->setVars([
                'info' => $info,
                'url' => $url,
            ]);
    }

    public function DoNtEditAction()
    {
        $this->view->disable();
        if (!$this->validFlag)
            return $this->jsonMsg(2,$this->warnMsg);

        $ntId = intval($this->request->getPost('n_id'));
        $ntitle = htmlspecialchars(trim($this->request->getPost('n_title')));
        $ncontent = htmlspecialchars(trim($this->request->getPost('n_content')));

        $bool = $this->sysLogic->noticeEdit($ntId, $ntitle, $ncontent);
        if (!$bool)
            return $this->jsonMsg(2, '没有发生修改');
        else {
            $this->logContent = '修改公告ID：'. $ntId ;
            return $this->jsonMsg(1, '修改成功');
        }
    }

//notice add view
    public function ntAddAction()
    {


    }

    public function ntDoAddAction()
    {
        $this->view->disable();
        if (!$this->validFlag) {
            return $this->jsonMsg(2,$this->warnMsg);
        }

        $condition['n_title'] = htmlspecialchars(trim($this->request->getPost('n_title')));
        $condition['n_content'] = htmlspecialchars(trim($this->request->getPost('n_content')));

        $condition['n_created_time'] = $_SERVER['REQUEST_TIME'];
        $bool = $this->sysLogic->addNotice($condition);

        if (!$bool) {
            return $this->jsonMsg(2, 'Failed to add');
        } else {
            $this->logContent = '添加公告ID：'. $bool ;
            return $this->jsonMsg(1, '添加成功');
        }
    }

    public function ntDelAction()
    {
        $this->view->disable();
        if (!$this->validFlag)
            return $this->jsonMsg(0,$this->warnMsg);

        $ntId = intval($this->request->getPost('n_id'));
        $bool = $this->sysLogic->delNotice($ntId);

        if (!$bool)
            return $this->jsonMsg(2, 'Failed to deleted');
        else
        {
            $this->logContent = '删除公告ID：'. $ntId ;
            return $this->jsonMsg(1, '删除成功');
        }
    }
//notice end

//base begin
    public function baseAction()
    {
        $res = $this->sysLogic->getBase();
        $this->di['view']->setVars([
                'info' => $res
            ]);
    }

    public function doBaseAction()
    {
        $this->view->disable();
        if (!$this->validFlag)
            return $this->jsonMsg(0, $this->warnMsg);

        $info = $this->request->getPost();
        if (empty($info['sc_10']))
            return $this->jsonMsg(0, '域名或IP不能为空');

        if (!filter_var($info['sc_10'], FILTER_VALIDATE_URL) && !filter_var($info['sc_10'], FILTER_VALIDATE_IP))
            return $this->jsonMsg(0, '域名或IP不合法');

        foreach ($info as $key => $value) {
             $newkey = explode('_', $key);
             $date[end($newkey)] = $value;
        }
        $date[3] = sprintf("%.2f",$date[3]);
        // $date[4] = sprintf("%.4f",$date[4]);
        // $date[5] = sprintf("%.4f",$date[5]);
        $date[6] = sprintf("%.4f",$date[6]);
        $date[7] = sprintf("%.4f",$date[7]);
        $date[8] = sprintf("%.4f",$date[8]);
        $bool = $this->sysLogic->editBase($date);
        $json = json_encode($date);
        $this->logContent = '基础设置'.$json;
        return $this->jsonMsg(1, '设置成功');
    }

//pay begin
    public function payListAction()
    {
        $type = !empty($this->request->getQuery('type')) ? $this->request->getQuery('type') : key($this->di['config']['payType']);
        $res = $this->sysLogic->getPayConf($type);

        $info = [];
        $payType = $this->config['payType'];
        foreach ($payType as $keys => $values) {
            foreach ($values as $key => $value) {
                 $pay[$key] = $value;
            }
        }
        foreach ($res as $key => $value) {
            $info[$type][] = [
                'pcc_id' => $value['pcc_id'],
                'pcc_name' => $value['pcc_name'],
                'pcc_status' => $value['pcc_status'],
                'pcc_memo' => $value['pcc_memo'],
                'pcc_min' => $value['pcc_min'],
                'pcc_max' => $value['pcc_max']
            ];
        }

        $code = '';
        if($type==9)
            $code = $this->redis->get('aliqrcodebase64');
        if($type==11)
            $code = $this->redis->get('wxqrcodebase64');
        $this->di['view']->setVars([
                'type' => $type,
                'res' => $info,
                'payType' => $pay,
                'code' => $code
            ]);
    }

    public function payStatusAction()
    {
        $id = intval($this->request->getPost('id'));
        $status = intval($this->request->getPost('status'));
        if (empty($id) or empty($status))
            return $this->jsonMsg(2, 'IntvalDate');

        if (!$this->sysLogic->doChangestatus($id, $status))
            return $this->jsonMsg(2, 'Failed to operation ');
        else {
            $do = $status == 1 ? '关' : '开';
            $this->logContent = '修改支付渠道id:'. $id . '状态 :' . $do;
            return $this->jsonMsg(1, '设置成功');
        }
    }

    public function payRegAction()
    {
        $type = intval($this->request->getPost('type'));

        $id = intval($this->request->getPost('id'));
        $min = sprintf("%.2f", $this->request->getPost('min'));
        $max = sprintf("%.2f", $this->request->getPost('max'));
        if (empty($id) || empty($min) || empty($max) ||  empty($type))
            return $this->jsonMsg(2, 'IntvalDate');
        if ($min > $max || $min == $max)
            return $this->jsonMsg(2, '限额最高必须大于最低');
        if($type == 7)
        {
            $arr['bank'] = $this->request->getPost('bank');
            $arr['name'] = $this->request->getPost('name');
            $arr['number'] = $this->request->getPost('number');
            foreach ($arr as $value) {
               if(!isset($value))
                    return $this->jsonMsg(2, 'IntvalDate');
            }
            $json = json_encode($arr);
        } else if($type < 7 || $type >11)
            $json = '';
        if (!$this->sysLogic->payReg($id, $min, $max, $json))
            return $this->jsonMsg(2, '数据无改动');
        else {
            $this->logContent = '修改支付渠道id：'. $id . ' 最大限额:' . $max . '最小限额:'. $min . $json ;
            return $this->jsonMsg(1, '设置成功');
        }
    }

    //banner begin
    public function bannerListAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? intval($this->request->getQuery('limit')) : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $page = 1;
        else
            $page = !empty($this->request->getQuery('page')) ? intval($this->request->getQuery('page')) : 1;
        empty($page) AND $page=1;
        $res = $this->sysLogic->bannerList($page, $limit);
        $url = $this->sysLogic->getUrl();

        $res['limit'] = ceil($res['total']/$limit);
        $this->di['view']->setVars([
                'info' => $res,
                'perpage' => $perpage,
                'url' => $url
            ]);
    }

    public function bnaddAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? $this->request->getQuery('limit') : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $page = 1;
        else
            $page = !empty($this->request->getQuery('page')) ? $this->request->getQuery('page') : 1;

        $res = $this->sysLogic->getAcList($page, $limit);

        $res['limit'] = ceil($res['total']/$limit);
        $this->di['view']->setVars([
                'info' => $res,
                'perpage' => $perpage
            ]);
    }

    public function bnDoAddAction()
    {
        $this->view->disable();
        $type = intval($this->request->getPost('type'));

        if (!$this->validFlag)
            return $this->jsonMsg(2,$this->warnMsg);
        if ($type ==3) {
            $sort = !empty($this->request->getPost('sort')) ? trim($this->request->getPost('sort')) : '';
            $title = htmlspecialchars(trim($this->request->getPost('title')));
            $URL = trim($this->request->getPost('URL'));
            if ( !isset($title) || !isset($URL))
                return $this->jsonMsg(2, '参数错误');
            $upLib = new \Components\Utils\GoodsImgUpload($this->di);
            $img = '';

            if (!empty($_FILES['img']['tmp_name']))
            {
                $img = $upLib->upload($_FILES['img'] , $this->di['config']['admin']['imgViewPath']);
                if (!empty($avatar->errmsg))
                    return $this->jsonMsg(2, 'Failed to upload image');

            }

            } else {
                $sort = !empty($this->request->getPost('sort1')) ? trim($this->request->getPost('sort1')) : '';
                $aId = intval($this->request->getPost('radio'));
                $info = $this->sysLogic->getActive($aId);
                if (empty($info))
                    return $this->showMsg('活动不存在', true, '/system/bannerlist');
                $img = '';
                if (!empty($_FILES['img1']['tmp_name']))
                {
                    $upLib = new \Components\Utils\GoodsImgUpload($this->di);
                    $img = $upLib->upload($_FILES['img1'] , $this->di['config']['admin']['imgViewPath']);
                    if (!empty($avatar->errmsg))
                        return $this->jsonMsg(2, 'Failed to upload image');

                    } else {
                        $img = $info['pa_img'];
                }
                $URL = 'activity/detail?paId='. $aId;
                $title = $info['pa_title'];
        }

        $bool = $this->sysLogic->addBanner($title, $sort, $URL, $img);
        if (!$bool)
            return $this->jsonMsg(2, 'Failed to add');
        else
        {
            $this->logContent = '添加bannerID：'. $bool ;
            return $this->jsonMsg(1, '添加成功');
        }
    }

    public function bnDelAction()
    {
        $this->view->disable();
        if (!$this->validFlag)
            return $this->jsonMsg(0,$this->warnMsg);

        $ibId = intval($this->request->getPost('ib_id'));
        $bool = $this->sysLogic->delBanner($ibId);

        if (!$bool)
            return $this->jsonMsg(2, 'Failed to deleted');
        else
        {
            $this->logContent = '删除bannerID：'. $ibId ;
            return $this->jsonMsg(1, '删除成功');
        }
    }

    public function bnEditAction()
    {
        $ibId = intval($this->request->getQuery('ib_id'));
        $info = $this->sysLogic->getBanner($ibId);
        if (empty($info))
            return $this->showMsg('Banner不存在', true, '/system/bannerlist');
        $url = $this->sysLogic->getUrl();
        $this->di['view']->setVars([
                'info' => $info,
                'url' => $url
            ]);
    }

    public function DoBnEditAction()
    {
        $this->view->disable();
        if (!$this->validFlag)
            return $this->jsonMsg(2,$this->warnMsg);
        $ibId = intval($this->request->getPost('ib_id'));
        $sort = !empty($this->request->getPost('sort')) ? trim($this->request->getPost('sort')) : '';
        $title = htmlspecialchars(trim($this->request->getPost('title')));
        $URL = trim($this->request->getPost('URL'));
        $upLib = new \Components\Utils\GoodsImgUpload($this->di);
        $img = '';

        if (!empty($_FILES['img']['tmp_name']))
        {
            $img = $upLib->upload($_FILES['img'] , $this->di['config']['admin']['imgViewPath']);
            if (!empty($avatar->errmsg))
                return $this->jsonMsg(2, 'Failed to upload image');
        }

        $bool = $this->sysLogic->editBanner($ibId, $title, $sort, $URL, $img);
        if (!$bool)
            return $this->jsonMsg(2, '没有发生修改');
        else {
            $this->logContent = '修改bannerID：'. $ibId ;
            return $this->jsonMsg(1, '修改成功');
        }
    }

    public function qrcodeAction()
    {
        $this->view->disable();
        if (empty($_FILES['img']['tmp_name']))
            return $this->jsonMsg(2, '二维码不能为空');
        $img = explode('.', $_FILES['img']['name']);

        if(!in_array(end($img),['jpg', 'png']))
            return $this->jsonMsg(2, '图片格式不正确');

        $type = intval($this->request->getPost('type'));
        if($fp = fopen($_FILES['img']['tmp_name'],"rb", 0))
        {
            $gambar = fread($fp,$_FILES['img']['size']);
            fclose($fp);
            $base64 = base64_encode($gambar);
            $base = 'data:image/jpg/png;base64,' . $base64;
        }
        if($type == 9)
        {
            if(!$this->redis->set('aliqrcodebase64', $base))
                return $this->jsonMsg(1, '上传失败');
        }
        if($type == 11)
        {
            if(!$this->redis->set('wxqrcodebase64', $base))
                return $this->jsonMsg(1, '上传失败');
        }
        if($type == 99)
        {
            if(!$this->redis->set('wxcustomerservicebase64', $base))
                return $this->jsonMsg(1, '上传失败');
        }
        return $this->jsonMsg(1, '上传成功');
    }

    public function payopenAction()
    {
        $payType = $this->config['payType'];
        foreach ($payType as $keys => $values) {
            foreach ($values as $key => $value) {
                 $pay[$key] = $value;
                 $rpay[$key] = 1;
            }
        }
        // $this->redis->del('payTypeStatus');
        $redisPay = $this->redis->get('payTypeStatus');

        if(empty($redisPay)) {
            $this->redis->set('payTypeStatus',$rpay);
            $redisPay = $rpay;
        }

        $this->di['view']->setVars([
                'payType' => $pay,
                'rpay' => $redisPay,
            ]);
    }

    public function changePayStatusAction()
    {
        $key = intval($this->request->getPost('key'));
        $status = intval($this->request->getPost('status'));
        if(empty($key) || empty($status))
            return $this->jsonMsg(2, 'invaldata');
        $value = $status == 1 ? 3 : 1;
        $rpay = $this->redis->get('payTypeStatus');
        $rpay[$key] = $value;
        if($this->redis->set('payTypeStatus',$rpay))
            return $this->jsonMsg(1, 'ok');
        return $this->jsonMsg(2, 'error');
    }

    public function WxCustomerServiceAction()
    {
         $code = '';
        $code = $this->redis->get('wxcustomerservicebase64');

        $this->di['view']->setVars([
                'type' => 99,
                'code' => $code
            ]);
    }
}

