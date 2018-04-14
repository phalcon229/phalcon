define({ "api": [
  {
    "type": "get",
    "url": "/activity/detail",
    "title": "获取活动详情",
    "group": "activity",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "paId",
            "description": "<p>活动ID（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "pa_id",
            "description": "<p>活动id</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "pa_title",
            "description": "<p>活动标题</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "pa_content",
            "description": "<p>活动内容</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "pa_starttime",
            "description": "<p>开始时间（时间戳）</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "pa_endtime",
            "description": "<p>结束时间（时间戳）</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "if_end",
            "description": "<p>是否结束状态 0-未开始 1-进行中 2-已结束</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"code\":200,\n    \"data\":\n        {\n            \"pa_id\":\"10\",\n            \"pa_title\":\"\\u6d4b\\u8bd5\\u6ce8\\u518c\\u9001\",\n            \"pa_content\":\"\\u6d4b\\u8bd5\\u5145\\u503c\\u9001\\u5185\\u5bb9\",\n            \"pa_starttime\":\"1511222400\",\n            \"pa_endtime\":\"1511222400\",\n            \"if_end\": 0\n        }\n    }",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\": \"活动不存在\"\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\": \"参数错误\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/activity.php",
    "groupTitle": "activity",
    "name": "GetActivityDetail"
  },
  {
    "type": "get",
    "url": "/activity/index",
    "title": "活动列表",
    "group": "activity",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "pa_id",
            "description": "<p>活动id</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "pa_title",
            "description": "<p>活动标题</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "pa_img",
            "description": "<p>活动图片</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "if_end",
            "description": "<p>是否结束状态 0-未开始 1-进行中 2-已结束</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n{\n    \"code\":200,\n    \"data\":{\n        {\n            \"pa_id\":\"10\",\n            \"pa_img\":\"http://www.baidu.com/1.png\",\n            \"pa_title\":\"\\u6d4b\\u8bd5\\u6ce8\\u518c\\u9001\",\n            \"if_end\":0\n        },\n        {\n            \"pa_id\":\"11\",\n            \"pa_img\":\"http://www.baidu.com/2.png\",\n            \"pa_title\":\"\\u6d4b\\u8bd5\\u6ce8\\u518c\\u9001\",\n            \"if_end\":1\n        },\n    }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/activity.php",
    "groupTitle": "activity",
    "name": "GetActivityIndex"
  },
  {
    "type": "get",
    "url": "/agent/getrate",
    "title": "获取当前用户返点率",
    "group": "agent",
    "description": "<p>计算返点赔率 bonus-(bonus*maxRate/100)</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "rate",
            "description": "<p>用户返点率</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bonus",
            "description": "<p>系统默认赔率值</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "maxRate",
            "description": "<p>最大返点率</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\":{\n      'rate':3,//用户返点率\n      'bonus':1.985,//系统赔率\n      'maxRate':3 //最大返点率\n  }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/agent.php",
    "groupTitle": "agent",
    "name": "GetAgentGetrate"
  },
  {
    "type": "get",
    "url": "/agent/link",
    "title": "推广链接列表",
    "group": "agent",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "page",
            "description": "<p>页码</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "nums",
            "description": "<p>显示条数</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ur_id",
            "description": "<p>推广id</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "url",
            "description": "<p>推广链接,前段根据链接生成二维码</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "ur_created_time",
            "description": "<p>生成时间（时间戳）</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "ur_fandian",
            "description": "<p>返点率</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ur_reg_nums",
            "description": "<p>注册人数</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ur_visitor_nums",
            "description": "<p>访问人数</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ur_type",
            "description": "<p>开户类型 1-普通用户 3-代理</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"code\":200,\n    \"data\":[\n                {\n                    \"ur_id\":\"107\",\n                    \"url\":\"http://le.admin.com/auth/reg?c=7dc7e7fb11785844\",\n                    \"ur_type\":\"1\",\n                    \"ur_created_time\":\"1511222400\",\n                    \"ur_fandian\":\"0.9000\",\n                    \"ur_visitor_nums\":\"0\",\n                    \"ur_reg_nums\":\"0\",\n                 },\n                 {\n                    \"ur_id\":\"106\",\n                    \"url\":\"http://le.admin.com/auth/reg?c=553d0860fd529b0d\",\n                    \"ur_type\":\"3\",\n                    \"ur_created_time\":\"2017-09-16 18:48:18\",\n                    \"ur_fandian\":\"0.8000\",\n                    \"ur_visitor_nums\":\"0\",\n                    \"ur_reg_nums\":\"0\"\n                 }\n\n     ]\n  }",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/agent.php",
    "groupTitle": "agent",
    "name": "GetAgentLink"
  },
  {
    "type": "post",
    "url": "/agent/add",
    "title": "手动注册",
    "group": "agent",
    "description": "<p>当前赔率浮动计算公式 newRateMoney = rateMoney + bonus*rate/100</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>注册类型 1-会员 3-代理 （必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "decimal",
            "optional": false,
            "field": "rate",
            "description": "<p>返点率（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "varchar",
            "optional": false,
            "field": "username",
            "description": "<p>用户名（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "varchar",
            "optional": false,
            "field": "pwd",
            "description": "<p>密码（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "varchar",
            "optional": false,
            "field": "confirm",
            "description": "<p>确认密码（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "    {\n      \"code\": \"500\",\n\t\t \"msg\":\"注册失败\"\n    }",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/agent.php",
    "groupTitle": "agent",
    "name": "PostAgentAdd"
  },
  {
    "type": "post",
    "url": "/agent/edit",
    "title": "编辑推广链接",
    "group": "agent",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>链接id(必填)</p>"
          },
          {
            "group": "Parameter",
            "type": "demical",
            "optional": false,
            "field": "rate",
            "description": "<p>用户类型(必填,可为0)</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>用户类型(必填)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\":\"更新出错\"\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\":\"推广链接不存在\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/agent.php",
    "groupTitle": "agent",
    "name": "PostAgentEdit"
  },
  {
    "type": "post",
    "url": "/agent/linkadd",
    "title": "生成推广链接",
    "group": "agent",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Float",
            "optional": false,
            "field": "rate",
            "description": "<p>返点率</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "type",
            "description": "<p>用户类型  1-会员  3-代理</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\":\"生成失败\"\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\":\"请选择用户类型\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/agent.php",
    "groupTitle": "agent",
    "name": "PostAgentLinkadd"
  },
  {
    "type": "get",
    "url": "/auth/captcha",
    "title": "图形验证码",
    "group": "auth",
    "description": "<p>直接返回图片，同时写入session，使用方法，参考调用方法&lt;img src=&quot;/auth/captcha&quot;/ onclick=&quot;this.src='/auth/captcha?'+Math.random();&quot;&gt;  图形验证码图片大小可以接口设置。。。到时候沟通</p>",
    "version": "0.0.0",
    "filename": "docs/conf/auth.php",
    "groupTitle": "auth",
    "name": "GetAuthCaptcha"
  },
  {
    "type": "get",
    "url": "/auth/logout",
    "title": "退出登录",
    "group": "auth",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  'data':{\n      'url':'/auth/login'\n  }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/auth.php",
    "groupTitle": "auth",
    "name": "GetAuthLogout"
  },
  {
    "type": "get",
    "url": "/auth/mobiCap",
    "title": "手机验证码",
    "group": "auth",
    "description": "<p>type类型为1,3,6的时候需要使用图形验证码</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "type",
            "description": "<p>使用类型  1-重置登录密码 2-重置资金密码 3-注册验证码 4-解绑旧手机 5-修改微信号 6-绑定新手机（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "mobi",
            "description": "<p>手机号（1,3,6需要填写手机号）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "imgcap",
            "description": "<p>图形验证码（1,3,6需要填写图形验证码）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  'msg': \"请输入图形验证码/请输入正确的手机号/\"\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"501\",\n  'msg': \"图片验证码输入错误/验证码发送失败\"\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"503\",\n  'msg': \"0000072165\" //距离下一条短信发送时间\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/auth.php",
    "groupTitle": "auth",
    "name": "GetAuthMobicap"
  },
  {
    "type": "post",
    "url": "/auth/login",
    "title": "用户登录",
    "group": "auth",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "username",
            "description": "<p>用户名</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "pwd",
            "description": "<p>密码（当密码为空时从）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "rem",
            "description": "<p>是否记住密码  0-否  1-是</p>"
          }
        ]
      }
    },
    "error": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  'data':{\n      'url': '/'\n  }\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"密码错误\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/auth.php",
    "groupTitle": "auth",
    "name": "PostAuthLogin"
  },
  {
    "type": "post",
    "url": "/auth/wxLogin",
    "title": "微信登录",
    "group": "auth",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "accessToken",
            "description": "<p>accessToken</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "openId",
            "description": "<p>openId</p>"
          }
        ]
      }
    },
    "error": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  'data':{\n      'url': '/'\n  }\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"密码错误\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/auth.php",
    "groupTitle": "auth",
    "name": "PostAuthWxlogin"
  },
  {
    "type": "get",
    "url": "/bank/list",
    "title": "银行列表",
    "group": "bank",
    "description": "<p>key为银行id</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\" : [\n      {\n          'id':0,\n          'name':\"中国农业银行\"\n      },\n      {\n          'id':1,\n          'name':\"中国建设银行\"\n      },\n      {\n          'id':1,\n          'name':\"中国银行\"\n      }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/bank.php",
    "groupTitle": "bank",
    "name": "GetBankList"
  },
  {
    "type": "get",
    "url": "/bank/show",
    "title": "银行卡列表",
    "group": "bank",
    "description": "<p>姓名和卡号要隐藏部分后返回给客户端</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ubc_id",
            "description": "<p>银行卡记录id</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "ubc_uname",
            "description": "<p>开户人姓名</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "ubc_bank_name",
            "description": "<p>开户行</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "ubc_number",
            "description": "<p>银行卡号</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "ubc_province",
            "description": "<p>开户省</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ubc_status",
            "description": "<p>银行卡状态   1-正常   3-删除</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\" : [{\n          'ubc_id': 10,\n          'ubc_uname' : '黄*超',\n          'ubc_bank_name' : '中国银行',\n          'ubc_number' : '169111****4516',\n          'ubc_province' : '河北省邯郸市',\n          'ubc_status' : 1\n      }]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/bank.php",
    "groupTitle": "bank",
    "name": "GetBankShow"
  },
  {
    "type": "post",
    "url": "/bank/add",
    "title": "添加银行卡",
    "group": "bank",
    "description": "<p>添加银行卡需要先设置资金密码，如果没有资金密码，默认返回code：501，需要跳转到设置个人信息界面。。。其他错误返回500</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "name",
            "description": "<p>真实姓名（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号码（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "bankId",
            "description": "<p>所属银行（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "number",
            "description": "<p>银行账号（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "province",
            "description": "<p>开户行省份（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "city",
            "description": "<p>开户行城市（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "uname",
            "description": "<p>开户行 (选填)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "pwd",
            "description": "<p>资金密码（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"添加失败\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/bank.php",
    "groupTitle": "bank",
    "name": "PostBankAdd"
  },
  {
    "type": "post",
    "url": "/bank/del",
    "title": "删除银行卡",
    "group": "bank",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "ubc_id",
            "description": "<p>银行卡记录id（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"删除失败\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/bank.php",
    "groupTitle": "bank",
    "name": "PostBankDel"
  },
  {
    "type": "get",
    "url": "/help/index",
    "title": "新手帮助",
    "group": "help",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "problem",
            "description": "<p>常见问题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "recharge",
            "description": "<p>存款帮助</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "withdraw",
            "description": "<p>取款帮助</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "game",
            "description": "<p>玩法说明</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\": {\n      'problem' : \"\",\n      'recharge' : \"\",\n      'withdraw' : \"\",\n      'game' : [\n          {\n              'id':1,\n              'name':'重庆时时彩',\n              'rule' : ''\n          },\n          {\n              'id':2,\n              'name':'11选5',\n              'rule' : '',\n          },\n      ]\n  }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/help.php",
    "groupTitle": "help",
    "name": "GetHelpIndex"
  },
  {
    "type": "get",
    "url": "/index/banner",
    "title": "首页banner/公告",
    "group": "index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ib_id",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "ib_url",
            "description": "<p>url</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "ib_img",
            "description": "<p>img</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n  \"data\":\n  [\n      {\n          'ib_id' : 1,\n          'ib_url':'http://www.xxx.com/xxx.com',\n          'ib_img':'http://www.xxx.com/xxx.jpg',\n      }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/index.php",
    "groupTitle": "index",
    "name": "GetIndexBanner"
  },
  {
    "type": "get",
    "url": "/index/index",
    "title": "所有彩种信息",
    "group": "index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "bet_id",
            "description": "<p>彩种id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "bet_name",
            "description": "<p>彩种名</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "img",
            "description": "<p>彩种图标</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bet_play_type",
            "description": "<p>彩种玩法(1-信用玩法 3-官方玩法)</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "type",
            "description": "<p>彩种结果显示（1-圆形 2-方形）</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "sel",
            "description": "<p>用户选择，是否显示   1-是 0-否</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "url",
            "description": "<p>彩种下注地址</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\": [\n      {\n          'bet_id': 5,\n          'bet_name' : '北京28',\n          'img' : \"img/11xuan5-liaoning.png\",\n          'bet_play_type' : 1,\n          'type' : 1,//圆形\n          'sel' : 1,//用户选择\n          'url' : 'http://dazhongcai.vip/game/info/1',  //url地址\n      },\n      {\n          'bet_id': 6,\n          'bet_name' : '加拿大28',\n          'img' : \"img/cakeno.png\",\n          'bet_play_type' : 1,\n          'type' : 2,//方形,\n          'sel' : 0,\n          'url' : 'http://dazhongcai.vip/game/info/6',\n      }\n\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/index.php",
    "groupTitle": "index",
    "name": "GetIndexIndex"
  },
  {
    "type": "get",
    "url": "/index/notice",
    "title": "通知滚动条",
    "group": "index",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n  \"data\":\n        [\n            {'n_content':'诚邀实力代理合作'}, //通知内容\n            {'n_content':'注册送好礼，XXXXXXXX'},\n        ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/index.php",
    "groupTitle": "index",
    "name": "GetIndexNotice"
  },
  {
    "type": "get",
    "url": "/index/opentime",
    "title": "各彩种开奖时间",
    "group": "index",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n  \"data\":{\n      \"interval\":30,   //截止时间30s\n      \"time\":1514310140,    //服务器当前时间\n      \"list\":[\n         {\n              \"bet_id\":1,   //彩种id\n              \"bres_open_time\":1514311140     //开奖时间\n         },\n         {\n              \"bet_id\":2,\n              \"bres_open_time\":1514311140\n         },\n         {\n              \"bet_id\":5,\n              \"bres_open_time\":1514312040\n         },\n         {\n              \"bet_id\":34,\n              \"bres_open_time\":0   //未获取到最新一期开奖时间，设置每隔几分钟请求刷新\n         },\n      ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/index.php",
    "groupTitle": "index",
    "name": "GetIndexOpentime"
  },
  {
    "type": "get",
    "url": "/index/service",
    "title": "联系客服",
    "group": "index",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n  \"data\":{\n      'url':'',//网络地址\n  }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/index.php",
    "groupTitle": "index",
    "name": "GetIndexService"
  },
  {
    "type": "post",
    "url": "/index/setBet",
    "title": "设置彩种",
    "group": "index",
    "description": "<p>默认信用、官方玩法至少各保留3个彩种不可删除</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "betId",
            "description": "<p>彩种ID （必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "type",
            "description": "<p>类型（1为添加，3为删除）（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  'msg' : '操作失败'\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/index.php",
    "groupTitle": "index",
    "name": "PostIndexSetbet"
  },
  {
    "type": "get",
    "url": "/order/detail",
    "title": "订单详情",
    "group": "order",
    "description": "<p>注数默认为1，实际金额等于中奖金额+返水金额-投注金额，投注号码为{投注玩法(球/赔率)</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Ind",
            "optional": false,
            "field": "boId",
            "description": "<p>订单id（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bet_name",
            "description": "<p>彩种</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_sn",
            "description": "<p>交易编号</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "bo_played_name",
            "description": "<p>玩法</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_created_time",
            "description": "<p>下注时间</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_money",
            "description": "<p>投注金额</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_issue",
            "description": "<p>期号</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "bres_result",
            "description": "<p>开奖号码</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "bo_bonus",
            "description": "<p>中奖金额</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "bo_back",
            "description": "<p>返水点数</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "bo_back_money",
            "description": "<p>返水金额</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_draw_result",
            "description": "<p>中奖状态   1-中奖  3-未中奖   5-未开奖</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "act_money",
            "description": "<p>实际金额</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "content",
            "description": "<p>投注号码</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  'data':\n      {\n          'bet_name': '北京赛车PK10',\n          'bo_sn': '1707192249284207',\n          'bo_played_name': '两面',\n          'bo_created_time': '1509331475',\n          'bo_money': 2,\n          'bo_issue': '629675',\n          'bres_result': '2,10,5,6,7,3,8,4,9,1',\n          'bo_bonus': 0.0000,\n          'bo_back': 0.0110,\n          'bo_back_money': 0.0220,\n          'bo_draw_result':  1,\n          'act_money'：2.0,\n          'content':'任选一(3/2.0000)'\n      }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"订单不存在\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/order.php",
    "groupTitle": "order",
    "name": "GetOrderDetail"
  },
  {
    "type": "get",
    "url": "/order/report",
    "title": "个人报表",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "startTime",
            "description": "<p>开始时间(选填)</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "endTime",
            "description": "<p>结束时间(选填)</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "bet_id",
            "description": "<p>彩种id(选填,为0时代表全部)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "date",
            "description": "<p>日期(日期格式)</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "num",
            "description": "<p>彩种有效笔数</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "money",
            "description": "<p>投注金额</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "win",
            "description": "<p>输赢金额</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "water",
            "description": "<p>回水金额</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "earn",
            "description": "<p>实际输赢金额</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\":\n     'lists': [\n         {\n             'date': '2017-11-01',//输出格式为日期格式，非时间戳\n             'num': 2,\n             'money': 4,\n             'win': -4,\n             'water': '0.044',\n             'earn' : -3.956\n          },\n      ],\n      'total':\n          {\n              'num': 2,\n              'money': 4,\n              'win': -4,\n              'water': '0.044',\n              'earn' : -3.956\n          }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"彩种id不存在\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/order.php",
    "groupTitle": "order",
    "name": "GetOrderReport"
  },
  {
    "type": "get",
    "url": "/order/reportdetail",
    "title": "日报表详情（订单/追号查询）",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "startTime",
            "description": "<p>开始时间（选填,日期格式2017-12-01）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "endTime",
            "description": "<p>结束时间（选填，日期格式2017-12-01）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "bet_id",
            "description": "<p>彩种id  0为全部（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "type",
            "description": "<p>类型    1-订单查询   3-追号查询   5-可撤销订单列表</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "page",
            "description": "<p>页码（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "nums",
            "description": "<p>记录条数（选填）</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "bet_name",
            "description": "<p>彩种名称</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "bo_played_name",
            "description": "<p>玩法</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_issue",
            "description": "<p>期号</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_created_time",
            "description": "<p>下注时间</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_draw_result",
            "description": "<p>中奖状态   1-中奖 3-未中奖 5-未开奖</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bo_money",
            "description": "<p>投注金额</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\":\n  [\n      {\n        'bo_id': 5,\n        'bet_name': '北京赛车PK10',\n        'bo_issue': '629675'\n        'bo_created_time': '1509331475',\n        'bo_draw_result': 5,\n        'bo_money': 2,\n        'bo_played_name': '两面(冠军:龙)'\n      }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"类型错误\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/order.php",
    "groupTitle": "order",
    "name": "GetOrderReportdetail"
  },
  {
    "type": "post",
    "url": "/order/cancelorder",
    "title": "订单撤销操作",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "boId",
            "description": "<p>订单id（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"订单不存在\"\n}",
          "type": "json"
        },
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"撤销失败\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/order.php",
    "groupTitle": "order",
    "name": "PostOrderCancelorder"
  },
  {
    "type": "get",
    "url": "/recharge/index",
    "title": "充值方式",
    "group": "recharge",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "pay_type",
            "description": "<p>充值方式</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "type_name",
            "description": "<p>充值方式名称</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"code\" : 200,\n    \"data\" : {\n        'qrcode':[{    //付款方式\n                \"pay_type\" : 1,\n                \"type_name\" : \"快捷入款\",\n                'logo': 'fs',\n                'pcc_min': 100.00,\n                'pcc_max':10000.00\n                'chanel':[{\n                    'pcc_id':1,\n                    'pcc_name' : 'AustPay',\n                    },\n                    {\n                    'pcc_id':2,\n                    'pcc_name' : 'Aust',\n                    }\n                 ]\n             },\n             {\n                 \"pay_type\" :3,\n                 \"type_name\" : \"微信入账\",\n                 'logo': 'wxscan',\n                 'pcc_min': 100.00,\n                 'pcc_max':10000.00\n                 'chanel':[{\n                    'pcc_id':1,\n                    'pcc_name' : 'AustPay',\n                    },\n                    {\n                    'pcc_id':2,\n                    'pcc_name' : 'Aust',\n                    }\n                 ]\n              },\n              {\n                  \"pay_type\" :5,\n                  \"type_name\" : \"QQ钱包\",\n                  'logo': 'QQ',\n                  'pcc_min': 100.00,\n                  'pcc_max':10000.00\n                  'chanel':[{\n                    'pcc_id':1,\n                    'pcc_name' : 'AustPay',\n                    }\n                 ]\n              },\n              {\n                  \"pay_type\" :7,\n                  \"type_name\" : \"支付宝\",\n                  'pcc_min': 100.00,\n                  'pcc_max':10000.00\n                  'chanel':[{\n                    'pcc_id':1,\n                    'pcc_name' : 'AustPay',\n                    }\n                 ]\n              },\n              ],\n        \"bank\":{\n               'min': 1,\n               'max':1000,\n               'list': {\n                   '中国农业银行',\n                   '中国银行'\n               }\n        }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/recharge.php",
    "groupTitle": "recharge",
    "name": "GetRechargeIndex"
  },
  {
    "type": "post",
    "url": "/recharge/create",
    "title": "创建充值订单",
    "group": "recharge",
    "description": "<p>快捷支付扫码则返回付款二维码地址</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "channel",
            "description": "<p>支付渠道id  3-微信入款   5-QQ钱包   7-支付宝  9-支付宝收款码   11-微信收款码   13-银联扫码   15-银行卡入款</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "account",
            "description": "<p>账号 (个别支付渠道必填)</p>"
          },
          {
            "group": "Parameter",
            "type": "Float",
            "optional": false,
            "field": "amount",
            "description": "<p>实际充值金额（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "aId",
            "description": "<p>充值活动id(选填)</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "bank",
            "description": "<p>银行id(银行入款时必填)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "name",
            "description": "<p>银行账户名(银行入款时必填)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  'data':\n      {\n          'url': 'http://zf.dyyjk.com/api3.php?merchid=13',\n          'qrcode' : '',//base64位二维码\n          'bank' : {\n              'name':'',//银行户名\n              'account': '',//银行账号\n              'bankId':1,\n          }\n      }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"类型错误\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/recharge.php",
    "groupTitle": "recharge",
    "name": "PostRechargeCreate"
  },
  {
    "type": "get",
    "url": "/result/detail",
    "title": "彩种开奖结果列表",
    "group": "result",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "nums",
            "description": "<p>每页加载条数</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "page",
            "description": "<p>加载页码</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "bet_id",
            "description": "<p>彩种id(必填)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bres_id",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bres_periods",
            "description": "<p>期号</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "bres_result",
            "description": "<p>开奖结果</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bres_plat_open_time",
            "description": "<p>开奖时间</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "title",
            "description": "<p>玩法总和名称</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "zh",
            "description": "<p>总和</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "ds",
            "description": "<p>单双   1-双   2-单</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "dx",
            "description": "<p>大小   1-大   2-小   3-和</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "lh",
            "description": "<p>龙虎</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\":\n  [\n      {\n        'bres_id': 2,\n        'bres_periods': '10002',\n        'bres_plat_open_time' : '1656122468',\n        'bres_result': [4,1,3,6,7],\n        'title':'总和',\n        'zh': 23,\n        'dx':2,\n        'ds':2,\n        'lh': ['虎','虎','龙','龙']\n      },\n      {\n        'bres_id': 1,\n        'bres_periods': '10001',\n        'bres_plat_open_time' : '1656122468',\n        'bres_result': [1,5,3,6,2],\n        'title':'总和',\n        'zh': 17,\n        'dx':1,\n        'ds':2,\n        'lh': ['龙','虎','龙','龙']\n      }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n   \"code\": \"500\",\n   \"msg\" : \"彩种不存在\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/result.php",
    "groupTitle": "result",
    "name": "GetResultDetail"
  },
  {
    "type": "get",
    "url": "/result/index",
    "title": "获取各彩种开奖信息",
    "group": "result",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bres_id",
            "description": "<p>结果id</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bet_id",
            "description": "<p>彩种id</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "bres_periods",
            "description": "<p>期号</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "bres_result",
            "description": "<p>开奖结果</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"code\":200,\n    \"data\":\n         [\n             {\n                 \"bres_id\":\"78235\",\n                 \"bet_id\":\"1\",\n                 \"bres_periods\":\"20170922023\",\n                 \"bres_result\":[\"9\",\"6\",\"1\",\"5\",\"2\"],\n             },\n             {\n                 \"bres_id\":\"79084\",\n                 \"bet_id\":\"2\",\n                 \"bres_periods\":\"846583\",\n                 \"bres_result\":[\"5\",\"0\",\"8\"],\n             },\n         ]\n  }",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/result.php",
    "groupTitle": "result",
    "name": "GetResultIndex"
  },
  {
    "type": "get",
    "url": "/team/show",
    "title": "下线管理-下线总账",
    "group": "team",
    "description": "<p>没有传时间参数默认显示本周报表数据</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "startTime",
            "description": "<p>开始时间(选填,日期格式)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "endTime",
            "description": "<p>结束时间(选填，日期格式)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ua_team_num",
            "description": "<p>总人数</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ua_reg_num",
            "description": "<p>注册人数</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "online",
            "description": "<p>在线人数</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "balance",
            "description": "<p>总余额</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "ar_team_withdraw_money",
            "description": "<p>提现</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "ar_team_recharge_money",
            "description": "<p>充值</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "ar_team_bet_money",
            "description": "<p>投注金额</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "ar_my_back_money",
            "description": "<p>本级佣金</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "agent_back",
            "description": "<p>下级佣金</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "agent_earn",
            "description": "<p>下级盈亏</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\" :\n  {\n          'ua_team_num': 1,\n          'ua_reg_num' : 1,\n          'online' : 3,\n          'balance' : 50214.3240,\n          'ar_team_withdraw_money': 0,\n          'ar_team_recharge_money': 0.0000,\n          'ar_team_bet_money': 0.0000,\n          'ar_my_back_money': 0.0000,\n          'agent_back' :  0.0000,\n          'agent_earn':0\n  }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/team.php",
    "groupTitle": "team",
    "name": "GetTeamShow"
  },
  {
    "type": "get",
    "url": "/team/table",
    "title": "下线报表",
    "group": "team",
    "description": "<p>数值都已计算号</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "uid",
            "description": "<p>没有则为自己的下线 (选填)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "startTime",
            "description": "<p>开始时间(选填，日期格式)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "endTime",
            "description": "<p>结束时间(选填，日期格式)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\" :\n  {\n      'base' : //列表\n      [\n          {\n              'u_id': 21,//下级id\n              'u_name': 'test11',//下级用户名\n              'ua_type': 1,//下级类型   1-会员   3-代理\n              'recharge': '0.0000',//充值\n              'withdraw': '0.0000',//提现\n              'earn':0,//输赢\n              'bet':0,//投注额\n              'mybet':0,//本级投注\n              'myearn'：0,//本级盈亏\n              'myback':0,//我的佣金\n              'ua_reg_nums':0,//注册人数\n              'memo':'',//备注\n          },\n      ],\n      'total':   //总计\n          {\n              'myback':0,//我的佣金\n              'myearn':0,//本级盈亏\n              'mybet' : 0, //本级投注\n              'earn': -4,//总盈亏\n              'bet': 0,//投注金额\n              'withdraw':0,//提现总额\n              'recharge':0,//充值总额\n              'reg_nums':0//注册人数\n          }\n  }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/team.php",
    "groupTitle": "team",
    "name": "GetTeamTable"
  },
  {
    "type": "get",
    "url": "/user/list",
    "title": "下线列表",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "uId",
            "description": "<p>用户id（选填,没有默认当前用户）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "username",
            "description": "<p>用户名（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "starttime",
            "description": "<p>开始时间（选填，日期格式）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "endtime",
            "description": "<p>结束时间（选填，日期格式）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "page",
            "description": "<p>页码（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "nums",
            "description": "<p>每页显示条数（选填）</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "u_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "u_name",
            "description": "<p>用户名</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ua_type",
            "description": "<p>用户类型 1-会员 3-代理</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "ua_rate",
            "description": "<p>返点率</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ua_status",
            "description": "<p>状态 1-正常 3-关闭</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "w_money",
            "description": "<p>余额</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "ua_memo",
            "description": "<p>备注</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\":\n          [\n              {\n                  'u_id': '32',\n                  'u_name': 'test',\n                  'ua_type':'3',\n                  'ua_rate': '5',\n                  'ua_status' : '1' ,\n                  'w_money' : '50125.3312',\n                  'ua_memo': '张三'\n              }\n          ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/user.php",
    "groupTitle": "user",
    "name": "GetUserList"
  },
  {
    "type": "get",
    "url": "/user/money",
    "title": "用户余额",
    "group": "user",
    "description": "<p>刷用户是否被踢，同时还是要返回用户当前金额</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n  \"data\" : 100//账户余额\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/user.php",
    "groupTitle": "user",
    "name": "GetUserMoney"
  },
  {
    "type": "get",
    "url": "/user/rateDetail",
    "title": "下级返点详情",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "uId",
            "description": "<p>代理id（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "u_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "u_name",
            "description": "<p>开户账号</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "ua_rate",
            "description": "<p>回水等级</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "max_rate",
            "description": "<p>最高回水为用户自身的回水等级</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "rateMoney",
            "description": "<p>当前奖金</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  'data':\n      {\n          'u_id':1,\n          'u_name' : 'test',\n          'ua_rate' : 1.39,\n          'max_rate' :4,\n          'rate_money' : 1987\n      }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"详情不存在\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/user.php",
    "groupTitle": "user",
    "name": "GetUserRatedetail"
  },
  {
    "type": "post",
    "url": "/user/base",
    "title": "个人信息",
    "group": "user",
    "description": "<p>首次设置默认没有个人信息，【get】可获取用户已设置的个人信息，【post】提交个人信息</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "pwd",
            "description": "<p>资金密码（首次设置必填，已设置资金密码则选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "confirm",
            "description": "<p>重复资金密码（首次设置必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "mobile",
            "description": "<p>手机号（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "wechat",
            "description": "<p>微信号（首次设置必填，其他选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "captcha",
            "description": "<p>验证码（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\":{\n      'pwd': 0,//0-未设置, 1-已设置\n      'mobile': '188****666',\n      'wechat': 'test'\n  }\n}",
          "type": "json"
        },
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"设置失败\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/user.php",
    "groupTitle": "user",
    "name": "PostUserBase"
  },
  {
    "type": "post",
    "url": "/user/checkcap",
    "title": "校验短信验证码",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "type",
            "description": "<p>类型(2-重置资金密码   4-解绑旧手机  5-修改微信号)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "msgcode",
            "description": "<p>短信验证码</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"验证失败\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/user.php",
    "groupTitle": "user",
    "name": "PostUserCheckcap"
  },
  {
    "type": "post",
    "url": "/user/edit",
    "title": "备注信息",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "info",
            "description": "<p>备注信息（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "uId",
            "description": "<p>用户id（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"备注失败\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/user.php",
    "groupTitle": "user",
    "name": "PostUserEdit"
  },
  {
    "type": "post",
    "url": "/user/setInfo",
    "title": "修改个人资料",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "type",
            "description": "<p>类型(2-重置资金密码   4-解绑旧手机  5-修改微信号)</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "mobi",
            "description": "<p>手机号（type为4更换手机必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "mobicap",
            "description": "<p>手机验证码（type为4更换手机必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "pwd",
            "description": "<p>资金密码（type为2时用）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "confirm",
            "description": "<p>确认密码（type为2时用）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "wechat",
            "description": "<p>微信号（type为5时用）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"设置失败\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/user.php",
    "groupTitle": "user",
    "name": "PostUserSetinfo"
  },
  {
    "type": "post",
    "url": "/user/setpwd",
    "title": "修改登录密码",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "old_pwd",
            "description": "<p>旧密码（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "new_pwd",
            "description": "<p>新密码（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "confirm",
            "description": "<p>重复新密码（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"修改失败\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/user.php",
    "groupTitle": "user",
    "name": "PostUserSetpwd"
  },
  {
    "type": "post",
    "url": "/user/setRate",
    "title": "返点设置",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "uId",
            "description": "<p>代理id（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Float",
            "optional": false,
            "field": "rate",
            "description": "<p>返点率（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"设置失败\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/user.php",
    "groupTitle": "user",
    "name": "PostUserSetrate"
  },
  {
    "type": "get",
    "url": "/wallet/index",
    "title": "账变记录",
    "group": "wallet",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "type",
            "description": "<p>账变类型  1-充值 3-开奖结算 5-提现 7-投注,9-系统赠送 11-返点 13-系统回水 15-退款 0-全部（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "page",
            "description": "<p>页码（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "nums",
            "description": "<p>显示条数（选填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\":\n  {\n      'count' : '100',//总计笔数\n      'total' : '236.321',//总计交易额度\n      'subInfo' ://账变记录列表\n          [{\n              'uwr_created_time' : '1486952578',//时间\n              'uwr_type' : 1,//账变类型\n              'uwr_money' ; '-2',//交易额\n              'uwr_memo': '注册活动赠送65535',//备注\n          },\n      ],\n   }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"类型错误\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/wallet.php",
    "groupTitle": "wallet",
    "name": "GetWalletIndex"
  },
  {
    "type": "get",
    "url": "/wallet/show",
    "title": "下线帐变记录",
    "group": "wallet",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "uId",
            "description": "<p>用户Id（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "username",
            "description": "<p>用户名（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "next",
            "description": "<p>是否包括下级   0-否   1-是（选填，必须填写用户名才能勾选）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "type",
            "description": "<p>账变类型  1-充值 3-开奖结算 5-提现 7-投注,9-系统赠送 11-返点 13-系统回水 15-退款 0-全部（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "startDay",
            "description": "<p>开始时间（选填，日期格式）</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "endDay",
            "description": "<p>结束时间（选填，日期格式）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "page",
            "description": "<p>页码（选填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "nums",
            "description": "<p>显示条数（选填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\":\n  {\n      'count' : '59',//总计交易笔数\n      'income' : 22111.00,//总收入\n      'expend' : -11111.00,//总支出\n      'lists' ://账变记录列表\n          [{\n              'u_name' : 'winson',//用户名\n              'uwr_created_time' : '1486952578',//时间\n              'uwr_type' : 1,//账变类型\n              'uwr_money' ; '-2',//交易额\n              'uwr_memo': '注册活动赠送65535',//备注\n          },\n      ],\n   }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  \"msg\" : \"类型错误\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/wallet.php",
    "groupTitle": "wallet",
    "name": "GetWalletShow"
  },
  {
    "type": "get",
    "url": "/withdraw/index",
    "title": "提现规则",
    "group": "withdraw",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "min",
            "description": "<p>出款下限</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "max",
            "description": "<p>出款上限</p>"
          },
          {
            "group": "Success 200",
            "type": "Float",
            "optional": false,
            "field": "limit",
            "description": "<p>每日最高提款</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "spent",
            "description": "<p>已达投注量</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "consume",
            "description": "<p>出款需达投注量</p>"
          },
          {
            "group": "Success 200",
            "type": "Int",
            "optional": false,
            "field": "whether",
            "description": "<p>是否能提款(1-是 0-否)</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "bank",
            "description": "<p>出款银行信息</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\",\n  \"data\":\n      {\n\n          'min':10,\n          'max': 42\n          'limit': '100000',\n          'spent': 8.0000,\n          'consume': 8.0000\n          'whether': 1,\n          \"bank\":[{\"ubc_id\":\"9\",\"ubc_number\":\"6214855920210123\"}],\n      }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/withdraw.php",
    "groupTitle": "withdraw",
    "name": "GetWithdrawIndex"
  },
  {
    "type": "post",
    "url": "/withdraw/apply",
    "title": "提现操作",
    "group": "withdraw",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "bank",
            "description": "<p>银行卡id（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Float",
            "optional": false,
            "field": "money",
            "description": "<p>提现金额（必填）</p>"
          },
          {
            "group": "Parameter",
            "type": "Int",
            "optional": false,
            "field": "moneyPwd",
            "description": "<p>资金密码（必填）</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n  \"code\": \"500\",\n  'msg': '密码错'\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "docs/conf/withdraw.php",
    "groupTitle": "withdraw",
    "name": "PostWithdrawApply"
  }
] });
