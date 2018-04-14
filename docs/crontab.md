# 定时任务
* * * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open dealOrders >> /data/code/lottery/app/logs/dealOrders.log
* * * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open agentEarn >> /data/code/lottery/app/logs/agentEarn.log
* * * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open countTrend >> /data/code/lottery/app/logs/countTrend.log

# 重庆时时彩
01 03 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setCqsscExpects >> /data/code/lottery/app/logs/setCqsscExpects.log
* 09,10,11,12,13,14,15,16,17,18,19,20,21,22,23,00,01,02 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setCqsscResult >> /data/code/lottery/app/logs/setCqsscResult.log

# 北京pk10
10 00 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setBjpk10Expects >> /data/code/lottery/app/logs/setBjpk10Expects.log
* 09,10,11,12,13,14,15,16,17,18,19,20,21,22,23 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setBjpk10Result >> /data/code/lottery/app/logs/setBjpk10Result.log

# 幸运飞艇
01 05 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setMlaftExpects >> /data/code/lottery/app/logs/setMlaftExpects.log
* 00,01,02,03,04,13,14,15,16,17,18,19,20,21,22,23 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setMlaftResult >> /data/code/lottery/app/logs/setMlaftResult.log

# 广东快乐10分
01 00 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setGdklsfExpects >> /data/code/lottery/app/logs/setGdklsfExpects.log
* 09,10,11,12,13,14,15,16,17,18,19,20,21,22,23 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setGdklsfResult >> /data/code/lottery/app/logs/setGdklsfResult.log

# pc蛋蛋(北京快乐8)
10 00 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setBjkl8Expects >> /data/code/lottery/app/logs/setBjkl8Expects.log
* 09,10,11,12,13,14,15,16,17,18,19,20,21,22,23 * * * /usr/bin/php /data/code/lottery/app/tasks/cli.php Open setBjkl8Result >> /data/code/lottery/app/logs/setBjkl8Result.log
