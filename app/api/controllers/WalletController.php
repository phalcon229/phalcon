<?php
//交易记录
class WalletController extends ControllerBase
{
    const NUM_PER_PAGE = 20;
    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->logic = new WalletRecordLogic();
        $this->usersLogic = new UsersLogic;
        $this->walletLogic = new WalletLogic();
        $this->userAgentLogic = new UserAgentLogic;

    }

    public function indexAction()
    {
        $page = intval($this->request->getQuery('page')) ?: 1;
        $nums = intval($this->request->getQuery('nums')) ?: SELF::NUM_PER_PAGE;
        $type = intval($this->request->getQuery('type')) ?: 0;

        if (!in_array($type, [0, 1, 3, 5, 7, 9, 11, 13, 15]))
            return $this->di['helper']->resRet('类型错误', 500);

        $lists = $this->logic->getUserLists($this->uId, $type, $page, $nums);
        $total = $this->logic->getUserTotal($this->uId, $type);//总计所有帐变记录

        return $this->di['helper']->resRet([
            'count' => $total['count'],
            'total' => $total['total'],
            'subInfo' => $lists
        ]);
    }

    public function showAction()
    {
        $page = intval($this->request->getQuery('page')) ?: 1;
        $nums = intval($this->request->getQuery('nums')) ?: SELF::NUM_PER_PAGE;
        $name = trim($this->request->getQuery('username')) ?: '';
        $next = intval($this->request->getQuery('next')) ?: 0;
        $type = intval($this->request->getQuery('type')) ?: 0;
        $startDay = strtotime(trim($this->request->getQuery('startDay'))) ?: 0;
        $endDay = strtotime(trim($this->request->getQuery('endDay'))) ?: 0;
        $uId = intval($this->request->getQuery('uId')) ?: $this->uId;

        //判断当前用户是否有权限查看指定ID报表
        $ids = [];
        if (!$agentIds = $this->userAgentLogic->getDown($this->uId))
            return $this->di['helper']->resRet();

        $ids = explode(',', rtrim($agentIds, ','));
        // if (!$name && $next)
        //     return $this->di['helper']->resRet('必须搜索用户名才能勾选查看下级', 500);

        if (!in_array($type, [0, 1, 3, 5, 7, 9, 11, 13, 15]))
            return $this->di['helper']->resRet('类型错误', 500);

        //获取指定用户名的
        if ($name)
        {
            if (!$uId = $this->usersLogic->getInfoByName($name)['u_id'])
                return $this->di['helper']->resRet('没有此用户', 500);
        }

        if (!in_array($uId, array_merge($ids, [$this->uId])))
            return $this->di['helper']->resRet('没有权限查看', 500);

        //获取指定用户的所有下级
        $all = [];
        if ($next)
        {
            $agents = $this->userAgentLogic->getDown($uId);
            $all = explode(',', rtrim($agents, ','));
        }

        //要查找的uIds
        $uIds = array_merge([$uId], $all);

        $lists = $this->logic->getListsByUids($uIds, $type, $startDay, $endDay, $page, $nums);

        $users = $this->usersLogic->getUserByIds($uIds);

        $names = $res = $tmp = [];
        foreach ($users as $v) {
            $names[$v['u_id']] = $v['u_name'];
        }

        foreach ($lists as $list) {
            $tmp = $list;
            $tmp['u_name'] = $names[$list['u_id']];
            $res[] = $tmp;
        }

        //总计笔数，收入，支出
        $total = $this->logic->getListsTotal($uIds, $type, $startDay, $endDay);

        $data = [
            'count' => $total['count'],
            'income' => $total['income'],
            'expend' => $total['expend'],
            'lists' => $res,
        ];

        return $this->di['helper']->resRet($data);
    }

}
