<?php
namespace Components\Utils;

use \Phalcon\Validation,
    \Phalcon\Validation\Validator\PresenceOf,
    \Phalcon\Validation\Validator\Email,
    \Phalcon\Validation\Validator\Identical,
    \Phalcon\Validation\Validator\ExclusionIn,
    \Phalcon\Validation\Validator\InclusionIn,
    \Phalcon\Validation\Validator\Regex,
    \Phalcon\Validation\Validator\StringLength,
    \Phalcon\Validation\Validator\Between,
    \Phalcon\Validation\Validator\Confirmation,
    \Phalcon\Http\Request;

class RulesParse
{
    protected $rules;
    public $reqErr = false;
    public $methodErr = false;
    public $resFlag = true;   //　校验结果标识，true通过　false禁止
    public $warnMsg = false;    // 返回错误集，包含code和msg
    public $warnMsgCode = false; // 返回错误集，包含code
    public $warnMsgMsg = false;  // 返回错误集，包含msg

    public $_sanReq = array();
    protected $sanitize = array();

    /**
     * @name __construct
     *
     * @param $var
     * @param $rule
     *
     * @returns
     */
    public function __construct($rule)
    {
        $this->rules = $rule;
    }

    /**
     * @brief parse 进行规则解析
     *
     * @Returns
     */
    public function parse()
    {
        // 判断是否有要求请求方式
        if(isset($this->rules['_request']) && !$this->parseRequest())
            return false;

        // 判断解析对应请求方式的参数规则
        if(isset($this->rules['_method']) && !$this->parseMethod())
        {
            $this->methodErr = true;
            return false;
        }

        return true;
    }

    /**
     * @brief parseRequest 解析请求方式
     *
     * @Returns
     */
    protected function parseRequest()
    {
        $request = new Request();
        $res = 1;
        foreach($this->rules['_request'] as $_req)
        {
            switch($_req)
            {
            case 'ajax':
                if($request->isAjax())
                    $res = $res & 1;
                break;

            case 'soap':
                if($request->isSoapRequested())
                    $res = $res & 1;
                break;

            case 'secure':
                if($request->isSecureRequest())
                    $res = $res & 1;
                break;

            default:
                $res = $res & 0;
                break;
            }
        }

        if(!$res)
        {
            $this->reqErr = true;
            return false;
        }
        else
            return true;
    }

    /**
     * @brief parseMethod 解析action里面的方法
     *
     * @Returns
     */
    protected function parseMethod()
    {
        $methods = array();
        $datas = array();

        if (!isset($this->rules['_method']['post']) && !isset($this->rules['_method']['get']) && !isset($this->rules['_method']['cookie'])) {
            return true;
        }
        if(isset($this->rules['_method']['get']))
        {
            $methods = $this->rules['_method']['get'];
            $datas = $_GET;
        }
        if(isset($this->rules['_method']['cookie']))
        {
            $methods = $this->rules['_method']['cookie'];
            $datas = $_COOKIE;
        }
        if(isset($this->rules['_method']['post']))
        {
            $methods = $this->rules['_method']['post'];
            $datas = $_POST;
        }

        $validation = new Validation();
        $hasValiData = false;   // 是否存在待校验数据标识
        foreach($methods as $param)
        {
            if(!isset($this->rules[$param]))
            {
                continue;
            }
            if (empty($datas[$param]) && isset($this->rules[$param]['default']) && $this->rules[$param]['default'])
            {
                $datas[$param] = $this->rules[$param]['default'];
            }
            // 非必填且参数无值  无需验证
            if (!$this->rules[$param]['required'] && empty($datas[$param]))
            {
                continue;
            }
            $hasValiData = true;
            $paramKeys = array_keys($this->rules[$param]);
            foreach($paramKeys as $key)
            {
                $ruleValue = isset($this->rules[$param][$key]) ? $this->rules[$param][$key] : '';
                $ruleMsg = isset($this->rules[$param]['msg']) ? $this->rules[$param]['msg'] : '';

                $this->_rule($validation, $key, $ruleValue, $ruleMsg, $param);
            }
        }

        $warnMsg = $hasValiData ? $validation->validate($datas) : array();

        if(count($warnMsg))
        {
            $this->resFlag = false;
            $this->warnMsg = $this->_warnFormat($warnMsg);
            $this->warnMsgCode = $this->_warnFormat($warnMsg, 'code');
            $this->warnMsgMsg = $this->_warnFormat($warnMsg, 'msg');
            return false;
        }
        else
        {
            foreach ($this->sanitize as $param => $rule)
            {
                $datas[$param] = $rule($datas[$param]);
            }

            $this->_sanReq = $datas;
            return true;
        }
    }

