<?php
/**
 * @api {get} /recharge/index 充值方式
 * @apiGroup recharge
 * @apiSuccess {Int} pay_type 充值方式
 * @apiSuccess {String} type_name 充值方式名称
 * @apiSuccessExample {json} Success-Response:
 *     {
 *         "code" : 200,
 *         "data" : {
 *             'qrcode':[{    //付款方式
 *                     "pay_type" : 1,
 *                     "type_name" : "快捷入款",
 *                     'logo': 'fs',
 *                     'pcc_min': 100.00,
 *                     'pcc_max':10000.00
 *                     'chanel':[{
 *                         'pcc_id':1,
 *                         'pcc_name' : 'AustPay',
 *                         },
 *                         {
 *                         'pcc_id':2,
 *                         'pcc_name' : 'Aust',
 *                         }
 *                      ]
 *                  },
 *                  {
 *                      "pay_type" :3,
 *                      "type_name" : "微信入账",
 *                      'logo': 'wxscan',
 *                      'pcc_min': 100.00,
 *                      'pcc_max':10000.00
 *                      'chanel':[{
 *                         'pcc_id':1,
 *                         'pcc_name' : 'AustPay',
 *                         },
 *                         {
 *                         'pcc_id':2,
 *                         'pcc_name' : 'Aust',
 *                         }
 *                      ]
 *                   },
 *                   {
 *                       "pay_type" :5,
 *                       "type_name" : "QQ钱包",
 *                       'logo': 'QQ',
 *                       'pcc_min': 100.00,
 *                       'pcc_max':10000.00
 *                       'chanel':[{
 *                         'pcc_id':1,
 *                         'pcc_name' : 'AustPay',
 *                         }
 *                      ]
 *                   },
 *                   {
 *                       "pay_type" :7,
 *                       "type_name" : "支付宝",
 *                       'pcc_min': 100.00,
 *                       'pcc_max':10000.00
 *                       'chanel':[{
 *                         'pcc_id':1,
 *                         'pcc_name' : 'AustPay',
 *                         }
 *                      ]
 *                   },
 *                   ],
 *             "bank":{
 *                    'min': 1,
 *                    'max':1000,
 *                    'list': {
 *                        '中国农业银行',
 *                        '中国银行'
 *                    }
 *             }
 *     }
 */


/**
 * @api {post} /recharge/create 创建充值订单
 * @apiGroup recharge
 *
 * @apiDescription 快捷支付扫码则返回付款二维码地址
 * @apiParam {Int} channel 支付渠道id  3-微信入款   5-QQ钱包   7-支付宝  9-支付宝收款码   11-微信收款码   13-银联扫码   15-银行卡入款
 * @apiParam {String} account 账号 (个别支付渠道必填)
 * @apiParam {Float} amount 实际充值金额（必填）
 * @apiParam {Int} aId 充值活动id(选填)
 * @apiParam {Int} bank 银行id(银行入款时必填)
 * @apiParam {String} name 银行账户名(银行入款时必填)
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       'data':
 *           {
 *               'url': 'http://zf.dyyjk.com/api3.php?merchid=13',
 *               'qrcode' : '',//base64位二维码
 *               'bank' : {
 *                   'name':'',//银行户名
 *                   'account': '',//银行账号
 *                   'bankId':1,
 *               }
 *           }
 *     }
* @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "500",
 *       "msg" : "类型错误"
 *     }
 */

