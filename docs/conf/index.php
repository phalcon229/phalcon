<?php
/**
 * @api {get}/index/index 所有彩种信息
 * @apiGroup index
 *
 * @apiSuccess {int} bet_id 彩种id
 * @apiSuccess {string} bet_name 彩种名
 * @apiSuccess {string} img 彩种图标
 * @apiSuccess {Int} bet_play_type 彩种玩法(1-信用玩法 3-官方玩法)
 * @apiSuccess {Int} type 彩种结果显示（1-圆形 2-方形）
 * @apiSuccess {Int} sel 用户选择，是否显示   1-是 0-否
 * @apiSuccess {string} url 彩种下注地址
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data": [
 *           {
 *               'bet_id': 5,
 *               'bet_name' : '北京28',
 *               'img' : "img/11xuan5-liaoning.png",
 *               'bet_play_type' : 1,
 *               'type' : 1,//圆形
 *               'sel' : 1,//用户选择
 *               'url' : 'http://dazhongcai.vip/game/info/1',  //url地址
 *           },
 *           {
 *               'bet_id': 6,
 *               'bet_name' : '加拿大28',
 *               'img' : "img/cakeno.png",
 *               'bet_play_type' : 1,
 *               'type' : 2,//方形,
 *               'sel' : 0,
 *               'url' : 'http://dazhongcai.vip/game/info/6',
 *           }
 *
 *       ]
 *     }
 *
 */


/**
 * @api {post} /index/setBet 设置彩种
 * @apiGroup index
 *@apiDescription 默认信用、官方玩法至少各保留3个彩种不可删除
 * @apiParam {Int} betId 彩种ID （必填）
 * @apiParam {Int} type 类型（1为添加，3为删除）（必填）
 *
 * @apiSuccessExample {json} Success-Response:
 *      {
 *       "code": "200"
 *      }
* @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       'msg' : '操作失败'
 *     }
 */


/**
 * @api {get} /index/banner 首页banner/公告
 * @apiGroup index
 *
 * @apiSuccess {Int} ib_id
 * @apiSuccess {String} ib_url url
 * @apiSuccess {String} ib_img img
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *       "data":
 *       [
 *           {
 *               'ib_id' : 1,
 *               'ib_url':'http://www.xxx.com/xxx.com',
 *               'ib_img':'http://www.xxx.com/xxx.jpg',
 *           }
 *       ]
 *     }
 */


/**
 * @api {get} /index/notice 通知滚动条
 * @apiGroup index
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *       "data":
 *             [
 *                 {'n_content':'诚邀实力代理合作'}, //通知内容
 *                 {'n_content':'注册送好礼，XXXXXXXX'},
 *             ]
 *     }
 */


/**
 * @api {get} /index/service 联系客服
 * @apiGroup index
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *       "data":{
 *           'url':'',//网络地址
 *       }
 *     }
 */

/**
 * @api {get} /index/opentime 各彩种开奖时间
 * @apiGroup index
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *       "data":{
 *           "interval":30,   //截止时间30s
 *           "time":1514310140,    //服务器当前时间
 *           "list":[
 *              {
 *                   "bet_id":1,   //彩种id
 *                   "bres_open_time":1514311140     //开奖时间
 *              },
 *              {
 *                   "bet_id":2,
 *                   "bres_open_time":1514311140
 *              },
 *              {
 *                   "bet_id":5,
 *                   "bres_open_time":1514312040
 *              },
 *              {
 *                   "bet_id":34,
 *                   "bres_open_time":0   //未获取到最新一期开奖时间，设置每隔几分钟请求刷新
 *              },
 *           ]
 *     }
 */