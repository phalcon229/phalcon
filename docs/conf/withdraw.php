<?php

/**
 * @api {get}/withdraw/index 提现规则
 * @apiGroup withdraw
 *
 * @apiSuccess {Float} min 出款下限
 * @apiSuccess {Float} max 出款上限
 * @apiSuccess {Float} limit 每日最高提款
 * @apiSuccess {Int} spent 已达投注量
 * @apiSuccess {Int} consume 出款需达投注量
 * @apiSuccess {Int} whether 是否能提款(1-是 0-否)
 * @apiSuccess {array} bank 出款银行信息
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "data":
 *           {
 *
 *               'min':10,
 *               'max': 42
 *               'limit': '100000',
 *               'spent': 8.0000,
 *               'consume': 8.0000
 *               'whether': 1,
 *               "bank":[{"ubc_id":"9","ubc_number":"6214855920210123"}],
 *           }
 *     }
 */


/**
 * @api {post}/withdraw/apply 提现操作
 * @apiGroup withdraw
 *
 * @apiParam {Int} bank 银行卡id（必填）
 * @apiParam {Float} money 提现金额（必填）
 * @apiParam {Int} moneyPwd 资金密码（必填）
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       'msg': '密码错'
 *     }
 */




