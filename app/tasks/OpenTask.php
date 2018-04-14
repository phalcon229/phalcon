<?php

use \Components\Bets;
class OpenTask extends TaskBase
{
    private $cqssc = 1;
    private $bjkl8 = 2; // 快乐28
    private $mlaft = 3;
    private $gdklsf = 4;
    private $bjpk10 = 5;
    private $cakeno = 6;
    private $fjks = 7;
    private $bjks = 8;
    private $jsks = 9;
    private $gd11x5 = 10;
    private $zj11x5 = 11;
    private $sd11x5 = 12;
    private $ln11x5 = 13;
    private $fjksG = 14;
    private $bjksG = 15;
    private $jsksG = 16;
    private $gd11x5G = 17;
    private $zj11x5G = 18;
    private $sd11x5G = 19;
    private $ln11x5G = 20;
    private $Txffc = 21;

    public function agentEarnAction()
    {
        $len = $this->di['redis']->llen('agent:earn');
        if($len > 0)
        {
            $betsObj = new Bets\Bets;
            for($i = 0; $i < $len; $i++)
            {
                $info = $this->di['redis']->lpop('agent:earn');
                if($info)
                {
                    if($betsObj->agentEarn($info) == true)
                        echo '[', date('Y-m-d H:i:s'), '][agent-earn][succ]代理抽佣计算成功，data:', json_encode($info), "\n";
                    else
                        echo '[', date('Y-m-d H:i:s'), '][agent-earn][fail]代理抽佣计算失败，data:', json_encode($info), "\n";
                }
            }
        }
    }

    public function countMoneyAction()
    {
        $conditions['startTime'] = strtotime(date('Y-m-d',strtotime(date('Y-m-d'))-1));
        $conditions['endTime'] = $conditions['startTime'] + 86399;

        $walletRecord = new WalletRecordLogic();

        $give = $walletRecord->getTotalByType(9,$conditions['startTime'], $conditions['endTime']);//赠送

        $recharge = $walletRecord->getTotalByType(1,$conditions['startTime'], $conditions['endTime']);//充值

        $withdraw = $walletRecord->getTotalByType(5,$conditions['startTime'], $conditions['endTime']);//提现

        $balance = $walletRecord->getTotalBalance($conditions['startTime']);//余额

        $balanceAfter = $walletRecord->getTotalBalance($conditions['endTime']);//余额

        $type = '';
        $earn = $walletRecord->getTotalByType($type,$conditions['startTime'], $conditions['endTime']);//平台盈亏

        $moneyModel = new MoneyTotalModel();
        $res = $moneyModel->addCount($conditions['startTime'],$give['uwr_money'],$recharge['uwr_money'],$withdraw['uwr_money'],$balance['uwr_balance'],$earn['uwr_money'],$balanceAfter['uwr_balance']);
        if($res)
        {
            echo '[', date('Y-m-d H:i:s'), '][agent-report][succ]统计金额入库成功',"\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][agent-report][fail]统计金额入库失败',"\n";
        }

    }

