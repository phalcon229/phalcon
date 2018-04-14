<?php
    require_once 'config.php';
    /**
     * 类库自动加载。
     * @param string $class 对象类名
     * @return void
     */
    class Autoloader {
        public static function GAutoload($class)
        {
            $name = $class;
            if(false !== strpos($name,'\\')) {
                $name = strstr($class, '\\', true);
            }

            $filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/request/'. $name . '.php';
            if (is_readable($filename)) {
                require $filename;
                return;
            }

            $filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/'. $name . '.php';
            if (is_readable($filename)) {
                require $filename;
                return;
            }
        }
    }

    if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
        if (version_compare(PHP_VERSION, '5.3.0', '>='))
            spl_autoload_register('Autoloader::GAutoload', true, true);
         else
            spl_autoload_register('Autoloader::GAutoload');
    } else {
        function __autoload($classname)
        {
            Autoloader::GAutoload($classname);
        }
    }
?>
