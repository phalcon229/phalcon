<?php
namespace Components\Utils;

use Phalcon\Mvc\User\Component;

Class Validate extends Component
{
    public function userName($uName)
    {
        if (!$uName) return '请输入用户名';
        if (!preg_match('/^[a-zA-Z0-9_-]{4,15}$/', $uName))
            return '用户名为4-15位英文或数字';
    }

    public function pwd($pwd)
    {
        if (!$pwd) return '请输入密码';
        if (!preg_match('/^[a-z0-9_-~!@#$%^&=*()]{8,15}$/', $pwd))
            return '密码为8-15位字符';
    }

    public function mobi()
    {

    }

    public function email()
    {

    }

    public function name($name)
    {
        if (!$name) return '请输入姓名';
        if (!preg_match("/^[\x{4e00}-\x{9fa5}]{2,6}$/u", $name))
            return '姓名请填2-6位的中文';
    }

    public function account($account)
    {
        if (!$account) return '请输入银行账号';
        if (!preg_match('/^(\d{16,19})$/', $account))
            return '银行账号应为16-19位的数字';
    }

    public function bank($bank)
    {
        if (!preg_match("/^[\x{4e00}-\x{9fa5}]{2,10}$/u", $bank))
            return '开户行应为2-10位的中文';
    }

    public function phone($phone)
    {
        if (!preg_match("/^1(3|4|5|7|8)\d{9}$/",$phone))
            return '请输入正确的手机号';
    }
}