    public function countTrendAction()
    {
        $len = $this->di['redis']->llen('open:analyze');
        if($len > 0)
        {
            $utilsObj = new Bets\Utils;
            for($i = 0; $i < $len; $i++)
            {
                $info = $this->di['redis']->lpop('open:analyze');
                if($info)
                {

                    $code = explode(',', $info['info']['opencode']);
                    $expect = substr($info['info']['expect'], -3);
                    if(!$code || !$expect)
                        continue;

                    $timeout = 3600;

                    $codeDx = $codeDs = NULL;

                    switch($info['type'])
                    {
                    case $this->cqssc:// 重庆时时彩

                        $codeDx = $utilsObj->analyzeDx($code, 5);
                        $codeDs = $utilsObj->analyzeDs($code);
                        $codeZh = $utilsObj->analyzeZh($code, 23);

                        // 计算冷热
                        $lr = (array)$this->di['redis']->get('analyze:1:lr');
                        for($i = 0; $i < 5; $i++)
                        {
                            $dxNum = $codeDx[$i] == 1 ? 1 : 2;
                            $dxKey = $i . '-dx';
                            if(array_key_exists($dxKey, $lr) === false || $lr[$dxKey][1] != $dxNum)
                                $lr[$dxKey] = [$i+1, $dxNum, 1];
                            else if($lr[$dxKey][1] == $dxNum)
                                $lr[$dxKey][2]++;

                            $dsNum = $codeDs[$i] == 1 ? 3 : 4;
                            $dsKey = $i . '-ds';
                            if(array_key_exists($dsKey, $lr) === false || $lr[$dsKey][1] != $dsNum)
                                $lr[$dsKey] = [$i+1, $dsNum, 1];
                            else if($lr[$dsKey][1] == $dsNum)
                                $lr[$dsKey][2]++;
                        }

                        $zhDxNum = $codeZh[1] == 1 ? 1 : 2;
                        $zhDxKey = '6-dx';
                        if(array_key_exists($zhDxKey, $lr) === false || $lr[$zhDxKey][1] != $zhDxNum)
                            $lr[$zhDxKey] = [6, $zhDxNum, 1];
                        else if($lr[$zhDxKey][1] == $zhDxNum)
                            $lr[$zhDxKey][2]++;

                        $zhDsNum = $codeZh[2] == 1 ? 3 : 4;
                        $zhDsKey = '6-ds';
                        if(array_key_exists($zhDsKey, $lr) === false || $lr[$zhDsKey][1] != $zhDsNum)
                            $lr[$zhDsKey] = [6, $zhDsNum, 1];
                        else if($lr[$zhDsKey][1] == $zhDsNum)
                            $lr[$zhDsKey][2]++;

                        $this->di['redis']->setEx('analyze:1:lr', $timeout, $lr);
                        break;

                    case $this->Txffc:// 重庆时时彩

                        $codeDx = $utilsObj->analyzeDx($code, 5);
                        $codeDs = $utilsObj->analyzeDs($code);
                        $codeZh = $utilsObj->analyzeZh($code, 23);

                        // 计算冷热
                        $lr = (array)$this->di['redis']->get('analyze:21:lr');
                        for($i = 0; $i < 5; $i++)
                        {
                            $dxNum = $codeDx[$i] == 1 ? 1 : 2;
                            $dxKey = $i . '-dx';
                            if(array_key_exists($dxKey, $lr) === false || $lr[$dxKey][1] != $dxNum)
                                $lr[$dxKey] = [$i+1, $dxNum, 1];
                            else if($lr[$dxKey][1] == $dxNum)
                                $lr[$dxKey][2]++;

                            $dsNum = $codeDs[$i] == 1 ? 3 : 4;
                            $dsKey = $i . '-ds';
                            if(array_key_exists($dsKey, $lr) === false || $lr[$dsKey][1] != $dsNum)
                                $lr[$dsKey] = [$i+1, $dsNum, 1];
                            else if($lr[$dsKey][1] == $dsNum)
                                $lr[$dsKey][2]++;
                        }

                        $zhDxNum = $codeZh[1] == 1 ? 1 : 2;
                        $zhDxKey = '6-dx';
                        if(array_key_exists($zhDxKey, $lr) === false || $lr[$zhDxKey][1] != $zhDxNum)
                            $lr[$zhDxKey] = [6, $zhDxNum, 1];
                        else if($lr[$zhDxKey][1] == $zhDxNum)
                            $lr[$zhDxKey][2]++;

                        $zhDsNum = $codeZh[2] == 1 ? 3 : 4;
                        $zhDsKey = '6-ds';
                        if(array_key_exists($zhDsKey, $lr) === false || $lr[$zhDsKey][1] != $zhDsNum)
                            $lr[$zhDsKey] = [6, $zhDsNum, 1];
                        else if($lr[$zhDsKey][1] == $zhDsNum)
                            $lr[$zhDsKey][2]++;

                        $this->di['redis']->setEx('analyze:21:lr', $timeout, $lr);
                        break;

                    case $this->cakeno:

                    case $this->bjkl8:// 北京快乐8
                    $analyzeLrKey = sprintf('analyze:%s:lr', $info['type']);
                        $expect = $info['info']['expect'];

                        $codeDx = $utilsObj->analyzeDx($code, 5);
                        $codeDs = $utilsObj->analyzeDs($code);
                        $codeZh = $utilsObj->analyzeZh($code, 13);

                        // 计算冷热
                        $lr = (array)$this->di['redis']->get($analyzeLrKey);
                        // 总和大小
                        $zhDxNum = $codeZh[1] == 1 ? 1 : 2;
                        $zhDxKey = '4-dx';
                        if(array_key_exists($zhDxKey, $lr) === false || $lr[$zhDxKey][1] != $zhDxNum)
                            $lr[$zhDxKey] = [4, $zhDxNum, 1];
                        else if($lr[$zhDxKey][1] == $zhDxNum)
                            $lr[$zhDxKey][2]++;

                        // 总和单双
                        $zhDsNum = $codeZh[2] == 1 ? 3 : 4;
                        $zhDsKey = '4-ds';
                        if(array_key_exists($zhDsKey, $lr) === false || $lr[$zhDsKey][1] != $zhDsNum)
                            $lr[$zhDsKey] = [4, $zhDsNum, 1];
                        else if($lr[$zhDsKey][1] == $zhDsNum)
                            $lr[$zhDsKey][2]++;

                        // 总和极大小
                        $zhJDXNum = 0;
                        if($codeZh[0] <= 5)
                            $zhJDXNum = 1;
                        else if($codeZh[0] >= 22)
                            $zhJDXNum = 2;

                        $zhJDXKey = '4-jdx';
                        if($zhJDXNum > 0 && (array_key_exists($zhJDXKey, $lr) === false || $lr[$zhJDXKey][1] != $zhJDXNum))
                            $lr[$zhJDXKey] = [4, $zhJDXNum, 1];
                        else if($zhJDXNum == 0)
                        {
                            $index = array_search($zhJDXKey, $lr);
                            if($index !== false)
                                array_splice($lr, $index, 1);
                        }

                        $this->di['redis']->setEx($analyzeLrKey, $timeout, $lr);
                        break;

                    case $this->bjpk10://北京赛车PK10
                        $expect = $info['info']['expect'];
                    case $this->mlaft://幸运飞艇
                        $codeDx = $utilsObj->analyzeDx($code, 6);
                        $codeDs = $utilsObj->analyzeDs($code);
                        $codeZh = $utilsObj->analyzeZh([$code[0], $code[1]], 12);

                        // 计算冷热
                        $analyzeLrKey = sprintf('analyze:%s:lr', $info['type']);
                        $lr = (array)$this->di['redis']->get($analyzeLrKey);
                        for($i = 0; $i < 10; $i++)
                        {

                            // 特码大小
                            $dxNum = $codeDx[$i] == 1 ? 1 : 2;
                            $dxKey = $i . '-dx';
                            if(array_key_exists($dxKey, $lr) === false || $lr[$dxKey][1] != $dxNum)
                                $lr[$dxKey] = [$i+1, $dxNum, 1];
                            else if($lr[$dxKey][1] == $dxNum)
                                $lr[$dxKey][2]++;

                            // 特码单双
                            $dsNum = $codeDs[$i] == 1 ? 3 : 4;
                            $dsKey = $i . '-ds';
                            if(array_key_exists($dsKey, $lr) === false || $lr[$dsKey][1] != $dsNum)
                                $lr[$dsKey] = [$i+1, $dsNum, 1];
                            else if($lr[$dsKey][1] == $dsNum)
                                $lr[$dsKey][2]++;

                            // 特码龙虎
                            if($i < 5 && $code[$i] != $code[9-$i])
                            {
                                $lhNum = $code[$i] > $code[9-$i] ? 5 : 6;
                                $lhKey = $i . '-lh';
                                if(array_key_exists($lhKey, $lr) === false || $lr[$lhKey][1] != $lhNum)
                                    $lr[$lhKey] = [$i+1, $lhNum, 1];
                                else if($lr[$lhKey][1] == $lhNum)
                                    $lr[$lhKey][2]++;
                            }
                        }

                        // 冠亚和大小
                        $zhDxNum = $codeZh[1] == 1 ? 1 : 2;
                        $zhDxKey = '11-dx';
                        if(array_key_exists($zhDxKey, $lr) === false || $lr[$zhDxKey][1] != $zhDxNum)
                            $lr[$zhDxKey] = [11, $zhDxNum, 1];
                        else if($lr[$zhDxKey][1] == $zhDxNum)
                            $lr[$zhDxKey][2]++;

                        // 冠亚和单双
                        $zhDsNum = $codeZh[2] == 1 ? 3 : 4;
                        $zhDsKey = '11-ds';
                        if(array_key_exists($zhDsKey, $lr) === false || $lr[$zhDsKey][1] != $zhDsNum)
                            $lr[$zhDsKey] = [11, $zhDsNum, 1];
                        else if($lr[$zhDsKey][1] == $zhDsNum)
                            $lr[$zhDsKey][2]++;

                        $this->di['redis']->setEx($analyzeLrKey, $timeout, $lr);
                        break;

                    case $this->gdklsf:
                        $codeDx = $utilsObj->analyzeDx($code, 11);
                        $codeDs = $utilsObj->analyzeDs($code);
                        $codeZh = $utilsObj->analyzeGdklsfZh($code);

                        // 计算冷热
                        $analyzeLrKey = sprintf('analyze:%s:lr', $info['type']);
                        $lr = (array)$this->di['redis']->get($analyzeLrKey);
                        for($i = 0; $i < 8; $i++)
                        {
                            // 特码大小
                            $dxNum = $codeDx[$i] == 1 ? 1 : 2;
                            $dxKey = $i . '-dx';
                            if(array_key_exists($dxKey, $lr) === false || $lr[$dxKey][1] != $dxNum)
                                $lr[$dxKey] = [$i+1, $dxNum, 1];
                            else if($lr[$dxKey][1] == $dxNum)
                                $lr[$dxKey][2]++;

                            // 特码单双
                            $dsNum = $codeDs[$i] == 1 ? 3 : 4;
                            $dsKey = $i . '-ds';
                            if(array_key_exists($dsKey, $lr) === false || $lr[$dsKey][1] != $dsNum)
                                $lr[$dsKey] = [$i+1, $dsNum, 1];
                            else if($lr[$dsKey][1] == $dsNum)
                                $lr[$dsKey][2]++;

                            // 特码尾大尾小
                            $wdsNum = $codeDs[$i] % 10 >= 5 ? 5 : 6;
                            $wdsKey = $i . '-wds';
                            if(array_key_exists($wdsKey, $lr) === false || $lr[$wdsKey][1] != $wdsNum)
                                $lr[$wdsKey] = [$i+1, $wdsNum, 1];
                            else if($lr[$wdsKey][1] == $wdsNum)
                                $lr[$wdsKey][2]++;
                        }

                        // 总和大小
                        $zhDxNum = $codeZh[1] == 1 ? 1 : 2;
                        $zhDxKey = '9-dx';
                        if(array_key_exists($zhDxKey, $lr) === false || $lr[$zhDxKey][1] != $zhDxNum)
                            $lr[$zhDxKey] = [9, $zhDxNum, 1];
                        else if($lr[$zhDxKey][1] == $zhDxNum)
                            $lr[$zhDxKey][2]++;

                        // 总和单双
                        $zhDsNum = $codeZh[2] == 1 ? 3 : 4;
                        $zhDsKey = '10-ds';
                        if(array_key_exists($zhDsKey, $lr) === false || $lr[$zhDsKey][1] != $zhDsNum)
                            $lr[$zhDsKey] = [9, $zhDsNum, 1];
                        else if($lr[$zhDsKey][1] == $zhDsNum)
                            $lr[$zhDsKey][2]++;

                        // 总和尾大尾小
                        $zhWdsNum = $codeZh[0] % 10 >= 5 ? 1 : 2;
                        $zhWdsKey = '11-wds';
                        if(array_key_exists($zhWdsKey, $lr) === false || $lr[$zhWdsKey][1] != $zhWdsNum)
                            $lr[$zhWdsKey] = [9, $zhWdsNum, 1];
                        else if($lr[$zhWdsKey][1] == $zhWdsNum)
                            $lr[$zhWdsKey][2]++;

                        $this->di['redis']->setEx($analyzeLrKey, $timeout, $lr);

                        break;
                        case $this->fjksG:
                        case $this->bjksG:
                        case $this->jsksG:
                            $analyzeZstKey = sprintf('analyze:%s:zst', $info['type']);
                            $zst = (array)$this->di['redis']->get($analyzeZstKey);
                            $code = explode(',', $info['info']['opencode']);
                            $expect =substr($info['info']['expect'], -3);
                            $zs = [intval($expect) => $code];
                            if(empty($zst)) {
                                $this->redis->setEx($analyzeZstKey, $timeout, $zs);
                            }
                            else {
                                $anew = [];
                                $new = $zst+$zs;
                                foreach ($new as $key => $value) {
                                    if(!empty($value))
                                        $anew[intval($key)] = $value;
                                }
                                $this->redis->setEx($analyzeZstKey, $timeout, $anew);
                            }
                        break;
                        case $this->fjks:
                        case $this->bjks:
                        case $this->jsks:
                            $analyzeLrKey = sprintf('analyze:%s:lr', $info['type']);
                            $codeDx = $utilsObj->analyzeDx($code, 4);
                            $codeDs = $utilsObj->analyzeDs($code);
                            $codeZh = $utilsObj->analyzeZh($code,11);
                        // 计算冷热
                        $lr = (array)$this->di['redis']->get($analyzeLrKey);
                        for($i = 0; $i < 3; $i++)
                        {
                            $dxNum = $codeDx[$i] == 1 ? 1 : 2;
                            $dxKey = $i . '-dx';
                            if(array_key_exists($dxKey, $lr) === false || $lr[$dxKey][1] != $dxNum)
                                $lr[$dxKey] = [$i+1, $dxNum, 1];
                            else if($lr[$dxKey][1] == $dxNum)
                                $lr[$dxKey][2]++;

                            $dsNum = $codeDs[$i] == 1 ? 3 : 4;
                            $dsKey = $i . '-ds';
                            if(array_key_exists($dsKey, $lr) === false || $lr[$dsKey][1] != $dsNum)
                                $lr[$dsKey] = [$i+1, $dsNum, 1];
                            else if($lr[$dsKey][1] == $dsNum)
                                $lr[$dsKey][2]++;
                        }

                        $zhDxNum = $codeZh[1] == 1 ? 1 : 2;
                        $zhDxKey = '6-dx';
                        if(array_key_exists($zhDxKey, $lr) === false || $lr[$zhDxKey][1] != $zhDxNum)
                            $lr[$zhDxKey] = [6, $zhDxNum, 1];
                        else if($lr[$zhDxKey][1] == $zhDxNum)
                            $lr[$zhDxKey][2]++;

                        $zhDsNum = $codeZh[2] == 1 ? 3 : 4;
                        $zhDsKey = '6-ds';
                        if(array_key_exists($zhDsKey, $lr) === false || $lr[$zhDsKey][1] != $zhDsNum)
                            $lr[$zhDsKey] = [6, $zhDsNum, 1];
                        else if($lr[$zhDsKey][1] == $zhDsNum)
                            $lr[$zhDsKey][2]++;

                        $this->di['redis']->setEx($analyzeLrKey, $timeout, $lr);
                        break;

                        case $this->gd11x5G:
                        case $this->sd11x5G:
                        case $this->zj11x5G:
                        case $this->ln11x5G:
                        $analyzeZstKey = sprintf('analyze:%s:zst', $info['type']);
                        $zst = (array)$this->di['redis']->get($analyzeZstKey);
                        $code = explode(',', $info['info']['opencode']);
                        $expect =substr($info['info']['expect'], -2);
                        $zs = [ intval($expect) => $code];
                        if(empty($zst)) {
                            $this->redis->setEx($analyzeZstKey, $timeout, $zs);
                        }
                        else {
                            $anew = [];
                            $new = $zst+$zs;
                            foreach ($new as $key => $value) {
                                if(!empty($value))
                                    $anew[intval($key)] = $value;
                            }
                            $this->redis->setEx($analyzeZstKey, $timeout, $anew);
                        }
                        break;
                        case $this->gd11x5:
                        case $this->sd11x5:
                        case $this->zj11x5:
                        case $this->ln11x5:
                        $analyzeLrKey = sprintf('analyze:%s:lr', $info['type']);
                            $codeDx = $utilsObj->analyze11x5Dx($code, 6);
                            $codeDs = $utilsObj->analyze11x5Ds($code);
                            $codeZh = $utilsObj->analyze11x5Zh($code);
                            // 计算冷热
                            $lr = (array)$this->di['redis']->get($analyzeLrKey);
                            for($i = 0; $i < 5; $i++)
                            {
                                switch ($codeDx[$i]) {
                                    case 0:
                                        $dxNum = 0;
                                        break;

                                    case 1:
                                        $dxNum = 1;
                                        break;

                                    case 2:
                                        $dxNum = 2;
                                        break;
                                }
                                $dxKey = $i . '-dx';
                                if(array_key_exists($dxKey, $lr) === false || $lr[$dxKey][1] != $dxNum)
                                    $lr[$dxKey] = [$i+1, $dxNum, 1];
                                else if($lr[$dxKey][1] == $dxNum)
                                    $lr[$dxKey][2]++;

                                switch ($codeDx[$i]) {
                                    case 0:
                                        $dsNum = 0;
                                        break;

                                    case 1:
                                        $dsNum = 1;
                                        break;

                                    case 2:
                                        $dsNum = 2;
                                        break;
                                }
                                $dsKey = $i . '-ds';
                                if(array_key_exists($dsKey, $lr) === false || $lr[$dsKey][1] != $dsNum)
                                    $lr[$dsKey] = [$i+1, $dsNum, 1];
                                else if($lr[$dsKey][1] == $dsNum)
                                    $lr[$dsKey][2]++;
                            }

                            switch ($codeZh[1]) {
                                    case 0:
                                        $zhDxNum = 0;
                                        break;

                                    case 1:
                                        $zhDxNum = 1;
                                        break;

                                    case 2:
                                        $zhDxNum = 2;
                                        break;
                                }
                            $zhDxKey = '6-dx';
                            if(array_key_exists($zhDxKey, $lr) === false || $lr[$zhDxKey][1] != $zhDxNum)
                                $lr[$zhDxKey] = [6, $zhDxNum, 1];
                            else if($lr[$zhDxKey][1] == $zhDxNum)
                                $lr[$zhDxKey][2]++;

                            switch ($codeZh[2]) {
                                    case 0:
                                        $zhDsNum = 0;
                                        break;

                                    case 1:
                                        $zhDsNum = 1;
                                        break;

                                    case 2:
                                        $zhDsNum = 2;
                                        break;
                                }
                            $zhDsKey = '6-ds';
                            if(array_key_exists($zhDsKey, $lr) === false || $lr[$zhDsKey][1] != $zhDsNum)
                                $lr[$zhDsKey] = [6, $zhDsNum, 1];
                            else if($lr[$zhDsKey][1] == $zhDsNum)
                                $lr[$zhDsKey][2]++;

                            $this->di['redis']->setEx($analyzeLrKey, $timeout, $lr);
                            break;
                    }

                    if($codeDx !== NULL)
                    {
                        // 计算大小走势
                        $analyzeDxKey = sprintf('analyze:%s:dx', $info['type']);
                        $dx = $this->di['redis']->get($analyzeDxKey);
                        if(array_key_exists($expect, (array)$dx) === false)
                        {
                            $dx[$expect] = $codeDx;
                            $this->di['redis']->setEx($analyzeDxKey, $timeout, $utilsObj->sortTrendByExpectAndNum($dx, 12));
                        }
                    }

                    if($codeDs !== NULL)
                    {
                        // 计算单双走势
                        $analyzeDsKey = sprintf('analyze:%s:ds', $info['type']);
                        $ds = $this->di['redis']->get($analyzeDsKey);
                        if(array_key_exists($expect, (array)$ds) === false)
                        {
                            $ds[$expect] = $codeDs;
                            $this->di['redis']->setEx($analyzeDsKey, $timeout, $utilsObj->sortTrendByExpectAndNum($ds, 12));
                        }
                    }

                    echo '[', date('Y-m-d H:i:s'), ']计算走势完成，Data:', json_encode($info), "\n";
                }
            }
        }
    }

