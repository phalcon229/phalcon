<?php

return [
    'verInfo' => [
        'verKey' => ['1.0', '1.1', '2.0'],
        'verName' => [
            '1.0' => 'V1',
            '1.1' => 'V11',
            '2.0' => 'V2'
        ]
    ],

    'baseInfo' => [
        'domain' => '',
        'cryptKey' => '#1dj9#co@ldp=j1V',
        'cookieDomain' => '.butup.me',
    ],

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'le',
        'user' => 'root',
        'pass' => '229229229',
    ],

    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'db' => 6,
    ],


    'openidType' => [
        'weixin' => 1,
    ],

    'callShield' => [
        'maxNums' => 10,  // 请求不能超过10次
        'timeRange' => 1000, // 1000毫秒内
        'msg' => 'Request too quickly'
    ],

    'wxConf' => [
        'debug' => true,
        'app_id' => 'wx15d9602890ce41ad',
        'secret' => '9ec7de7f2a003a5de63701b93f266279',
        'token' => 'pedometerapibutupme',
        'aes_key' => 'oBqdwjhiE4udQ85G4WUzkmwYTwYO3atRDVlYIdG6kTF',

        'jsApiList' => [
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'onMenuShareQZone',
            'onMenuShareQQ',
            'onMenuShareWeibo',
            'connectWXDevice',
            'openWXDeviceLib',
            'scanQRCode',
        ],

        'log' => [
            'level' => 'debug',
            'file' => __DIR__ . '/../logs/wechat.log'
        ],

        'oauth' => [
            'scopes' => ['snsapi_userinfo'],
            'callback' => '/auth/oauthcb'
        ],
    ],

    'acl' => [
        'baseLogin' => ['index' => '*', 'user' => '*', 'wechat' => ['base', 'bind'], 'pk' => ['base', 'fighters', 'tofight', 'history']]
    ],
    //注册类型
    'agent' => [1 => '会员', 3 => '代理'],

    'taskConf' => [
        'errorLog' => __DIR__ . '/../logs/tasks/open.log',
        'betResLog' => __DIR__ . '/../logs/tasks/betResultFromInterface.log',
    ],

    'mobiConf' => [
        'url' => 'http://47.100.15.44:8063/subSmsApi.aspx',
        'userID' => 364,
        'cpKey' => 'D73621D239DA17886BE760AD0F766D87',
        'cpSubID' => [ 1 => '【短信验证】', 2 => '【短信验证】',3 => '【短信验证】',4 => '【短信验证】',5 => '【短信验证】',6=> '【短信验证】'],
        'content' => [
                                1 =>'【短信验证】验证码：%s，您正在重置登录密码，请您输入短信验证码完成验证，验证码5分钟有效。',
                                2 =>'【短信验证】验证码：%s，您正在重置资金密码，请您输入短信验证码完成验证，验证码5分钟有效。' ,
                                3 =>'【短信验证】验证码：%s，请您输入短信验证码完成验证，验证码5分钟有效。',
                                4 =>'【短信验证】验证码：%s，您正在解绑旧手机，请您输入短信验证码完成验证，验证码5分钟有效。',
                                5 =>'【短信验证】验证码：%s，您正在修改微信账号，请您输入短信验证码完成验证，验证码5分钟有效。',
                                6 =>'【短信验证】验证码：%s，您正在绑定新手机，请您输入短信验证码完成验证，验证码5分钟有效。'
                            ]
    ],

    'captchaExpire' => 60,   // 手机验证码发送间隔时间
    'captchaAlive' => 5 * 60,  // 手机验证码存活时间

    'email' => [
        'user' => 'support@dazhongcai.cn',
        'pass' => 'Win5251314',
        'host' => 'smtp.exmail.qq.com',
        'secure' => 'ssl',
        'port' => 465,
        'subject' => [1=>'找回密码'],
        'content' => [ 1=>'验证码：%s，您正在重置登录密码，请您输入验证码完成验证，验证码5分钟有效。']
    ],
];
