<?php

return [
    'activeType' => [
        1 => '注册赠送',
        3 => '充值赠送'
    ],

    'perPage' => [
        25 => 25,
        50 => 50,
    ],

    //上传图片大小的最大值 (6M)
    'allowedFileSize' => 1024*1024*6,

    'imgViewPath' => 'admpublic/images/',

    //前台域名
    'webDomain' => 'http://dazhongcai.vip/',
    'staticDomain' => 'http://admin.dazhongcai.vip/',

    //总盘口数据刷新频率,单位秒
    'refreshTime' => [60, 30, 20, 10],

];