    // 设置重庆时时彩第二天开奖期数
    public function setCqsscExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->cqssc;
        $date = date('Ymd');
        $values = [];
        $openTime = strtotime($date . '095000');
        for($i = 24; $i <= 96; $i++)
        {
            $expect = $date . sprintf('%03d', $i);
            $openTime += 600;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        for($i = 97; $i <= 120; $i++)
        {
            $expect = $date . sprintf('%03d', $i);
            $openTime += 300;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $date = date('Ymd', strtotime($date) + 86400);
        for($i = 1; $i <= 23; $i++)
        {
            $expect = $date . sprintf('%03d', $i);
            $openTime += 300;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;
        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setCqsscExpects]重庆时时彩期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setCqsscExpects]重庆时时彩期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置最近一期开奖状态，以及获取开奖结果
    public function setCqsscResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->cqssc);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setCqsscResult][cqssc]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setCqsscResult][cqssc][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->cqssc, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setCqsscResult][cqssc][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setCqsscResult][cqssc][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->cqssc, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setCqsscResult][cqssc][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setCqsscResult][cqssc][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->cqssc, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);       //

        echo '[', date('Y-m-d H:i:s'), '][setCqsscResult][cqssc][success]-', json_encode($expect) , "\n";
        return;
    }

    // 根据队列获取开奖期计算投注
    public function dealOrdersAction()
    {
        for($i = 0; $i < 60; $i++)
        {
            // 获取队列长度
            $llen = $this->di['redis']->llen('open:queue');

            if($llen > 0)
            {
                $betsResultModel = new BetsResultModel;
                $betsOrdersLogic = new BetOrdersLogic;
                $betsResultLogic = new BetsResultLogic;
                $eachNums = 50;
                for($j = 0; $j < $llen; $j++)
                {
                    $isException = 1;
                    // 获取开奖的期号
                    $expect = $this->di['redis']->rpop('open:queue');
                    if(!$expect)
                    {
                        continue;
                    }

                    // 获取开奖的状态和开奖号码
                    $info = $betsResultModel->getOpeningCodeById($expect['bres_id']);
                    if(!$info)
                    {
                        echo '[', date('Y-m-d H:i:s'), '][dealOrders][getOpeningCodeById][null]-', json_encode($expect) , "\n";
                        // 设置开奖状态为异常
                        $betsResultLogic->setBetResultStatus($expect['bres_id'], 7);
                        continue;
                    }

                    // 反json化memo数据
                    $betRes = json_decode($info['bres_memo'], true);

                    // 获取投注订单数据，计算开奖结果
                    $totalNums = $betsOrdersLogic->getWaittingOpenNumsByBetid($expect['bet_id'], $expect['bres_periods']);
                    if($totalNums <= 0)
                    {
                        // 修改该期状态为已开奖
                        if($betsResultLogic->setBetResultStatus($expect['bres_id'], $isException, time()) <= 0)
                        {
                            // 记录错误日志
                            echo '[', date('Y-m-d H:i:s'), '][dealOrders][setBetResultStatus][fail]-', json_encode($expect) , "\n";
                        }

                        echo '[', date('Y-m-d H:i:s'), '][dealOrders][getWaittingOpenNumsByBetid]result:0, ', json_encode($expect) , "\n";
                        continue;
                    }

                    $page = intval(ceil($totalNums / $eachNums));
                    for ($k = 0; $k < $page; $k++)
                    {
                        $orders = $betsOrdersLogic->getWaittingOpenOrderByBetidAndPage($expect['bet_id'], $expect['bres_periods'], 0, $eachNums);
                        $nums = count($orders);

                        if($nums <= 0)
                        {
                            continue;
                        }

                        for($l = 0; $l < $nums; $l++)
                        {
                            try
                            {
                                $betsOrdersLogic->openBets($expect['bet_id'], $expect['bres_periods'], $info['bres_result'], $betRes['detail'], $orders[$l]);
                                echo '[', date('Y-m-d H:i:s'), '][dealOrders][openBets][success]-', $orders[$l]['bo_id'], "\n";
                            }
                            catch(Exception $e)
                            {
                                $isException = 7;
                                echo '[', date('Y-m-d H:i:s'), '][dealOrders][openBets]-', json_encode($orders[$l]), ' [errMsg]-', $e->getMessage(), "\n";
                                continue;
                            }
                        }

                    }

                    // 修改该期状态为已开奖
                    if($betsResultLogic->setBetResultStatus($expect['bres_id'], $isException, $isException == 7 ? 0 : time()) <= 0)
                    {
                        // 记录错误日志
                        echo '[', date('Y-m-d H:i:s'), '][dealOrders][setBetResultStatus][fail]-', json_encode($expect) , "\n";
                        return;
                    }
                    else
                    {
                        echo '[', date('Y-m-d H:i:s'), '][dealOrders][setBetResultStatus][success]-', json_encode($expect) , "\n";

                    }

                }
            }

            sleep(1);
        }
    }

    // 设置北京PK10第二天开奖期数
    public function setBjpk10ExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->bjpk10;
        // 查询最近一期已经开奖的期号
        $betsResultModel = new BetsResultModel;
        $expectInfo = $betsResultModel->getLastExpectByBetId($betId);
        if(!$expectInfo)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjpk10Expects]北京赛车PK10期号预生成失败，找不到最近一期期号', "\n";
            return;
        }

        $expect = $expectInfo['bres_periods'];

        $values = [];
        $openTime = strtotime(date('Ymd') . '090200');
        for($i = 1; $i <= 179; $i++)
        {
            $expect++;
            $openTime += 300;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;
         if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjpk10Expects]北京赛车PK10期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjpk10Expects]北京赛车PK10期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置北京pk10最近一期开奖状态，以及获取开奖结果
    public function setBjpk10ResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->bjpk10);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjpk10Result]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjpk10Result][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->bjpk10, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setBjpk10Result][setExceptionById][failed]-', json_encode($expect) , "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setBjpk10Result][compareResult]获取开奖结果失败-', json_encode($expect) , "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->bjpk10, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjpk10Result][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjpk10Result][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->bjpk10, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);       //

        echo '[', date('Y-m-d H:i:s'), '][setBjpk10Result][success]-', json_encode($expect) , "\n";
        return;
    }

    // 设置幸运飞艇第二天开奖期数
    public function setMlaftExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->mlaft;
        $date = date('Ymd');
        $values = [];
        $openTime = strtotime($date . '130400');
        for($i = 1; $i <= 180; $i++)
        {
            $expect = $date . sprintf('%03d', $i);
            $openTime += 300;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;
         if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setMlaftExpects]幸运飞艇期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setMlaftExpects]幸运飞艇期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置幸运飞艇最近一期开奖状态，以及获取开奖结果
    public function setMlaftResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->mlaft);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setMlaftResult]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setMlaftResult][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->mlaft, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setMlaftResult][setExceptionById][failed]-', json_encode($expect) , "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setMlaftResult][compareResult]获取开奖结果失败-', json_encode($expect) , "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->mlaft, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setMlaftResult][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setMlaftResult][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->mlaft, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);       //

        echo '[', date('Y-m-d H:i:s'), '][setMlaftResult][success]-', json_encode($expect) , "\n";
        return;
    }

    // 设置广东快乐十分第二天开奖期数
    public function setGdklsfExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->gdklsf;
        $date = date('Ymd');
        $values = [];
        $openTime = strtotime($date . '090000');
        for($i = 1; $i <= 84; $i++)
        {
            $expect = $date . sprintf('%03d', $i);
            $openTime += 600;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;
         if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setGdklsfExpects]广东快乐十分期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setGdklsfExpects]广东快乐十分期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置广东快乐十分最近一期开奖状态，以及获取开奖结果
    public function setGdklsfResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->gdklsf);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setgdklsfResult]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setGdklsfResult][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->gdklsf, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setGdklsfResult][setExceptionById][failed]-', json_encode($expect) , "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setGdklsfResult][compareResult]获取开奖结果失败-', json_encode($expect) , "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->gdklsf, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setGdklsfResult][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setGdklsfResult][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->gdklsf, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);       //

        echo '[', date('Y-m-d H:i:s'), '][setGdklsfResult][success]-', json_encode($expect) , "\n";
        return;
    }

    // 设置北京快乐8期号
    public function setBjkl8ExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->bjkl8;
        // 查询最近一期已经开奖的期号
        $betsResultModel = new BetsResultModel;
        $expectInfo = $betsResultModel->getLastExpectByBetId($betId);
        if(!$expectInfo)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjkl8Expects]北京快乐8期号预生成失败，找不到最近一期期号', "\n";
            return;
        }

        $expect = $expectInfo['bres_periods'];

        $values = [];
        $openTime = strtotime(date('Ymd') . '090000');
        for($i = 1; $i <= 179; $i++)
        {
            $expect++;
            $openTime += 300;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;
         if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjkl8Expects]北京快乐8期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjkl8Expects]北京快乐8期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置北京快乐8最近一期开奖状态，以及获取开奖结果
    public function setBjkl8ResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->bjkl8);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjkl8Result]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjkl8Result][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->bjkl8, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setBjkl8Result][setExceptionById][failed]-', json_encode($expect) , "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setBjkl8Result][compareResult]获取开奖结果失败-', json_encode($expect) , "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        $res = $utilsObj->analyzeFromResByType($this->bjkl8, explode(',', $result));
        if(!$betsResultModel->setResultById($expect['bres_id'], $res['result'], json_encode($res)))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjkl8Result][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjkl8Result][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->bjkl8, 'info' => ['opencode' => $res['result'], 'expect' => $expect['bres_periods'] ]]);       //

        echo '[', date('Y-m-d H:i:s'), '][setBjkl8Result][success]-', json_encode($expect) , "\n";
        return;
    }

    // 设置加拿大快乐8期号
    public function setCakenoExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->cakeno;
        // 查询最近一期已经开奖的期号
        $betsResultModel = new BetsResultModel;
        $expectInfo = $betsResultModel->getLastExpectByBetId($betId);
        if(!$expectInfo)
        {
            echo '[', date('Y-m-d H:i:s'), '][setCakenoExpects]加拿大28期号预生成失败，找不到最近一期期号', "\n";
            return;
        }

