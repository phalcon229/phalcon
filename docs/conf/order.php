<?php
/**
 * @api {get}/order/report 个人报表
 * @apiGroup order
 * @apiParam {Int} startTime 开始时间(选填)
 * @apiParam {Int} endTime 结束时间(选填)
 * @apiParam {Int} bet_id 彩种id(选填,为0时代表全部)
 *
 * @apiSuccess {String} date 日期(日期格式)
 * @apiSuccess {Int} num 彩种有效笔数
 * @apiSuccess {Float} money 投注金额
 * @apiSuccess {Float} win 输赢金额
 * @apiSuccess {Float} water 回水金额
 * @apiSuccess {Float} earn 实际输赢金额
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data":
 *          'lists': [
 *              {
 *                  'date': '2017-11-01',//输出格式为日期格式，非时间戳
 *                  'num': 2,
 *                  'money': 4,
 *                  'win': -4,
 *                  'water': '0.044',
 *                  'earn' : -3.956
 *               },
 *           ],
 *           'total':
 *               {
 *                   'num': 2,
 *                   'money': 4,
 *                   'win': -4,
 *                   'water': '0.044',
 *                   'earn' : -3.956
 *               }
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "彩种id不存在"
 *     }
 */

/**
 * @api {get}/order/reportdetail 日报表详情（订单/追号查询）
 * @apiGroup order
 * @apiParam {String} startTime 开始时间（选填,日期格式2017-12-01）
 * @apiParam {String} endTime 结束时间（选填，日期格式2017-12-01）
 * @apiParam {Int} bet_id 彩种id  0为全部（选填）
 * @apiParam {Int} type  类型    1-订单查询   3-追号查询   5-可撤销订单列表
 * @apiParam {Int} page 页码（选填）
 * @apiParam {Int} nums 记录条数（选填）
 *
 * @apiSuccess {Int} bo_id  订单id
 * @apiSuccess {String} bet_name 彩种名称
 * @apiSuccess {String} bo_played_name 玩法
 * @apiSuccess {Int} bo_issue 期号
 * @apiSuccess {Int} bo_created_time 下注时间
 * @apiSuccess {Int} bo_draw_result 中奖状态   1-中奖 3-未中奖 5-未开奖
 * @apiSuccess {Int} bo_money 投注金额
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data":
 *       [
 *           {
 *             'bo_id': 5,
 *             'bet_name': '北京赛车PK10',
 *             'bo_issue': '629675'
 *             'bo_created_time': '1509331475',
 *             'bo_draw_result': 5,
 *             'bo_money': 2,
 *             'bo_played_name': '两面(冠军:龙)'
 *           }
 *       ]
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "类型错误"
 *     }
 */

/**
 * @api {get} /order/detail 订单详情
 * @apiGroup order
 * @apiDescription 注数默认为1，实际金额等于中奖金额+返水金额-投注金额，投注号码为{投注玩法(球/赔率)
 *
 * @apiParam {Ind} boId  订单id（必填）
 *
 * @apiSuccess {Int} bet_name 彩种
 * @apiSuccess {Int} bo_sn 交易编号
 * @apiSuccess {String} bo_played_name 玩法
 * @apiSuccess {Int} bo_created_time 下注时间
 * @apiSuccess {Int} bo_money 投注金额
 * @apiSuccess {Int} bo_issue 期号
 * @apiSuccess {String} bres_result 开奖号码
 * @apiSuccess {Float} bo_bonus 中奖金额
 * @apiSuccess {Float} bo_back 返水点数
 * @apiSuccess {Float} bo_back_money 返水金额
 * @apiSuccess {Int} bo_draw_result 中奖状态   1-中奖  3-未中奖   5-未开奖
 * @apiSuccess {Int} act_money 实际金额
 * @apiSuccess {String} content 投注号码
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       'data':
 *           {
 *               'bet_name': '北京赛车PK10',
 *               'bo_sn': '1707192249284207',
 *               'bo_played_name': '两面',
 *               'bo_created_time': '1509331475',
 *               'bo_money': 2,
 *               'bo_issue': '629675',
 *               'bres_result': '2,10,5,6,7,3,8,4,9,1',
 *               'bo_bonus': 0.0000,
 *               'bo_back': 0.0110,
 *               'bo_back_money': 0.0220,
 *               'bo_draw_result':  1,
 *               'act_money'：2.0,
 *               'content':'任选一(3/2.0000)'
 *           }
 *     }
* @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "订单不存在"
 *     }
 */


/**
 * @api {post} /order/cancelorder 订单撤销操作
 * @apiGroup order
 *
 *  @apiParam {Int} boId 订单id（必填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "订单不存在"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "撤销失败"
 *     }
 */


