<?php
/**
 * @api {get}/bank/list 银行列表
 * @apiGroup bank
 *
 * @apiDescription key为银行id
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data" : [
 *           {
 *               'id':0,
 *               'name':"中国农业银行"
 *           },
 *           {
 *               'id':1,
 *               'name':"中国建设银行"
 *           },
 *           {
 *               'id':1,
 *               'name':"中国银行"
 *           }
 *       ]
 *     }
 *
 */

/**
 * @api {get}/bank/show 银行卡列表
 * @apiGroup bank
 *@apiDescription 姓名和卡号要隐藏部分后返回给客户端
 * @apiSuccess {Int}  ubc_id  银行卡记录id
 * @apiSuccess {String} ubc_uname 开户人姓名
 * @apiSuccess {String} ubc_bank_name 开户行
 * @apiSuccess {String} ubc_number 银行卡号
 * @apiSuccess {String} ubc_province 开户省
 * @apiSuccess {Int} ubc_status 银行卡状态   1-正常   3-删除
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data" : [{
 *               'ubc_id': 10,
 *               'ubc_uname' : '黄*超',
 *               'ubc_bank_name' : '中国银行',
 *               'ubc_number' : '169111****4516',
 *               'ubc_province' : '河北省邯郸市',
 *               'ubc_status' : 1
 *           }]
 *     }
 *
 */

/**
 * @api {post}/bank/add 添加银行卡
 *@apiGroup bank
 *@apiDescription 添加银行卡需要先设置资金密码，如果没有资金密码，默认返回code：501，需要跳转到设置个人信息界面。。。其他错误返回500
 *@apiParam {String} name 真实姓名（必填）
 *@apiParam {Int} phone 手机号码（必填）
 *@apiParam {Int} bankId 所属银行（必填）
 *@apiParam {Int} number 银行账号（必填）
 *@apiParam {String} province 开户行省份（必填）
 *@apiParam {String} city 开户行城市（必填）
 *@apiParam {String} uname 开户行 (选填)
 *@apiParam {String} pwd 资金密码（必填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "添加失败"
 *     }
 */

/**
 * @api {post}/bank/del 删除银行卡
 *@apiGroup bank
 *
 *@apiParam {Int} ubc_id 银行卡记录id（必填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "删除失败"
 *     }
 */
