<?php
namespace Components\Bets;

use Phalcon\Mvc\User\Component;

class Utils extends Component
{
    public function analyzeFromResByType($bType, $data)
    {
        $detail = [];
        switch($bType)
        {
        //重庆时时彩
        case 1:
            $sum = array_sum($data);
            $detail[] = $sum % 2 == 0 ? '6-13' : '6-12';
            $detail[] = $sum >= 23 ? '6-10' : '6-11';
            if ($data[0] == $data[4])
                $detail[] = '1-16';
            else {
                $detail[] = $data[0]>$data[4] ? '1-14' : '1-15';//龙虎
            }
            for ($i = 0; $i < 5; $i++)
            {
                $j = $i + 1;
                $detail[] = $j . '-' . $data[$i];
                $detail[] = $data[$i] %2 == 0 ? $j . '-13' : $j . '-12';
                $detail[] = $data[$i] >=5 ? $j . '-10' : $j . '-11';
            }

            return [
                'zh' => $sum,
                'dx' => $sum >= 23 ? 1 : 2,
                'ds' => $sum % 2 == 0 ? 1 : 2,
                'detail' => $detail,
            ];
            break;
            
        //腾讯分分彩
        case 21:
            $sum = array_sum($data);
            $detail[] = $sum % 2 == 0 ? '6-13' : '6-12';
            $detail[] = $sum >= 23 ? '6-10' : '6-11';
            $detail[] = $data[0]>$data[4] ? '1-14' : '1-15';//龙虎
            for ($i = 0; $i < 5; $i++)
            {
                $j = $i + 1;
                $detail[] = $j . '-' . $data[$i];
                $detail[] = $data[$i] %2 == 0 ? $j . '-13' : $j . '-12';
                $detail[] = $data[$i] >=5 ? $j . '-10' : $j . '-11';
            }

            return [
                'zh' => $sum,
                'dx' => $sum >= 23 ? 1 : 2,
                'ds' => $sum % 2 == 0 ? 1 : 2,
                'detail' => $detail,
            ];
            break;
//        case 6:
        // 北京快乐8
        case 2:
            $noTotal1 = $noTotal2 = $noTotal3 = 0;
            for($i = 0; $i < 18; $i++)
            {
                if($i < 6)
                    $noTotal1 += $data[$i];
                else if($i < 12)
                    $noTotal2 += $data[$i];
                else if($i < 18)
                    $noTotal3 += $data[$i];
            }

            $no1 = $noTotal1 % 10;
            $no2 = $noTotal2 % 10;
            $no3 = $noTotal3 % 10;
            $sum = $no1 + $no2 + $no3;
            $detail[] = $sum > 13 ? '6-10' : '6-11';
            $detail[] = $sum % 2 == 0 ? '6-13' : '6-12';
            if($sum<=5)
            {
                $detail[] = '6-25';
            }
            else if($sum>=22)
            {
                $detail[] = '6-24';
            }
            else
            {

            }
            if($sum > 13)
            {
                $detail[] = $sum % 2 == 0 ? '6-45' : '6-44';
            }
            else
            {
                $detail[] = $sum % 2 == 0 ? '6-47' : '6-46';
            }
            for($i = 0; $i < 28; $i++)
            {
                if($sum == $i)
                {
                    if($i <= 9)
                        $detail[] = sprintf('6-%s', $i);
                    else
                        $detail[] = sprintf('6-%s', $i + 16);
                }
            }

            return [
                'result' => sprintf('%s,%s,%s', $no1, $no2, $no3),
                'zh' => $sum,
                'dx' => $sum > 13 ? 1 : 2,
                'ds' => $sum % 2 == 0 ? 1 : 2,
                'detail' => $detail,
            ];
            break;

        case 222:
            $no1 = $data[0];
            $no2 = $data[1];
            $no3 = $data[2];
            $sum = $no1 + $no2 + $no3;
            $detail[] = $sum > 13 ? '6-10' : '6-11';
            $detail[] = $sum % 2 == 0 ? '6-13' : '6-12';
            if($sum<=5)
            {
                $detail[] = '6-25';
            }
            else if($sum>=22)
            {
                $detail[] = '6-24';
            }
            else
            {

            }
            if($sum > 13)
            {
                $detail[] = $sum % 2 == 0 ? '6-45' : '6-44';
            }
            else
            {
                $detail[] = $sum % 2 == 0 ? '6-47' : '6-46';
            }
            for($i = 0; $i < 28; $i++)
            {
                if($sum == $i)
                {
                    if($i <= 9)
                        $detail[] = sprintf('6-%s', $i);
                    else
                        $detail[] = sprintf('6-%s', $i + 16);
                }
            }

            return [
                'result' => sprintf('%s,%s,%s', $no1, $no2, $no3),
                'zh' => $sum,
                'dx' => $sum > 13 ? 1 : 2,
                'ds' => $sum % 2 == 0 ? 1 : 2,
                'detail' => $detail,
            ];
            break;

        case 6:
            $noTotal1 = $noTotal2 = $noTotal3 = 0;
            for($i = 1; $i < 20; $i++)
            {
                if($i == 1 || $i == 4 ||$i == 7 || $i == 10 ||$i == 13 || $i == 16 )
                    $noTotal1 += $data[$i];
                else if($i == 2 || $i == 5 ||$i == 8 || $i == 11 ||$i == 14 || $i == 17)
                    $noTotal2 += $data[$i];
                else if($i == 3 || $i == 6 ||$i == 9 || $i == 12 ||$i == 15 || $i == 18)
                    $noTotal3 += $data[$i];
            }

            $no1 = $noTotal1 % 10;
            $no2 = $noTotal2 % 10;
            $no3 = $noTotal3 % 10;
            $sum = $no1 + $no2 + $no3;
            $detail[] = $sum > 13 ? '6-10' : '6-11';
            $detail[] = $sum % 2 == 0 ? '6-13' : '6-12';
            if($sum<=5)
            {
                $detail[] = '6-25';
            }
            else if($sum>=22)
            {
                $detail[] = '6-24';
            }
            else
            {

            }
            if($sum > 13)
            {
                $detail[] = $sum % 2 == 0 ? '6-45' : '6-44';
            }
            else
            {
                $detail[] = $sum % 2 == 0 ? '6-47' : '6-46';
            }
            for($i = 0; $i < 28; $i++)
            {
                if($sum == $i)
                {
                    if($i <= 9)
                        $detail[] = sprintf('6-%s', $i);
                    else
                        $detail[] = sprintf('6-%s', $i + 16);
                }
            }

            return [
                'result' => sprintf('%s,%s,%s', $no1, $no2, $no3),
                'zh' => $sum,
                'dx' => $sum > 13 ? 1 : 2,
                'ds' => $sum % 2 == 0 ? 1 : 2,
                'detail' => $detail,
            ];
            break;

        case 666:
            $no1 = $data[0];
            $no2 = $data[1];
            $no3 = $data[2];
            $sum = $no1 + $no2 + $no3;
            $detail[] = $sum > 13 ? '6-10' : '6-11';
            $detail[] = $sum % 2 == 0 ? '6-13' : '6-12';
            if($sum<=5)
            {
                $detail[] = '6-25';
            }
            else if($sum>=22)
            {
                $detail[] = '6-24';
            }
            else
            {

            }
            if($sum > 13)
            {
                $detail[] = $sum % 2 == 0 ? '6-45' : '6-44';
            }
            else
            {
                $detail[] = $sum % 2 == 0 ? '6-47' : '6-46';
            }
            for($i = 0; $i < 28; $i++)
            {
                if($sum == $i)
                {
                    if($i <= 9)
                        $detail[] = sprintf('6-%s', $i);
                    else
                        $detail[] = sprintf('6-%s', $i + 16);
                }
            }

            return [
                'result' => sprintf('%s,%s,%s', $no1, $no2, $no3),
                'zh' => $sum,
                'dx' => $sum > 13 ? 1 : 2,
                'ds' => $sum % 2 == 0 ? 1 : 2,
                'detail' => $detail,
            ];
            break;
        // 广东快乐十分
        case 4:
            $sum = array_sum($data);
            $dx = 0;
            $ds = 0;

            // 总和单双
            if($sum % 2 == 0)
            {
                $detail[] = '6-13';
                $ds = 1;
            }
            else
            {
                $detail[] = '6-12';
                $ds = 2;
            }

            // 总和大小
            if($sum >= 85 && $sum <= 132)
            {
                $detail[] = '6-10';
                $dx = 1;
            }
            else if($sum >= 36 && $sum <= 83)
            {
                $detail[] = '6-11';
                $dx = 2;
            }
            else
            {
                $detail[] = '6-53';
                $dx = 3;
            }

            // 总和尾大小
            $detail[] = $sum % 10 >= 5 ? '6-22' : '6-23';

            for ($i = 0; $i < 8; $i++)
            {
                $j = $i + 18;
                if($data[$i]>=10)
                {
                    $detail[] = $j . '-' . ($data[$i]+16);
                }
                else
                {
                    $detail[] = $j . '-' . intval($data[$i]);
                }
                $rootId = 0;
                switch($i)
                {
                    // 第一球
                    case 0:
                        $rootId = '18-';
                        break;
                    // 第二球
                    case 1:
                        $rootId = '19-';
                        break;
                    // 第三球
                    case 2:
                        $rootId = '20-';
                        break;
                    // 第四球
                    case 3:
                        $rootId = '21-';
                        break;
                    // 第五球
                    case 4:
                        $rootId = '22-';
                        break;
                    // 第六球
                    case 5:
                        $rootId = '23-';
                        break;
                    // 第七球
                    case 6:
                        $rootId = '24-';
                        break;
                    // 第八球
                    case 7:
                        $rootId = '25-';
                        break;
                }
                //龙虎
                if($i <= 3 && $data[$i] != $data[7-$i])
                {
                    $detail[] = $data[$i]>$data[7-$i] ? $j.'-48' : $j.'-49';
                }
                // 大小
                $detail[] = $data[$i] > 10 ? $rootId . 10 : $rootId . 11;
                // 单双
                $detail[] = $data[$i] % 2 == 0 ? $rootId . 13 : $rootId . 12;
                // 尾大小
                $detail[] = $data[$i] % 10 >= 5 ? $rootId . 22 : $rootId . 23;
            }

            return [
                'zh' => $sum,
                'dx' => $dx,
                'ds' => $ds,
                'detail' => $detail,
            ];
            break;

        // 幸运飞艇
        case 3:


            // 冠亚和-大小单双
            $he = $data[0] + $data[1];
            if($he % 2 == 0)
            {
                $detail[] = '7-13';
                $ds = 1;
            }
            else
            {
                $detail[] = '7-12';
                $ds = 2;
            }

            if($he > 11)
            {
                $detail[] = '7-10';
                $dx = 1;
            }
            else
            {
                $detail[] = '7-11';
                $dx = 2;
            }

            for($i = 0; $i < 10; $i++)
            {
                $j = $i + 8;
                if($data[$i]>=10)
                {
                    $detail[] = $j . '-' . ($data[$i]+7);
                }
                else
                {
                    $detail[] = $j . '-' . intval($data[$i]);
                }

                $rootId = 0;
                switch($i)
                {
                    // 冠军-大小单双龙虎
                    case 0:
                        $rootId = '8-';
                        break;
                    // 亚军-大小单双龙虎
                    case 1:
                        $rootId = '9-';
                        break;
                    // 第三名-大小单双龙虎
                    case 2:
                        $rootId = '10-';
                        break;
                    // 第四名-大小单双龙虎
                    case 3:
                        $rootId = '11-';
                        break;
                    // 第五名-大小单双龙虎
                    case 4:
                        $rootId = '12-';
                        break;
                    // 第六名-大小单双
                    case 5:
                        $rootId = '13-';
                        break;
                    // 第七名-大小单双
                    case 6:
                        $rootId = '14-';
                        break;
                    // 第八名-大小单双
                    case 7:
                        $rootId = '15-';
                        break;
                    // 第九名-大小单双
                    case 8:
                        $rootId = '16-';
                        break;
                    // 第十名-大小单双
                    case 9:
                        $rootId = '17-';
                        break;
                }

                $detail[] = $rootId . ($data[$i] % 2 == 0 ? 13 : 12);
                $detail[] = $rootId . ($data[$i] >= 6 ? 10 : 11);

                if($i < 5 && $data[$i] != $data[9-$i])
                {
                    $detail[] = $rootId . ($data[$i] > $data[9-$i] ? 14 : 15);
                }
            }
            if($he<10){
                $detail[] = '7-'.$he;
            }
            else
            {
                $detail[] = '7-'.($he+16);
            }
            return [
                'zh' => $he,
                'ds' => $ds,
                'dx' => $dx,
                'detail' => $detail,
            ];
            break;
            // 北京塞车PK10
            case 5:
                $he = $data[0] + $data[1];
                if($he % 2 == 0)
                {
                    $detail[] = '7-13';
                    $ds = 1;
                }
                else
                {
                    $detail[] = '7-12';
                    $ds = 2;
                }

                if($he > 11)
                {
                    $detail[] = '7-10';
                    $dx = 1;
                }
                else
                {
                    $detail[] = '7-11';
                    $dx = 2;
                }

                for($i = 0; $i < 10; $i++)
                {
                    $j = $i + 8;
                    if($data[$i]>=10)
                    {
                        $detail[] = $j . '-' . ($data[$i]+7);
                    }
                    else
                    {
                        $detail[] = $j . '-' . intval($data[$i]);
                    }

                    $rootId = 0;
                    switch($i)
                    {
                        // 冠军-大小单双龙虎
                        case 0:
                            $rootId = '8-';
                            break;
                        // 亚军-大小单双龙虎
                        case 1:
                            $rootId = '9-';
                            break;
                        // 第三名-大小单双龙虎
                        case 2:
                            $rootId = '10-';
                            break;
                        // 第四名-大小单双龙虎
                        case 3:
                            $rootId = '11-';
                            break;
                        // 第五名-大小单双龙虎
                        case 4:
                            $rootId = '12-';
                            break;
                        // 第六名-大小单双
                        case 5:
                            $rootId = '13-';
                            break;
                        // 第七名-大小单双
                        case 6:
                            $rootId = '14-';
                            break;
                        // 第八名-大小单双
                        case 7:
                            $rootId = '15-';
                            break;
                        // 第九名-大小单双
                        case 8:
                            $rootId = '16-';
                            break;
                        // 第十名-大小单双
                        case 9:
                            $rootId = '17-';
                            break;
                    }

                    $detail[] = $rootId . ($data[$i] % 2 == 0 ? 13 : 12);
                    $detail[] = $rootId . ($data[$i] >= 6 ? 10 : 11);

                    if($i < 5 && $data[$i] != $data[9-$i])
                    {
                        $detail[] = $rootId . ($data[$i] > $data[9-$i] ? 14 : 15);
                    }
                }
                if($he<10){
                    $detail[] = '7-'.$he;
                }
                else
                {
                    $detail[] = '7-'.($he+16);
                }
                return [
                    'zh' => $he,
                    'ds' => $ds,
                    'dx' => $dx,
                    'detail' => $detail,
                ];
                break;
            case 7:
            case 8:
            case 9:

            $sum = array_sum($data);
            $detail[] = $sum >= 11 ? '6-10' : '6-11';
            $detail[] = $sum % 2 == 0 ? '6-13' : '6-12';
            for ($i = 0; $i < 3; $i++)
            {
                $j = $i + 1;
                $detail[] = $j . '-' . $data[$i];
                $detail[] = $data[$i] %2 == 0 ? $j . '-13' : $j . '-12';
                $detail[] = $data[$i] >=4 ? $j . '-10' : $j . '-11';
            }
//            for($i = 3; $i < 19; $i++)
//            {
//                if($sum == $i)
//                {
//                    if($i <= 9)
//                        $detail[] = sprintf('6-%s', $i);
//                    else
//                        $detail[] = sprintf('6-%s', $i + 16);
//                }
//            }
            return [
                'zh' => $sum,
                'dx' => $sum >= 11 ? 1 : 2,
                'ds' => $sum % 2 == 0 ? 1 : 2,
                'detail' => $detail,
            ];
            break;

            case 10:
            case 11:
            case 12:
            case 13:

            $sum = array_sum($data);
            if($sum>30)
            {
                $detail[] = '6-10';
                $dx = 1;
            }
            else if($sum<30)
            {
                $detail[] = '6-11';
                $dx = 2;
            }
            else
            {
                $detail[] = '6-53';
                $dx = 3;
            }
            $detail[] = $sum % 2 == 0 ? '6-13' : '6-12';
            for ($i = 0; $i < 5; $i++)
            {
                if($data[$i]>=10)
                {
                    $j = $i + 1;
                    $detail[] = $j . '-' . ($data[$i]+16);
                }
                else
                {
                    $j = $i + 1;
                    $detail[] = $j . '-' . intval($data[$i]);
                }
                $detail[] = $data[$i] %2 == 0 ? $j . '-13' : $j . '-12';
                if($data[$i]>=6)
                {
                    $detail[] = $j.'-10';
                }
                else
                {
                    $detail[] = $j.'-11';
                }
            }

            return [
                'zh' => $sum,
                'dx' => $dx,
                'ds' => $sum % 2 == 0 ? 1 : 2,
                'detail' => $detail,
            ];
            break;
            case 17 :
            case 18 :
            case 19 :
            case 20 :
            $res = [];
            foreach ($data as $key => $value) {
                if($value>=10)
                    $res[$key] = $value;
                else
                    $res[$key] = substr($value, 1);
                }
            return [
                'detail' => $res,
            ];
            break;
            case 14:
            case 15:
            case 16:
            $res = [];
            $res['38'] = array_sum($data);
            $unique = array_unique($data);
            $num = count($unique);
            if ($num == 1) {
                $res['39'] = '*';
                $res['40'] = $unique[0].$unique[0].$unique[0];
                $suan = array_diff_assoc( $data, $unique);
                $res['43'] = current($suan).current($suan);
            } else if ($num == 2) {
                $suan = array_diff_assoc( $data, $unique);
                $res['43'] = current($suan).current($suan);
                $res['44'] = current($suan) .'|';
                $res['44'] .= current($suan) == current($unique) ? end($unique) : current($unique);
                $res['45'] = $data;
            } else {
                $res['41'] = $data;
                $res['45'] = $data;
            }
            if (in_array(implode(',', $data),['1,2,3','2,3,4','3,4,5','4,5,6']))
                $res['42'] = '*';
            return [
                'detail' => $res,
            ];
                break;
        }

    }

