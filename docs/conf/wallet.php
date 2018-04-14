<?php

/**
 * @api {get}/wallet/index 账变记录
 * @apiGroup wallet
 *
 * @apiParam {Int} type 账变类型  1-充值 3-开奖结算 5-提现 7-投注,9-系统赠送 11-返点 13-系统回水 15-退款 0-全部（选填）
 * @apiParam {Int} page 页码（选填）
 * @apiParam {Int} nums 显示条数（选填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data":
 *       {
 *           'count' : '100',//总计笔数
 *           'total' : '236.321',//总计交易额度
 *           'subInfo' ://账变记录列表
 *               [{
 *                   'uwr_created_time' : '1486952578',//时间
 *                   'uwr_type' : 1,//账变类型
 *                   'uwr_money' ; '-2',//交易额
 *                   'uwr_memo': '注册活动赠送65535',//备注
 *               },
 *           ],
 *        }
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "类型错误"
 *     }
 */


/**
 * @api {get}/wallet/show 下线帐变记录
 * @apiGroup wallet
 *
 * @apiParam {String}  uId  用户Id（选填）
 * @apiParam {String}  username  用户名（选填）
 * @apiParam {Int} next 是否包括下级   0-否   1-是（选填，必须填写用户名才能勾选）
 * @apiParam {Int} type 账变类型  1-充值 3-开奖结算 5-提现 7-投注,9-系统赠送 11-返点 13-系统回水 15-退款 0-全部（选填）
 * @apiParam {String} startDay 开始时间（选填，日期格式）
 * @apiParam {String} endDay 结束时间（选填，日期格式）
 * @apiParam {Int} page 页码（选填）
 * @apiParam {Int} nums 显示条数（选填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data":
 *       {
 *           'count' : '59',//总计交易笔数
 *           'income' : 22111.00,//总收入
 *           'expend' : -11111.00,//总支出
 *           'lists' ://账变记录列表
 *               [{
 *                   'u_name' : 'winson',//用户名
 *                   'uwr_created_time' : '1486952578',//时间
 *                   'uwr_type' : 1,//账变类型
 *                   'uwr_money' ; '-2',//交易额
 *                   'uwr_memo': '注册活动赠送65535',//备注
 *               },
 *           ],
 *        }
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "类型错误"
 *     }
 */
