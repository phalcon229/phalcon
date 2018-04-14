<?php
/**
 * @api {post}/user/setpwd 修改登录密码
 * @apiGroup user
 * @apiParam {String} old_pwd 旧密码（必填）
 * @apiParam {String} new_pwd 新密码（必填）
 * @apiParam {String} confirm 重复新密码（必填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "修改失败"
 *     }
 */



/**
 * @api {get} /user/money 用户余额
 * @apiGroup user
 *@apiDescription 刷用户是否被踢，同时还是要返回用户当前金额
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *       "data" : 100//账户余额
 *     }
 */


/**
 * @api {post}/user/edit 备注信息
 * @apiGroup user
 * @apiParam {String} info 备注信息（必填）
 * @apiParam {Int} uId 用户id（必填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "备注失败"
 *     }
 */


/**
 * @api {get}/user/list 下线列表
 * @apiGroup user
 *
 * @apiParam {String} uId 用户id（选填,没有默认当前用户）
 * @apiParam {String} username 用户名（选填）
 * @apiParam {String} starttime 开始时间（选填，日期格式）
 * @apiParam {String} endtime 结束时间（选填，日期格式）
 *  @apiParam {Int} page 页码（选填）
 *   @apiParam {Int} nums 每页显示条数（选填）
 *
 * @apiSuccess {Int} u_id 用户id
 * @apiSuccess {String} u_name 用户名
 * @apiSuccess {Int} ua_type 用户类型 1-会员 3-代理
 * @apiSuccess {String} ua_rate 返点率
 * @apiSuccess {Int} ua_status 状态 1-正常 3-关闭
 * @apiSuccess {String} w_money 余额
 * @apiSuccess {String} ua_memo 备注
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data":
 *               [
 *                   {
 *                       'u_id': '32',
 *                       'u_name': 'test',
 *                       'ua_type':'3',
 *                       'ua_rate': '5',
 *                       'ua_status' : '1' ,
 *                       'w_money' : '50125.3312',
 *                       'ua_memo': '张三'
 *                   }
 *               ]
 *     }
 *
 */


/**
 * @api {get}/user/rateDetail 下级返点详情
 * @apiGroup user
 *
 * @apiParam {Int} uId  代理id（必填）
 *
 * @apiSuccess {Int} u_id 用户id
 * @apiSuccess {Int} u_name 开户账号
 * @apiSuccess {Int} ua_rate 回水等级
 * @apiSuccess {Int} max_rate 最高回水为用户自身的回水等级
 * @apiSuccess {Int} rateMoney 当前奖金
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       'data':
 *           {
 *               'u_id':1,
 *               'u_name' : 'test',
 *               'ua_rate' : 1.39,
 *               'max_rate' :4,
 *               'rate_money' : 1987
 *           }
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "详情不存在"
 *     }
 */

/**
 * @api {post}/user/setRate 返点设置
 * @apiGroup user
 *
 * @apiParam {Int} uId   代理id（必填）
 * @apiParam {Float} rate  返点率（必填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "设置失败"
 *     }
 */

/**
 * @api {post}/user/base 个人信息
 * @apiGroup user
 *
 * @apiDescription  首次设置默认没有个人信息，【get】可获取用户已设置的个人信息，【post】提交个人信息
 * @apiParam  {String}  pwd  资金密码（首次设置必填，已设置资金密码则选填）
 * @apiParam  {String}  confirm  重复资金密码（首次设置必填）
 * @apiParam  {Int}  mobile  手机号（必填）
 * @apiParam  {String}  wechat  微信号（首次设置必填，其他选填）
 * @apiParam  {String}  captcha  验证码（必填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data":{
 *           'pwd': 0,//0-未设置, 1-已设置
 *           'mobile': '188****666',
 *           'wechat': 'test'
 *       }
 *     }
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "设置失败"
 *     }
 */

/**
 * @api {post}/user/setInfo 修改个人资料
 * @apiGroup user
 *
 * @apiParam {Int} type 类型(2-重置资金密码   4-解绑旧手机  5-修改微信号)
 * @apiParam  {Int}  mobi  手机号（type为4更换手机必填）
 * @apiParam  {Int}  mobicap  手机验证码（type为4更换手机必填）
 * @apiParam  {String}  pwd  资金密码（type为2时用）
 * @apiParam  {String}  confirm  确认密码（type为2时用）
 * @apiParam  {String}  wechat  微信号（type为5时用）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "设置失败"
 *     }
 */

/**
 * @api {post}/user/checkcap 校验短信验证码
 * @apiGroup user
 *
 * @apiParam {Int} type 类型(2-重置资金密码   4-解绑旧手机  5-修改微信号)
 * @apiParam  {String}  msgcode  短信验证码
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "验证失败"
 *     }
 */