//        $day = date("w");
//        if($day != 1){

           $file = date('Y-m-d').'.txt';
           if(file_exists($file))
           {
               return;
           }
            $expect = $expectInfo['bres_periods']+1;

            $betsObj = new Bets\Bets;
            $result = $betsObj->cakenoResult($this->cakeno, $expectInfo['bres_periods']+1, date('Y-m-d',$expectInfo['bres_open_time']));
            if(!$result)
            {
                echo '[', date('Y-m-d H:i:s'), '][setCakenoExpects]获取最新一期结果失败', "\n";
                return;
            }
            else
            {
               $file = date('Y-m-d').'.txt';
               $pf = fopen($file,"r");
               fclose($pf);
                $values = [];
                $openTime = 0;
                $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
                $bres_id = $betsResultModel->addPreRec($values) ;
                if($bres_id> 0)
                {
                    echo '[', date('Y-m-d H:i:s'), '][setCakenoExpects]插入最新开奖期成功，Data:', json_encode($values), "\n";
                }
                else
                {
                    echo '[', date('Y-m-d H:i:s'), '][setCakenoExpects]插入最新开奖期失败，Data:', json_encode($values), "\n";
                }

//                $endTime = strtotime(date('Ymd') . '190000')+86400;

                $utilsObj = new Bets\Utils;
                $res = $utilsObj->analyzeFromResByType($this->cakeno, explode(',', $result[0]));
                if(!$betsResultModel->settingResultById($bres_id, $res['result'], json_encode($res),$result[1]))
                {
                    echo '[', date('Y-m-d H:i:s'), '][setcakenoResult][settingResultById][failed]-', json_encode($expect) , "\n";
                }
                else
                {
                    $openTime = $result[1];
                }
            }

            $endTime = strtotime(date('Ymd') . '235959');
            $values = [];
            if(time() > strtotime(date('Ymd') . '191000'))
            {
                while($openTime < $endTime)
                {
                    $expect++;
                    $openTime += 210;
                  
                    $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
                }
                for($i = 0; $i < 330; $i++)
                {
                    $expect++;
                    $openTime += 210;
                
                    if ($openTime < strtotime(date("Ymd190100",strtotime("+1 day"))))
                        $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
                }
            }
            else
            {
                while($openTime < strtotime(date('Ymd') . '190000'))
                {
                    $expect++;
                    $openTime += 210;
                  
                    $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
                }
            }
