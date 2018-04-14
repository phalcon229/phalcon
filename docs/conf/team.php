<?php

/**
 * @api {get}/team/show 下线管理-下线总账
 * @apiGroup team
 * @apiDescription 没有传时间参数默认显示本周报表数据
 * @apiParam {String} startTime 开始时间(选填,日期格式)
 * @apiParam {String} endTime 结束时间(选填，日期格式)
 *
 * @apiSuccess {Int} ua_team_num  总人数
 * @apiSuccess {Int} ua_reg_num 注册人数
 * @apiSuccess {Int} online 在线人数
 * @apiSuccess {Float} balance 总余额
 * @apiSuccess {Float} ar_team_withdraw_money 提现
 * @apiSuccess {Float} ar_team_recharge_money 充值
 * @apiSuccess {Float} ar_team_bet_money 投注金额
 * @apiSuccess {Float} ar_my_back_money 本级佣金
 * @apiSuccess {Float} agent_back 下级佣金
 * @apiSuccess {Float} agent_earn 下级盈亏
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data" :
 *       {
 *               'ua_team_num': 1,
 *               'ua_reg_num' : 1,
 *               'online' : 3,
 *               'balance' : 50214.3240,
 *               'ar_team_withdraw_money': 0,
 *               'ar_team_recharge_money': 0.0000,
 *               'ar_team_bet_money': 0.0000,
 *               'ar_my_back_money': 0.0000,
 *               'agent_back' :  0.0000,
 *               'agent_earn':0
 *       }
 *     }
 *
 */


/**
 * @api {get}/team/table 下线报表
 * @apiGroup team
 * @apiDescription 数值都已计算号
 * @apiParam {Int} uid 没有则为自己的下线 (选填)
 * @apiParam {String} startTime 开始时间(选填，日期格式)
 * @apiParam {String} endTime 结束时间(选填，日期格式)
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data" :
 *       {
 *           'base' : //列表
 *           [
 *               {
 *                   'u_id': 21,//下级id
 *                   'u_name': 'test11',//下级用户名
 *                   'ua_type': 1,//下级类型   1-会员   3-代理
 *                   'recharge': '0.0000',//充值
 *                   'withdraw': '0.0000',//提现
 *                   'earn':0,//输赢
 *                   'bet':0,//投注额
 *                   'mybet':0,//本级投注
 *                   'myearn'：0,//本级盈亏
 *                   'myback':0,//我的佣金
 *                   'ua_reg_nums':0,//注册人数
 *                   'memo':'',//备注
 *               },
 *           ],
 *           'total':   //总计
 *               {
 *                   'myback':0,//我的佣金
 *                   'myearn':0,//本级盈亏
 *                   'mybet' : 0, //本级投注
 *                   'earn': -4,//总盈亏
 *                   'bet': 0,//投注金额
 *                   'withdraw':0,//提现总额
 *                   'recharge':0,//充值总额
 *                   'reg_nums':0//注册人数
 *               }
 *       }
 *     }
 *
 */


