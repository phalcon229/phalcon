<?php

//include __DIR__ . '/BaseRules.php';
$rules['betset'] = [
        '_method' => [
             'post' => ['br_id', 'br_bonus'],
        ],
        'br_id' => [
            'required' => 1,
            'filter' => 'int',
            'msg' => 'Invalid Data'
        ],
        'br_bonus' => [
            'required' => 1,
            'filter' => 'int',
            'msg' => 'Invalid Data'
        ]
];
$rules['ntdoadd'] = [
        '_method' => [
             'post' => ['n_title', 'n_content'],
        ],
        'n_title' => [
            'required' => 1,
            'filter' => 'trim',
            'msg' => '请输入标题'
        ],
        'n_content' => [
            'required' => 1,
            'filter' => 'trim',
            'msg' => '请输入内容'
        ]
];

$rules['ntdel'] = [
        '_method' => [
             'post' => ['n_id'],
        ],
        'n_id' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
];

$rules['dontedit'] = [
        '_method' => [
             'post' => ['n_id', 'n_title', 'n_content'],
        ],
        'n_id' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
         'n_title' => [
            'required' => 1,
            'filter' => 'trim',
            'msg' => '请输入标题'
        ],
        'n_content' => [
            'required' => 1,
            'filter' => 'trim',
            'msg' => '请输入内容'
        ]
];

$rules['acDel'] = [
        '_method' => [
             'post' => ['a_id'],
        ],
        'a_id' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
];

$rules['acdoedit'] = [
        '_method' => [
             'post' => ['a_id', 'a_title', 'a_content', 'img'],
        ],
        'a_id' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'a_title' => [
            'required' => 1,
            'filter' => 'trim',
            'msg' => '请输入活动名称'
        ],
        'a_content' => [
            'required' => 1,
            'filter' => 'trim',
            'msg' => '请输入活动内容'
        ],
        'img' => [
            'required' => 0,
            'filter' => 'trim',
        ]

];

$rules['acdoadd'] = [
        '_method' => [
             'post' => ['pa_type', 'pa_gift_money','pa_money3','pa_title','pa_starttime','pa_endtime','pa_money1','pa_history_money','pa_img', 'pa_content'],
        ],
        'pa_type' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'pa_gift_money' => [
            'required' => 0,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'pa_money3' => [
            'required' => 0,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'pa_title' => [
            'required' => 1,
            'filters' => 'trim',
            'msg' => '请输入活动名称'
        ],
        'pa_starttime' => [
            'required' => 1,
            'filters' => 'trim',
            'msg' => '请输入开始时间'
        ],
        'pa_endtime' => [
            'required' => 1,
            'filters' => 'trim',
            'msg' => '请输入结束时间'
        ],
        'pa_money1' => [
            'required' => 0,
            'filters' => 'float',
            'msg' => 'Invalid Data'
        ],
        'pa_history_money' => [
            'required' => 1,
            'filters' => 'float',
            'msg' => '请输入提现最低流水'
        ],
        'pa_content' => [
            'required' => 1,
            'filters' => 'trim',
            'msg' => '请输入活动内容'
        ],
        'pa_img' => [
            'required' => 0,
            'filters' => 'trim',
            'msg' => '请选择活动图片'
        ],
];

$rules['betconfset'] = [
        '_method' => [
             'post' => ['bet_id', 'bet_max', 'bet_min', 'bet_isenable' ],
        ],
        'bet_id' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'bet_max' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'bet_min' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'bet_isenable' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ]
];
$rules['dobase'] = [
        '_method' => [
             'post' => ['sc_1', 'sc_2', 'sc_3', 'sc_4', 'sc_5', 'sc_6', 'sc_7', 'sc_8'],
        ],
        'sc_1' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'sc_2' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'sc_3' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        // 'sc_4' => [
        //     'required' => 1,
        //     'filters' => 'int',
        //     'msg' => 'Invalid Data'
        // ],
        // 'sc_5' => [
        //     'required' => 1,
        //     'filters' => 'int',
        //     'msg' => 'Invalid Data'
        // ],
        'sc_6' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'sc_7' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
        'sc_8' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],

];
$rules['bndoadd'] = [
        '_method' => [
             'post' => ['type'],
        ],
        'type' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ]
];
$rules['dobnedit'] = [
        '_method' => [
             'post' => ['title', 'sort', 'URL', 'img'],
        ],
        'title' => [
            'required' => 1,
            'filters' => 'trim',
            'msg' => 'Invalid Data'
        ],
        'sort' => [
            'required' => 1,
            'filter' => 'trim',
            'msg' => 'Invalid Data'
        ],
        'URL' => [
            'required' => 1,
            'filter' => 'trim',
            'msg' => 'Invalid Data'
        ],
        'img' => [
            'required' => 0,
            'filter' => 'trim',
            'msg' => 'Invalid Date'
        ]
];

$rules['bndel'] = [
        '_method' => [
             'post' => ['ib_id', 'sort', 'URL', 'img'],
        ],
        'ib_id' => [
            'required' => 1,
            'filters' => 'int',
            'msg' => 'Invalid Data'
        ],
];
return $rules;