    protected function _rule($validation, $key, $ruleValue, $ruleMsg, $param)
    {
        $errorCode = $this->_errorCode($key);
        switch($key)
        {
            case 'required':    // 必填
                if ($ruleValue) {
                    $validation->add($param, new PresenceOf(array(
                        'message' => $ruleMsg,
                        'code' => $errorCode
                    )));
                }
                break;

            case 'length':  // 验证长度
                if(is_array($ruleValue))
                {
                    $max = $ruleValue[1];
                    $min = $ruleValue[0];
                }
                else
                    $min = $max = $ruleValue;

                $validation->add($param, new StrlenValidator(array(
                    'max' => $max,
                    'min' => $min,
                    'message' => $ruleMsg,
                    'code' => $errorCode
                )));
                break;

            case 'filters':
                if(is_array($ruleValue))
                {
                    array_walk($ruleValue, function($item, $iKey) use ($validation) {
                        $validation->setFilters($param, $item);
                    });
                }
                $validation->setFilters($param, $ruleValue);
                break;

            case 'regex':   // 正则
                $validation->add($param, new Regex(array(
                    'pattern' => $ruleValue,
                    'message' => $ruleMsg,
                    'code' => $errorCode
                )));
                break;

            case 'between': // 数值区间
                if(is_array($ruleValue))
                {
                    $max = $ruleValue[1];
                    $min = $ruleValue[0];
                }
                else
                    $min = $max = $$ruleValue;

                $validation->add($param, new Between(array(
                    'minimum' => $min,
                    'maximum' => $max,
                    'message' => $ruleMsg,
                    'code' => $errorCode
                )));
                break;

            case 'range':   // 匹配数值
                $validation->add($param, new InclusionIn(array(
                    'domain' => $ruleValue,
                    'message' => $ruleMsg,
                    'code' => $errorCode
                )));
                break;

            case 'rangeout':    // 之外
                $validation->add($param, new ExclusionIn(array(
                    'domain' => $ruleValue,
                    'message' => $ruleMsg,
                    'code' => $errorCode
                )));
                break;

            case 'valueis':     // 等于固定值
                $validation->add($param, new Identical(array(
                    'value' => $ruleValue,
                    'message' => $ruleMsg,
                    'code' => $errorCode
                )));
                break;

            case 'equalTo': // 匹配另一参数
                $validation->add($param, new Confirmation(array(
                    'with' => $ruleValue,
                    'message' => $ruleMsg,
                    'code' => $errorCode
                )));
                break;

            case 'expect': //
                $validation->add($param, new Email(array(
                    'message' => $ruleMsg,
                    'code' => $errorCode
                )));
                break;

            case 'sanitize':    // 自定义处理规则（转义过滤等）
                    $this->sanitize[$param] = $ruleValue;
                break;
            }
    }

    /**
     * 校验错误码
     * @param  $field
     * @return [int]
     */
    private function _errorCode($field)
    {
        $error = array(
            'required' => 1001,
            'length' => 1002,
            'regex' => 1003,
            'between' => 1004,
            'range' => 1005,
            'rangeout' => 1006,
            'valueis' => 1007,
            'equalTo' => 1008,
            'expect' => 1009,
            'nums' => 1010,
            'filetype' => 1011
        );
        return isset($error[$field]) ? $error[$field] : 0;
    }

    /**
     * [_warnFormat 格式化错误输出]
     * @return [array] [description]
     */
    private function _warnFormat($msgObj, $type='both')
    {
        $msgArr = array();
        foreach ($msgObj as  $val)
        {
            $field = $val->getField();
            if(isset($msgArr[$field]))
            {
                continue;
            }
            if(empty($msgArr[$field]))
            {
                switch ($type) {
                    case 'code':
                            $msgArr[$field]['code'] = '';
                            // $msgArr[$field]['code'] = $val->getCode();
                    break;

                    case 'msg':
                            $msgArr[$field]['msg'] = $val->getMessage();
                    break;

                    case 'both':
                    default:
                            $msgArr[$field]['msg'] = $val->getMessage();
                            $msgArr[$field]['code'] = '';
                            // $msgArr[$field]['code'] = $val->getCode();
                    break;
                }
            }
        }
        return $msgArr;
    }
}