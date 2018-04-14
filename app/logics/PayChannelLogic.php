<?php
use Phalcon\Security;

class PayChannelLogic extends LogicBase
{

    public function __construct()
    {
        $this->model = new PayChannelModel;
    }

    public function getLists($type)
    {
        return $this->model->getLists($type);
    }

    public function detailById($channelId)
    {
        return $this->model->detailById($channelId);
    }

    public function getAllChannels($status = FALSE)
    {
        $fields = 'pcc_id, pcc_name, pcc_type, pcc_min, pcc_max, pcc_flag';
        return $this->model->getAllChannels($status, $fields);
    }
}