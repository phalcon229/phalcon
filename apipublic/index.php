<?php

use Phalcon\Mvc\Application,
    Phalcon\DI\FactoryDefault,
    Phalcon\Loader,
    Phalcon\Events\Manager as EventsManager,
    Phalcon\Mvc\Dispatcher,
    Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter,
    Phalcon\Events\Event,
    Phalcon\Crypt,
    Phalcon\Mvc\Dispatcher\Exception as DispatcherException,
    Phalcon\Session\Adapter\Files as Session,
    Phalcon\Http\Response\Cookies,
    Phalcon\Mvc\View;

define('DS', DIRECTORY_SEPARATOR);
// 根目录定义
define("ROOT_PATH", dirname(__DIR__) . DS);

try {
    require_once  __DIR__ .'/../components/gold-city/goldCityAutoload.php';
    require_once  __DIR__ .'/../components/vendor/autoload.php';
    date_default_timezone_set( 'Asia/Shanghai');
    $di = new FactoryDefault();

    // set config service
    $di->setShared('config', function() {
        $config = require __DIR__ . '/../app/config/config.php';
        $ruleConfig = __DIR__ . '/../app/config/ruleConfig.php';
        $gameConfig=__DIR__ . '/../app/config/gameConfig.php';
        $adminConfig=__DIR__ . '/../app/config/adminConfig.php';
        $tipsConfig = __DIR__ . '/../app/config/tipsConfig.php';

        if(is_readable($ruleConfig))
        {
            $rConfig = require $ruleConfig;
            $config = array_merge($config, ['rules' => $rConfig]);
        }

        if(is_readable($gameConfig))
        {
            $lConfig = require $gameConfig;
            $config = array_merge($config, $lConfig);
        }

        if(is_readable($adminConfig))
        {
            $rConfig = require $adminConfig;
            $config = array_merge($config, ['admin' => $rConfig]);
        }

        if(is_readable($tipsConfig))
        {
            $rConfig = require $tipsConfig;
            $config = array_merge($config, ['tips' => $rConfig]);
        }
        return $config;
    });

    $config = $di['config'];

    $loader = new Loader();
    $loader->registerDirs([
        __DIR__ . '/../app/api/controllers',
        __DIR__ . '/../app/models/',
        __DIR__ . '/../app/logics/',
    ])->register();

    $loader->registerNamespaces([
        'Components\Utils' => __DIR__ . '/../components/utils/',
        'Components\Bets' => __DIR__ . '/../components/bets/',
        'Components\Payment' => __DIR__ . '/../components/payment/',
        'Plugins' => __DIR__ . '/../plugins/'
    ]);

    // set event listener
    $di->setShared('dispatcher', function() use ($config) {
        $eventManager = new EventsManager();

        $eventManager->attach(
            'dispatch:beforeDispatchLoop',
            new Plugins\VersionPlugin($config['verInfo'])
        );

        // $eventManager->attach(
        //     'dispatch:beforeException',
        //     new Plugins\NotFoundPlugin
        // );

        $eventManager->attach(
            'dispatch:beforeExecuteRoute',
            new Plugins\CallShieldPlugin
        );

        $dispatcher = new Dispatcher();
        $dispatcher->setEventsManager($eventManager);

        return $dispatcher;
    });

    $di->setShared('helper', new Components\Utils\Helper);
    $di->setShared('check', new Components\Utils\Validate);

    $di->setShared('session', function() {
        $session = new Session();
        $session->start();
        return $session;
    });

    $di->setShared('cookie', function() {
        $cookie = new Cookies();
        $cookie->useEncryption(true);
        return $cookie;
    });

    $di->set('crypt', function() {
        $crypt = new Phalcon\Crypt();
        $crypt->setKey('#_+//*(*&eA|;76$');
        return $crypt;
    });

    $di->set('PHPExcel', function () {
        require_once  __DIR__ .'/../components/utils/PHPExcel.php';
        return new PHPExcel();
    });

    $di->setShared('redis', function() use ($config) {
        $redis = new Redis();
        $redis->connect($config['redis']['host'], $config['redis']['port']);
        $redis->select($config['redis']['db']);
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

        return $redis;
    });

    // set database
    $di->setShared('db', function() use ($config) {
        $db = new DbAdapter(
            [
                'host' => $config['db']['host'],
                'port' => $config['db']['port'],
                'username' => $config['db']['user'],
                'password' => $config['db']['pass'],
                'dbname' => $config['db']['name'],
                'options' => [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, // 如果出现错误抛出错误警告
                    \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_TO_STRING, // 把所有的NULL改成""
                    \PDO::ATTR_TIMEOUT => 30
                ]
            ]
        );

        $db->query('SET NAMES utf8');

        return $db;
    });

    $di->setShared('wxClient', function() use ($config) {
        require __DIR__ . '/../components/vendor/autoload.php';
        $easyWechat = new \EasyWeChat\Foundation\Application($config['wxConf']);
        return $easyWechat;
    });

    $di->setShared('crypt', function() use ($config) {
        $crypt = new Crypt();
        $crypt->setKey($config['baseInfo']['cryptKey']);
        return $crypt;
    });

    $di->setShared('ErrorService', function() {
        $ec = new ErrorsController;
        return $ec;
    });

    $application = new Application();
    $application->setDI($di);

    $application->useImplicitView(false);
    $application->handle()->getContent();


} catch (Exception $e) {
    echo $e->getMessage();
}