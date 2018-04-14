<?php

/**
 * @api {get}/agent/getrate 获取当前用户返点率
 * @apiGroup agent
 * @apiDescription 计算返点赔率 bonus-(bonus*maxRate/100)
 * @apiSuccess {Int} rate 用户返点率
 * @apiSuccess {Int} bonus 系统默认赔率值
 * @apiSuccess {Int} maxRate 最大返点率
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data":{
 *           'rate':3,//用户返点率
 *           'bonus':1.985,//系统赔率
 *           'maxRate':3 //最大返点率
 *       }
 *     }
 */

/**
 * @api {post}/agent/add  手动注册
 * @apiGroup agent
 * @apiDescription 当前赔率浮动计算公式 newRateMoney = rateMoney + bonus*rate/100
 *
 * @apiParam {int} type 注册类型 1-会员 3-代理 （必填）
 * @apiParam {decimal} rate 返点率（必填）
 * @apiParam {varchar} username 用户名（必填）
 * @apiParam {varchar} pwd 密码（必填）
 * @apiParam {varchar} confirm 确认密码（必填）
 *

 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *		 "msg":"注册失败"
 *     }
 */

/**
 * @api {get}/agent/link 推广链接列表
 * @apiGroup agent
 *
 *@apiParam  {Int} page 页码
 *@apiParam  {Int} nums 显示条数
 *
 * @apiSuccess {Int} ur_id 推广id
 * @apiSuccess {String} url 推广链接,前段根据链接生成二维码
 * @apiSuccess {String} ur_created_time 生成时间（时间戳）
 * @apiSuccess {Float} ur_fandian 返点率
 * @apiSuccess {Int} ur_reg_nums 注册人数
 * @apiSuccess {Int} ur_visitor_nums 访问人数
 * @apiSuccess {Int} ur_type 开户类型 1-普通用户 3-代理
 * @apiSuccessExample {json} Success-Response:
 *     {
 *         "code":200,
 *         "data":[
 *                     {
 *                         "ur_id":"107",
 *                         "url":"http://le.admin.com/auth/reg?c=7dc7e7fb11785844",
 *                         "ur_type":"1",
 *                         "ur_created_time":"1511222400",
 *                         "ur_fandian":"0.9000",
 *                         "ur_visitor_nums":"0",
 *                         "ur_reg_nums":"0",
 *                      },
 *                      {
 *                         "ur_id":"106",
 *                         "url":"http://le.admin.com/auth/reg?c=553d0860fd529b0d",
 *                         "ur_type":"3",
 *                         "ur_created_time":"2017-09-16 18:48:18",
 *                         "ur_fandian":"0.8000",
 *                         "ur_visitor_nums":"0",
 *                         "ur_reg_nums":"0"
 *                      }
 *
 *          ]
 *       }
 */

/**
 * @api {post}/agent/linkadd 生成推广链接
 * @apiGroup agent
 * @apiParam {Float} rate 返点率
 * @apiParam {Int} type 用户类型  1-会员  3-代理
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg":"生成失败"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg":"请选择用户类型"
 *     }
 */


/**
 * @api {post}/agent/edit 编辑推广链接
 * @apiGroup agent
 *
 * @apiParam {int} id 链接id(必填)
 * @apiParam {demical} rate 用户类型(必填,可为0)
 * @apiParam {int} type 用户类型(必填)
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg":"更新出错"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg":"推广链接不存在"
 *     }
 */