    public function analyzeDx($code, $num)
    {
        return array_map(function($c)use($num){return $c >= $num ? 1 : 2;}, $code);
    }

    public function analyzeDs($code)
    {
        return array_map(function($c){return $c % 2 == 0 ? 1 : 2;}, $code);
    }

    public function analyze11x5Dx($code, $num)
    {
        return array_map(function($c)use($num){if($c>=11) {return 0;}else{return $c >= $num ? 1 : 2;}}, $code);
    }

    public function analyze11x5Ds($code)
    {
        return array_map(function($c){if($c>=11){return 0;}else{ return $c % 2 == 0 ? 1 : 2;}}, $code);
    }

    public function analyzeZh($code, $num)
    {
        $sum = array_sum($code);
        return [$sum, $sum >= $num ? 1 : 2, $sum % 2 == 0 ? 1 : 2];
    }

    public function analyzeGdklsfZh($code)
    {
        $sum = array_sum($code);
        $res = [$sum];
        if($sum >= 36 && $sum <= 83)
            $res[] = 2;
        else if($sum >= 85 && $sum <= 132)
            $res[] = 1;
        else
            $res[] = 0;

        $res[] = $sum % 2 == 0 ? 1 : 2;

        return $res;
    }

    public function analyze11x5Zh($code)
    {
        $sum = array_sum($code);
        $res = [$sum];
        if($sum <= 29)
            $res[] = 2;
        else if($sum >= 31)
            $res[] = 1;
        else
            $res[] = 0;

        $res[] = $sum % 2 == 0 ? 1 : 2;

        return $res;
    }

    public function sortTrendByExpectAndNum($trend, $nums)
    {
        krsort($trend);
        if(count($trend) > $nums)
        {
            $trend = array_slice($trend, 0, 12, TRUE);
        }

        return $trend;
    }

    public function fetchBetRes($url, $params = '', $method = 'GET')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if($method == 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        
        $data = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if($data === false)
        {
            throw new \Exception($err);

        }
        else
        {
            return $data;
        }
    }
}
