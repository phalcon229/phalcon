<?php

    $goldCity_config = [];

    // Base
    $goldCity_config['APPID'] = '9cb889b88b627b0dfc1e8e52f90441ad'; //请修改你的appid
    $goldCity_config['rsaPrivateKeyFilePath'] = 'MID_rsa_private_key.pem';//请修改你的商户私钥路径
    $goldCity_config['rsaPublicFilePath'] = 'rsa_public_key.txt';//请修改你的平台公钥路径

    // System
    $goldCity_config['HOST'] = 'https://api.rn3.cc/gateway';
    $goldCity_config['VERSION'] = '/v1';
    $goldCity_config['SIGN_TYPE'] = 'RSA2';



    // Request
    $goldCity_config['ORDER_PAYMENT_CONFIG'] = $goldCity_config['HOST'] . $goldCity_config['VERSION'] . "/trade";
    $goldCity_config['ORDER_PAYMENT_QUERY'] = $goldCity_config['HOST'] . $goldCity_config['VERSION'] . "/trade-query";
    $goldCity_config['WITHDRAWALS_MONEY'] = $goldCity_config['HOST'] . $goldCity_config['VERSION'] . "/withdraw";
    $goldCity_config['WITHDRaWALS_MONEY_QUERY'] = $goldCity_config['HOST'] . $goldCity_config['VERSION'] . "/withdraw-query";

    //config end

    $GLOBALS['goldCity_config'] = $goldCity_config;
