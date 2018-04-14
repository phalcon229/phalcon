<?php
/**
 * @api {get}/help/index 新手帮助
 * @apiGroup help
 *
 * @apiSuccess {String} problem 常见问题
 * @apiSuccess {string} recharge 存款帮助
 * @apiSuccess {string} withdraw 取款帮助
 * @apiSuccess {array} game 玩法说明
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data": {
 *           'problem' : "",
 *           'recharge' : "",
 *           'withdraw' : "",
 *           'game' : [
 *               {
 *                   'id':1,
 *                   'name':'重庆时时彩',
 *                   'rule' : ''
 *               },
 *               {
 *                   'id':2,
 *                   'name':'11选5',
 *                   'rule' : '',
 *               },
 *           ]
 *       }
 *     }
 *
 */