//        }
//        else
//        {
//            $expect = $expectInfo['bres_periods'];
//            $beginTime = strtotime(date('Ymd') . '205700');
//            $openTime = $beginTime;
//            $endTime = strtotime(date('Ymd') . '190000')+86400;
//            $values = [];
//            while($beginTime <= $openTime  && $openTime < $endTime)
//            {
//                $expect++;
//                $openTime += 210;
//                $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
//            }
//        }

        $betsResultModel = new BetsResultModel;
        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setCakenoExpects]加拿大28期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setCakenoExpects]加拿大28期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置加拿大快乐8最近一期开奖状态，以及获取开奖结果
    public function setCakenoResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->cakeno);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setCakenoResult]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setCakenoResult][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->cakeno, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setCakenoResult][setExceptionById][failed]-', json_encode($expect) , "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setCakenoResult][compareResult]获取开奖结果失败-', json_encode($expect) , "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        $res = $utilsObj->analyzeFromResByType($this->cakeno, explode(',', $result));
        if(!$betsResultModel->setResultById($expect['bres_id'], $res['result'], json_encode($res)))
        {
            echo '[', date('Y-m-d H:i:s'), '][setCakenoResult][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setCakenoResult][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->cakeno, 'info' => ['opencode' => $res['result'], 'expect' => $expect['bres_periods'] ]]);       //

        echo '[', date('Y-m-d H:i:s'), '][setCakenoResult][success]-', json_encode($expect) , "\n";
        return;
    }

    public function mendOneAction()
    {
        set_time_limit(600);
        $betsObj = new Bets\Bets;
        $utilsObj = new Bets\Utils;
        $betsResultModel = new BetsResultModel;
   
        for($i = 2;$i<=5;$i++)
        {
            $abnormal = $betsResultModel->getAllAbnormal($i);
            if(!empty($abnormal))
            {
                foreach ($abnormal as $key=>$value)
                {
                    sleep(4);
                    //获取一场期号信息
                    if(!$betsResultModel->setAbnormalOpeningById($abnormal[$key]['bres_id']))
                    {
                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][setAbnormalOpeningByBetid][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                        continue;
                    }
                    $time = date('Y-m-d',$value['bres_open_time']);
                    if ($i == 3)
                    { 
                        if (intval(date('H',$value['bres_open_time'])) >= 0 && intval(date('H',$value['bres_open_time'])) < 5)
                            $time = date('Y-m-d', $value['bres_open_time']-43200);
                    }
                    // 调用接口获取开奖结果
                    $result = $betsObj->mendResult($i, $abnormal[$key]['bres_periods'], $time);

                    if(!$result)
                    {
                        // 设置开奖异常
                        if(!$betsResultModel->setExceptionById($abnormal[$key]['bres_id']))
                        {
                            echo '[', date('Y-m-d H:i:s'), '][setmendResult][setExceptionById][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                            continue;
                        }

                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][compareResult]获取开奖结果失败-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                        continue;
                    }

                    // 设置官方开奖结果
                    $utilsObj = new Bets\Utils;
                    $res = $utilsObj->analyzeFromResByType($i, explode(',', $result));
                    if($i == 2)
                    {
                        if(!$betsResultModel->setResultById($abnormal[$key]['bres_id'], $res['result'], json_encode($res)))
                        {
                            echo '[', date('Y-m-d H:i:s'), '][setmendResult][setResultById][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                            continue;
                        }
                        if(!$this->di['redis']->rpush('open:queue', $abnormal[$key]))
                        {
                            echo '[', date('Y-m-d H:i:s'), '][setmendResult][lpush][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                            continue;
                        }
                    }
                    else
                    {
                        if(!$betsResultModel->setResultById($abnormal[$key]['bres_id'], $result, json_encode($res)))
                        {
                            echo '[', date('Y-m-d H:i:s'), '][setmendResult][setResultById][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                            continue;
                        }
                        if(!$this->di['redis']->rpush('open:queue', $abnormal[$key]))
                        {
                            echo '[', date('Y-m-d H:i:s'), '][setmendResult][lpush][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                            continue;
                        }
                    }

                    echo '[', date('Y-m-d H:i:s'), '][setmendResult][mandOne][success]-', $i,'-',json_encode($abnormal[$key]['bres_periods']) , "\n";
                    continue;
                }
            } 
        }
    }

    public function mendAction()
    {
        set_time_limit(600);
        $betsObj = new Bets\Bets;
        $utilsObj = new Bets\Utils;
        $betsResultModel = new BetsResultModel;
        for($i = 7;$i<=13;$i++)
        {
            $abnormal = $betsResultModel->getAllAbnormal($i);
            if(!empty($abnormal))
            {
                foreach ($abnormal as $key=>$value)
                {
                    //获取一场期号信息
                    if(!$betsResultModel->setAbnormalOpeningById($abnormal[$key]['bres_id']))
                    {
                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][setAbnormalOpeningByBetid][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                        continue;
                    }
                    // 调用接口获取开奖结果
                    $result = $betsObj->mendResult($i, $abnormal[$key]['bres_periods'], date('Y-m-d',$value['bres_open_time']));

                    if(!$result)
                    {
                        // 设置开奖异常
                        if(!$betsResultModel->setExceptionById($abnormal[$key]['bres_id']))
                        {
                            echo '[', date('Y-m-d H:i:s'), '][setmendResult][setExceptionById][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                        }

                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][compareResult]获取开奖结果失败-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                        continue;
                    }

                    // 设置官方开奖结果
                    $utilsObj = new Bets\Utils;
                    $res = $utilsObj->analyzeFromResByType($i, explode(',', $result));

                    $betsResultModel->setGuanOpenedById(intval($abnormal[$key]['bet_id'])+7, $abnormal[$key]['bres_periods']);
                    $betsResultModel->setResultByIssue(intval($abnormal[$key]['bet_id'])+7, $abnormal[$key]['bres_periods'], $result, json_encode($utilsObj->analyzeFromResByType(intval($i)+7, explode(',', $result))));
                    if(!$betsResultModel->setResultById($abnormal[$key]['bres_id'], $result, json_encode($res)))
                    {
                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][setResultById][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                    }
                    if(!$this->di['redis']->rpush('open:queue', $abnormal[$key]))
                    {
                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][lpush][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                    }
                    if(!$this->di['redis']->rpush('open:queue', $betsResultModel->getGAbnormal($i+7, $abnormal[$key]['bres_periods'])))
                    {
                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][lpush][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                    }
                }
            }
        }
    }

    public function mendCqsscAction()
    {
        set_time_limit(600);
        $betsObj = new Bets\Bets;
        $utilsObj = new Bets\Utils;
        $betsResultModel = new BetsResultModel;
        $abnormal = $betsResultModel->getAllAbnormal(1);
        if(!empty($abnormal))
        {
            foreach ($abnormal as $key=>$value){
                //获取一场期号信息
                if(!$betsResultModel->setAbnormalOpeningById($abnormal[$key]['bres_id']))
                {
                    echo '[', date('Y-m-d H:i:s'), '][setmendResult][setAbnormalOpeningByBetid][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                    continue;
                }
                // 调用接口获取开奖结果
                $result = $betsObj->mendResult(1, $abnormal[$key]['bres_periods'], date('Y-m-d',$value['bres_open_time']));

                if(!$result)
                {
                    // 设置开奖异常
                    if(!$betsResultModel->setExceptionById($abnormal[$key]['bres_id']))
                    {
                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][setExceptionById][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                    }

                    echo '[', date('Y-m-d H:i:s'), '][setmendResult][compareResult]获取开奖结果失败-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                    continue;
                }

                // 设置官方开奖结果
                $utilsObj = new Bets\Utils;
                $res = $utilsObj->analyzeFromResByType(1, explode(',', $result));

                if(!$betsResultModel->setResultById($abnormal[$key]['bres_id'], $result, json_encode($res)))
                {
                    echo '[', date('Y-m-d H:i:s'), '][setmendResult][setResultById][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                }
                if(!$this->di['redis']->rpush('open:queue', $abnormal[$key]))
                {
                    echo '[', date('Y-m-d H:i:s'), '][setmendResult][lpush][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                }

            }
        }
    }

    public function mendCakeAction()
    {
        set_time_limit(600);
        $betsObj = new Bets\Bets;
        $utilsObj = new Bets\Utils;
        $betsResultModel = new BetsResultModel;
        for ($j=1; $j<=60; $j++){
            $abnormal = $betsResultModel->getAllAbnormal($this->cakeno);
            if(!empty($abnormal))
            {
                foreach ($abnormal as $key=>$value){
                    //获取一场期号信息
                    if(!$betsResultModel->setAbnormalOpeningById($abnormal[$key]['bres_id']))
                    {
                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][setAbnormalOpeningByBetid][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                        continue;
                    }
                    // 调用接口获取开奖结果
                    $result = $betsObj->mendResult($this->cakeno, $abnormal[$key]['bres_periods'], $value['bres_open_time']);

                    if(!$result)
                    {
                        // 设置开奖异常
                        if(!$betsResultModel->setExceptionById($abnormal[$key]['bres_id']))
                        {
                            echo '[', date('Y-m-d H:i:s'), '][setmendResult][setExceptionById][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                        }

                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][compareResult]获取开奖结果失败-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                        continue;
                    }

                    // 设置官方开奖结果
                    $utilsObj = new Bets\Utils;
                    $res = $utilsObj->analyzeFromResByType($this->cakeno, explode(',', $result));

                    if(!$betsResultModel->setResultById($abnormal[$key]['bres_id'], $res['result'], json_encode($res)))
                    {
                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][setResultById][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                    }
                    if(!$this->di['redis']->rpush('open:queue', $abnormal[$key]))
                    {
                        echo '[', date('Y-m-d H:i:s'), '][setmendResult][lpush][failed]-', json_encode($abnormal[$key]['bres_periods']) , "\n";
                    }
                    
                }
            }
            sleep(1);
        }
    }

    // 设置福建快3第二天开奖期数
    public function setFjksExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->fjks;
        $betGId = $this->fjksG;
        $date = date('Ymd');
        $values = [];
        $valuesG = [];
        $openTime = strtotime($date . '090100');
        for($i = 1; $i <= 78; $i++)
        {
            $expect = $date . sprintf('%03d', $i);
            $openTime += 607;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
            $valuesG[] = '(' . $betGId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;
        if($betsResultModel->addPreRec($valuesG) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksGExpects]福建快三期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksGExpects]福建快三期号预生成失败，Data:', json_encode($values), "\n";
        }
        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksExpects]福建快三期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksExpects]福建快三期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置福建快3最近一期开奖状态，以及获取开奖结果
    public function setFjksResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->fjks);
        $expectG = $betsObj->getLastExpectById($this->fjksG);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksResult][Fjks]无待开奖的数据', "\n";
            return;
        }

        if(!$expectG)
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksGResult][Fjks]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expectG['bres_id']))
         {
            echo '[', date('Y-m-d H:i:s'), '][setFjksGResult][Fjks][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksResult][Fjks][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->fjks, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setFjksResult][Fjks][setExceptionById][failed]-', json_encode($expect), "\n";
            }
            if(!$betsResultModel->setExceptionById($expectG['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setFjksGResult][Fjks][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setFjksResult][Fjks][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            echo '[', date('Y-m-d H:i:s'), '][setFjksGResult][Fjks][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expectG['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->fjksG, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksGResult][Fjks][setResultById][failed]-', json_encode($expect) , "\n";
        }
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->fjks, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksResult][Fjks][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksResult][Fjks][lpush][failed]-', json_encode($expect) , "\n";
        }

        if(!$this->di['redis']->lpush('open:queue', $expectG))
        {
            echo '[', date('Y-m-d H:i:s'), '][setFjksGResult][Fjks][lpush][failed]-', json_encode($expectG) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->fjks, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);
        $this->di['redis']->rpush('open:analyze', ['type' => $this->fjksG, 'info' => ['opencode' => $result, 'expect' => $expectG['bres_periods'] ]]);

        echo '[', date('Y-m-d H:i:s'), '][setFjksResult][Fjks][success]-', json_encode($expect) , "\n";
        echo '[', date('Y-m-d H:i:s'), '][setFjksGResult][Fjks][success]-', json_encode($expectG) , "\n";
        return;
    }

    // 设置江苏快3第二天开奖期数
    public function setJsksExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->jsks;
        $betGId = $this->jsksG;
        $date = date('Ymd');
        $values = [];
        $valuesG = [];
        $openTime = strtotime($date . '083000');
        for($i = 1; $i <= 82; $i++)
        {
            $expect = $date . sprintf('%03d', $i);
            $openTime += 600;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
            $valuesG[] = '(' . $betGId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;
        if($betsResultModel->addPreRec($valuesG) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksGExpects]江苏快三期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksGExpects]江苏快三期号预生成失败，Data:', json_encode($values), "\n";
        }
        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksExpects]江苏快三期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksExpects]江苏快三期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置江苏快3最近一期开奖状态，以及获取开奖结果
    public function setJsksResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->jsks);
        $expectG = $betsObj->getLastExpectById($this->jsksG);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksResult][Jsks]无待开奖的数据', "\n";
            return;
        }

        if(!$expectG)
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksGResult][Jsks]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expectG['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksGResult][Jsks][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksResult][Jsks][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->jsks, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setJsksResult][Jsks][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            if(!$betsResultModel->setExceptionById($expectG['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setJsksGResult][Jsks][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setJsksGResult][Jsks][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            echo '[', date('Y-m-d H:i:s'), '][setJsksResult][Jsks][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expectG['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->jsksG, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksGResult][Jsks][setResultById][failed]-', json_encode($expect) , "\n";
        }
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->jsks, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksResult][Jsks][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksResult][Jsks][lpush][failed]-', json_encode($expect) , "\n";
        }

        if(!$this->di['redis']->lpush('open:queue', $expectG))
        {
            echo '[', date('Y-m-d H:i:s'), '][setJsksGResult][Jsks][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->jsks, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);
        $this->di['redis']->rpush('open:analyze', ['type' => $this->jsksG, 'info' => ['opencode' => $result, 'expect' => $expectG['bres_periods'] ]]);

        echo '[', date('Y-m-d H:i:s'), '][setJsksResult][Jsks][success]-', json_encode($expect) , "\n";
        echo '[', date('Y-m-d H:i:s'), '][setJsksGResult][Jsks][success]-', json_encode($expect) , "\n";
        return;
    }

    // 设置北京快3第二天开奖期数
    public function setBjksExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->bjks;
        $betGId = $this->bjksG;
        // 查询最近一期已经开奖的期号
        $betsResultModel = new BetsResultModel;
        $expectInfo = $betsResultModel->getLastExpectByBetId($betId);
        $expectInfoG = $betsResultModel->getLastExpectByBetId($betGId);
        if(!$expectInfo)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksExpects]北京快三期号预生成失败，找不到最近一期期号', "\n";
            return;
        }

        $expect = $expectInfo['bres_periods'];

        $date = date('Ymd');
        $values = [];
        $valuesG = [];
        $openTime = strtotime($date . '090000');
        for($i = 1; $i <= 89; $i++)
        {
            $expect = $expect+1;
            $openTime += 600;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
            $valuesG[] = '(' . $betGId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;

        if($betsResultModel->addPreRec($valuesG) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksGExpects]北京快三期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksGExpects]北京快三期号预生成失败，Data:', json_encode($values), "\n";
        }
        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksExpects]北京快三期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksExpects]北京快三期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置北京快3最近一期开奖状态，以及获取开奖结果
    public function setBjksResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->bjks);
        $expectG = $betsObj->getLastExpectById($this->bjksG);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksResult][Bjks]无待开奖的数据', "\n";
            return;
        }

        if(!$expectG)
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksGResult][Bjks]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expectG['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksGResult][Bjks][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksResult][Bjks][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->bjks, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setBjksResult][Bjks][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            if(!$betsResultModel->setExceptionById($expectG['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setBjksGResult][Bjks][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setBjksResult][Bjks][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            echo '[', date('Y-m-d H:i:s'), '][setBjksGResult][Bjks][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expectG['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->bjksG, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksGResult][Bjks][setResultById][failed]-', json_encode($expect) , "\n";
        }
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->bjks, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksResult][Bjks][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksResult][Bjks][lpush][failed]-', json_encode($expect) , "\n";
        }

        if(!$this->di['redis']->lpush('open:queue', $expectG))
        {
            echo '[', date('Y-m-d H:i:s'), '][setBjksGResult][Bjks][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->bjks, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);
        $this->di['redis']->rpush('open:analyze', ['type' => $this->bjksG, 'info' => ['opencode' => $result, 'expect' => $expectG['bres_periods'] ]]);

        echo '[', date('Y-m-d H:i:s'), '][setBjksResult][Bjks][success]-', json_encode($expect) , "\n";
        echo '[', date('Y-m-d H:i:s'), '][setBjksGResult][Bjks][success]-', json_encode($expectG) , "\n";
        return;
    }

    // 设置辽宁11选5第二天开奖期数
    public function setLn11x5ExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->ln11x5;
        $betGId = $this->ln11x5G;
        $date = date('Ymd');
        $values = [];
        $valuesG = [];
        $openTime = strtotime($date . '084000');
        for($i = 1; $i <= 83; $i++)
        {
            $expect = $date . sprintf('%02d', $i);
            $openTime += 600;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
            $valuesG[] = '(' . $betGId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;

        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5Expects]辽宁11选5期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5Expects]辽宁11选5期号预生成失败，Data:', json_encode($values), "\n";
        }

        if($betsResultModel->addPreRec($valuesG) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5GExpects]辽宁11选5期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5GExpects]辽宁11选5期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置辽宁11选5最近一期开奖状态，以及获取开奖结果
    public function setLn11x5ResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->ln11x5);
        $expectG = $betsObj->getLastExpectById($this->ln11x5G);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5Result][Ln11x5]无待开奖的数据', "\n";
            return;
        }

        if(!$expectG)
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5GResult][Ln11x5]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expectG['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5GResult][Ln11x5][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5Result][Ln11x5][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->ln11x5, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setLn11x5Result][Ln11x5][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            if(!$betsResultModel->setExceptionById($expectG['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setLn11x5GResult][Ln11x5][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setLn11x5Result][Ln11x5][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5GResult][Ln11x5][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expectG['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->ln11x5G, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5GResult][Ln11x5][setResultById][failed]-', json_encode($expect) , "\n";
        }
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->ln11x5, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5Result][Ln11x5][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5Result][Ln11x5][lpush][failed]-', json_encode($expect) , "\n";
        }

        if(!$this->di['redis']->lpush('open:queue', $expectG))
        {
            echo '[', date('Y-m-d H:i:s'), '][setLn11x5GResult][Ln11x5][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->ln11x5, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);
        $this->di['redis']->rpush('open:analyze', ['type' => $this->ln11x5G, 'info' => ['opencode' => $result, 'expect' => $expectG['bres_periods'] ]]);

        echo '[', date('Y-m-d H:i:s'), '][setLn11x5Result][Ln11x5][success]-', json_encode($expect) , "\n";
        echo '[', date('Y-m-d H:i:s'), '][setLn11x5GResult][Ln11x5][success]-', json_encode($expect) , "\n";
        return;
    }

    // 设置广东11选5第二天开奖期数
    public function setGd11x5ExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->gd11x5;
        $betGId = $this->gd11x5G;
        $date = date('Ymd');
        $values = [];
        $valuesG = [];
        $openTime = strtotime($date . '090000');
        for($i = 1; $i <= 84; $i++)
        {
            $expect = $date . sprintf('%02d', $i);
            $openTime += 600;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
            $valuesG[] = '(' . $betGId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;

        $analyzeZstKey = sprintf('analyze:%s:zst', $betGId);
        $this->redis->setEx($analyzeZstKey,86400, '');
        if($betsResultModel->addPreRec($valuesG) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5GExpects]广东11选5期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5GExpects]广东11选5期号预生成失败，Data:', json_encode($values), "\n";
        }

        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5Expects]广东11选5期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5Expects]广东11选5期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置广东11选5最近一期开奖状态，以及获取开奖结果
    public function setGd11x5ResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->gd11x5);
        $expectG = $betsObj->getLastExpectById($this->gd11x5G);
        if(!$expectG)
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5GResult][Gd11x5]无待开奖的数据', "\n";
            return;
        }
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5Result][Gd11x5]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expectG['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5GResult][Gd11x5][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5Result][Gd11x5][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->gd11x5, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setGd11x5Result][Gd11x5][setExceptionById][failed]-', json_encode($expect), "\n";
            }
            if(!$betsResultModel->setExceptionById($expectG['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setGd11x5GResult][Gd11x5][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setGd11x5Result][Gd11x5][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5GResult][Gd11x5][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;

        if(!$betsResultModel->setResultById($expectG['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->gd11x5G, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5GResult][Gd11x5][setResultById][failed]-', json_encode($expect) , "\n";
        }

        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->gd11x5, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5Result][Gd11x5][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setGd11x5Result][Gd11x5][lpush][failed]-', json_encode($expect) , "\n";
        }

        if(!$this->di['redis']->lpush('open:queue', $expectG))
        {

            echo '[', date('Y-m-d H:i:s'), '][setGd11x5GResult][Gd11x5][lpush][failed]-', json_encode($expectG) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->gd11x5, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);
        $this->di['redis']->rpush('open:analyze', ['type' => $this->gd11x5G, 'info' => ['opencode' => $result, 'expect' => $expectG['bres_periods'] ]]);

        echo '[', date('Y-m-d H:i:s'), '][setGd11x5Result][Gd11x5][success]-', json_encode($expect) , "\n";
        echo '[', date('Y-m-d H:i:s'), '][setGd11x5GResult][Gd11x5][success]-', json_encode($expectG) , "\n";
        return;
    }

    // 设置浙江11选5第二天开奖期数
    public function setZj11x5ExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->zj11x5;
        $betGId = $this->zj11x5G;
        $date = date('Ymd');
        $values = [];
        $valuesG = [];
        $openTime = strtotime($date . '082000');
        for($i = 1; $i <= 85; $i++)
        {
            $expect = $date . sprintf('%02d', $i);
            $openTime += 600;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
            $valuesG[] = '(' . $betGId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;
        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5Expects]浙江11选5期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5Expects]浙江11选5期号预生成失败，Data:', json_encode($values), "\n";
        }

        if($betsResultModel->addPreRec($valuesG) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5GExpects]浙江11选5期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5GExpects]浙江11选5期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置浙江11选5最近一期开奖状态，以及获取开奖结果
    public function setZj11x5ResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->zj11x5);
        $expectG = $betsObj->getLastExpectById($this->zj11x5G);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5Result][Zj11x5]无待开奖的数据', "\n";
            return;
        }

        if(!$expectG)
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5GResult][Zj11x5]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expectG['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5GResult][Zj11x5][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5Result][Zj11x5][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->zj11x5, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setZj11x5Result][Zj11x5][setExceptionById][failed]-', json_encode($expect), "\n";
            }
            if(!$betsResultModel->setExceptionById($expectG['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setZj11x5GResult][Zj11x5][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setZj11x5Result][Zj11x5][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5GResult][Zj11x5][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expectG['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->zj11x5G, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5GResult][Zj11x5][setResultById][failed]-', json_encode($expect) , "\n";
        }
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->zj11x5, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5Result][Zj11x5][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5Result][Zj11x5][lpush][failed]-', json_encode($expect) , "\n";
        }

        if(!$this->di['redis']->lpush('open:queue', $expectG))
        {
            echo '[', date('Y-m-d H:i:s'), '][setZj11x5GResult][Zj11x5][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->zj11x5, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);
        $this->di['redis']->rpush('open:analyze', ['type' => $this->zj11x5G, 'info' => ['opencode' => $result, 'expect' => $expectG['bres_periods'] ]]);

        echo '[', date('Y-m-d H:i:s'), '][setZj11x5Result][Zj11x5][success]-', json_encode($expect) , "\n";
        echo '[', date('Y-m-d H:i:s'), '][setZj11x5GResult][Zj11x5][success]-', json_encode($expect) , "\n";
        return;
    }

    // 设置山东11选5第二天开奖期数
    public function setSd11x5ExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->sd11x5;
        $betGId = $this->sd11x5G;
        $date = date('Ymd');
        $values = [];
        $valuesG = [];
        $openTime = strtotime($date . '082700');
        for($i = 1; $i <= 87; $i++)
        {
            $expect = $date . sprintf('%02d', $i);
            $openTime += 600;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
            $valuesG[] = '(' . $betGId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;

        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5Expects]山东11选5期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5Expects]山东11选5期号预生成失败，Data:', json_encode($values), "\n";
        }

        if($betsResultModel->addPreRec($valuesG) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5GExpects]山东11选5期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5GExpects]山东11选5期号预生成失败，Data:', json_encode($values), "\n";
        }
    }

    // 设置山东11选5最近一期开奖状态，以及获取开奖结果
    public function setSd11x5ResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->sd11x5);
        $expectG = $betsObj->getLastExpectById($this->sd11x5G);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5Result][Sd11x5]无待开奖的数据', "\n";
            return;
        }

        if(!$expectG)
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5GResult][Sd11x5]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expectG['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5GResult][Sd11x5][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5Result][Sd11x5][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->compareResult($this->sd11x5, $expect['bres_periods']);
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setSd11x5Result][Sd11x5][setExceptionById][failed]-', json_encode($expect), "\n";
            }
            if(!$betsResultModel->setExceptionById($expectG['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setSd11x5GResult][Sd11x5][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setSd11x5Result][Sd11x5][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5GResult][Sd11x5][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expectG['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->sd11x5G, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5GResult][Sd11x5][setResultById][failed]-', json_encode($expect) , "\n";
        }
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->sd11x5, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5Result][Sd11x5][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5Result][Sd11x5][lpush][failed]-', json_encode($expect) , "\n";
        }

        if(!$this->di['redis']->lpush('open:queue', $expectG))
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5GResult][Sd11x5][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->sd11x5, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);
        $this->di['redis']->rpush('open:analyze', ['type' => $this->sd11x5G, 'info' => ['opencode' => $result, 'expect' => $expectG['bres_periods'] ]]);

        echo '[', date('Y-m-d H:i:s'), '][setSd11x5Result][Sd11x5][success]-', json_encode($expect) , "\n";
        echo '[', date('Y-m-d H:i:s'), '][setSd11x5GResult][Sd11x5][success]-', json_encode($expect) , "\n";
        return;
    }

    // 设置腾讯分分彩第二天开奖期数
    public function setTxffcExpectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->Txffc;
        $date = date('Ymd');
        $values = [];
        $openTime = strtotime($date . '000000')+86400;
        for($i = 1; $i <= 720; $i++)
        {
            $expect = date('Ymd',strtotime($date)+86400) . sprintf('%03d', $i);
            $openTime += 120;
            $values[] = '(' . $betId . ', "' . $expect . '", ' .  $_SERVER['REQUEST_TIME'] . ', ' . $openTime . ')';
        }

        $betsResultModel = new BetsResultModel;

        if($betsResultModel->addPreRec($values) > 0)
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5Expects]腾讯分分彩期号预生成成功，Data:', json_encode($values), "\n";
        }
        else
        {
            echo '[', date('Y-m-d H:i:s'), '][setSd11x5Expects]腾讯分分彩期号预生成失败，Data:', json_encode($values), "\n";
        }

    }

    // 设置腾讯分分彩最近一期开奖状态，以及获取开奖结果
    public function setTxffcResultAction()
    {
        set_time_limit(600);
        // 查找最近一期到期未开奖的期号
        $betsObj = new Bets\Bets;
        $expect = $betsObj->getLastExpectById($this->Txffc);
        if(!$expect)
        {
            echo '[', date('Y-m-d H:i:s'), '][setTxffcResult][Txffc]无待开奖的数据', "\n";
            return;
        }

        // 设置成开奖中状态
        $betsResultModel = new BetsResultModel;
        if(!$betsResultModel->setOpeningById($expect['bres_id']))
        {
            echo '[', date('Y-m-d H:i:s'), '][setTxffcResult][Txffc][setOpeningByBetid][failed]-', json_encode($expect) , "\n";
            return;
        }

        // 调用接口获取开奖结果
        $result = $betsObj->getTxffcResult();
        if(!$result)
        {
            // 设置开奖异常
            if(!$betsResultModel->setExceptionById($expect['bres_id']))
            {
                echo '[', date('Y-m-d H:i:s'), '][setTxffcResult][Txffc][setExceptionById][failed]-', json_encode($expect), "\n";
            }

            echo '[', date('Y-m-d H:i:s'), '][setTxffcResult][Txffc][compareResult]获取开奖结果失败-', json_encode($expect), "\n";
            return;
        }

        // 设置官方开奖结果
        $utilsObj = new Bets\Utils;
        if(!$betsResultModel->setResultById($expect['bres_id'], $result, json_encode($utilsObj->analyzeFromResByType($this->Txffc, explode(',', $result)))))
        {
            echo '[', date('Y-m-d H:i:s'), '][setTxffcResult][Txffc][setResultById][failed]-', json_encode($expect) , "\n";
        }

        // push到开奖的队列中
        if(!$this->di['redis']->lpush('open:queue', $expect))
        {
            echo '[', date('Y-m-d H:i:s'), '][setTxffcResult][Txffc][lpush][failed]-', json_encode($expect) , "\n";
        }

        // 设置计算走势分析
        $this->di['redis']->rpush('open:analyze', ['type' => $this->Txffc, 'info' => ['opencode' => $result, 'expect' => $expect['bres_periods'] ]]);       //

        echo '[', date('Y-m-d H:i:s'), '][setTxffcResult][Txffc][success]-', json_encode($expect) , "\n";
        return;
    }


    

}

