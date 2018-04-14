<?php

/**
 * @api {post} /auth/login 用户登录
 * @apiGroup auth
 *
 *
 * @apiParam {String} username 用户名
 * @apiParam {String} pwd 密码（当密码为空时从）
 * @apiParam {Int} rem 是否记住密码  0-否  1-是
 *
 * @apiErrorExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       'data':{
 *           'url': '/'
 *       }
 *     }
 *
* @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "密码错误"
 *     }
 */

/**
 * @api {get} /auth/logout 退出登录
 * @apiGroup auth
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       'data':{
 *           'url':'/auth/login'
 *       }
 *     }
 */



/**
 * @api {get} /auth/captcha 图形验证码
 * @apiGroup auth
 *
 * @apiDescription 直接返回图片，同时写入session，使用方法，参考调用方法<img src="/auth/captcha"/ onclick="this.src='/auth/captcha?'+Math.random();">  图形验证码图片大小可以接口设置。。。到时候沟通
 *
 *
 */

/**
 * @api {get} /auth/mobiCap 手机验证码
 * @apiGroup auth
 *
 * @apiDescription  type类型为1,3,6的时候需要使用图形验证码
 *
 * @apiParam  {Int} type 使用类型  1-重置登录密码 2-重置资金密码 3-注册验证码 4-解绑旧手机 5-修改微信号 6-绑定新手机（必填）
 * @apiParam  {Int}  mobi 手机号（1,3,6需要填写手机号）
 * @apiParam {String}  imgcap 图形验证码（1,3,6需要填写图形验证码）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiSuccessExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       'msg': "请输入图形验证码/请输入正确的手机号/"
 *     }
 * @apiSuccessExample {json} Error-Response:
 *     {
 *       "code": "501",
 *       'msg': "图片验证码输入错误/验证码发送失败"
 *     }
 *
 * @apiSuccessExample {json} Error-Response:
 *     {
 *       "code": "503",
 *       'msg': "0000072165" //距离下一条短信发送时间
 *     }
 */


/**
 * @api {post} /auth/wxLogin 微信登录
 * @apiGroup auth
 *
 *
 * @apiParam {String} accessToken accessToken
 * @apiParam {String} openId openId
 *
 * @apiErrorExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       'data':{
 *           'url': '/'
 *       }
 *     }
 *
* @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "密码错误"
 *     }
 */