<?php
/**
 * @api {get}/activity/index 活动列表
 * @apiGroup activity
 *
 * @apiSuccess {Int} pa_id 活动id
 * @apiSuccess {String} pa_title 活动标题
 * @apiSuccess {String} pa_img 活动图片
 * @apiSuccess {Int} if_end 是否结束状态 0-未开始 1-进行中 2-已结束
 * @apiSuccessExample {json} Success-Response:
 *
 *      {
 *          "code":200,
 *          "data":{
 *              {
 *                  "pa_id":"10",
 *                  "pa_img":"http://www.baidu.com/1.png",
 *                  "pa_title":"\u6d4b\u8bd5\u6ce8\u518c\u9001",
 *                  "if_end":0
 *              },
 *              {
 *                  "pa_id":"11",
 *                  "pa_img":"http://www.baidu.com/2.png",
 *                  "pa_title":"\u6d4b\u8bd5\u6ce8\u518c\u9001",
 *                  "if_end":1
 *              },
 *          }
 *      }
 */

/**
 * @api {get} /activity/detail 获取活动详情
 * @apiGroup activity
 *
 * @apiParam {Int} paId 活动ID（必填）
 *
 * @apiSuccess {Int} pa_id 活动id
 * @apiSuccess {String} pa_title 活动标题
 * @apiSuccess {String} pa_content 活动内容
 * @apiSuccess {Int} pa_starttime 开始时间（时间戳）
 * @apiSuccess {Int} pa_endtime 结束时间（时间戳）
 * @apiSuccess {Int} if_end 是否结束状态 0-未开始 1-进行中 2-已结束
 *
 * @apiSuccessExample {json} Success-Response:
 *      {
 *          "code":200,
 *          "data":
 *              {
 *                  "pa_id":"10",
 *                  "pa_title":"\u6d4b\u8bd5\u6ce8\u518c\u9001",
 *                  "pa_content":"\u6d4b\u8bd5\u5145\u503c\u9001\u5185\u5bb9",
 *                  "pa_starttime":"1511222400",
 *                  "pa_endtime":"1511222400",
 *                  "if_end": 0
 *              }
 *          }
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg": "活动不存在"
 *     }
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg": "参数错误"
 *     }
 */


