<?php

/**
 * @api {get}/result/index 获取各彩种开奖信息
 * @apiGroup result
 *
 * @apiSuccess {Int} bres_id 结果id
 * @apiSuccess {Int} bet_id 彩种id
 * @apiSuccess {Int} bres_periods 期号
 * @apiSuccess {array} bres_result 开奖结果
 * @apiSuccessExample {json} Success-Response:
 *      {
 *          "code":200,
 *          "data":
 *               [
 *                   {
 *                       "bres_id":"78235",
 *                       "bet_id":"1",
 *                       "bres_periods":"20170922023",
 *                       "bres_result":["9","6","1","5","2"],
 *                   },
 *                   {
 *                       "bres_id":"79084",
 *                       "bet_id":"2",
 *                       "bres_periods":"846583",
 *                       "bres_result":["5","0","8"],
 *                   },
 *               ]
 *        }
 *
 */

/**
 * @api {get}/result/detail 彩种开奖结果列表
 *@apiGroup result
 *@apiParam {Int} nums 每页加载条数
 *@apiParam {Int} page 加载页码
 *@apiParam {Int} bet_id 彩种id(必填)
 *
 * @apiSuccess {Int} bres_id
 * @apiSuccess {Int} bres_periods 期号
 * @apiSuccess {array} bres_result 开奖结果
 * @apiSuccess {Int} bres_plat_open_time 开奖时间
 * @apiSuccess {String} title 玩法总和名称
 * @apiSuccess {int} zh 总和
 * @apiSuccess {int} ds 单双   1-双   2-单
 * @apiSuccess {int} dx 大小   1-大   2-小   3-和
 * @apiSuccess {array} lh 龙虎
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data":
 *       [
 *           {
 *             'bres_id': 2,
 *             'bres_periods': '10002',
 *             'bres_plat_open_time' : '1656122468',
 *             'bres_result': [4,1,3,6,7],
 *             'title':'总和',
 *             'zh': 23,
 *             'dx':2,
 *             'ds':2,
 *             'lh': ['虎','虎','龙','龙']
 *           },
 *           {
 *             'bres_id': 1,
 *             'bres_periods': '10001',
 *             'bres_plat_open_time' : '1656122468',
 *             'bres_result': [1,5,3,6,2],
 *             'title':'总和',
 *             'zh': 17,
 *             'dx':1,
 *             'ds':2,
 *             'lh': ['龙','虎','龙','龙']
 *           }
 *       ]
 *     }
 *     * @apiErrorExample {json} Error-Response:
 *     {
 *        "code": "500",
 *        "msg" : "彩种不存在"
 *     }
 */
