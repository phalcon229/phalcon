<?php
namespace Components\Utils;

use Phalcon\Mvc\User\Component;

class Curl extends Component
{
    public static function send($url, $fields = array(), $method = 'get'){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        switch ($method) {
            case 'get':
                    curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
                    break;
            case 'post':
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
                    break;
            case 'delete':
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
                    break;
            case 'put':
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
                    break;
            default:
                    # code...
                    break;
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    function curl_post($url = '',$data = array(), $headers=array()){
        $postfield = '';
        
        foreach ($data as $k=>$v){
            if($v){
                $postfield .= '&'.$k.'='.$v;
            }
        }
        
        $postfield = trim($postfield, '&');
        
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, 1);//启用POST提交
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfield); //设置POST提交的字符串
        curl_setopt($curl, CURLOPT_HTTPHEADER , $headerArr );
        curl_setopt($curl, CURLOPT_TIMEOUT, 25); // 超时时间
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; SeaPort/1.2; Windows NT 5.1; SV1; InfoPath.2)");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        $value = curl_exec($curl);
        curl_close($curl);
        
        return $value;
    }
    
    private static function _format_fields($fields){
        $uri_fields = '';
        foreach ($fields as $key => $value) {
                $uri_fields .= '/'.$key.'/'.$value.'/';
        }
        return $uri_fields;
    }
}