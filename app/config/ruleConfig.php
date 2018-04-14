<?php

return [
    'index' => [
        'index' => [
            'GET' => [
                'id' => [
                    'type' => 'absdigit',
                    'cancel' => true,
                    'msg' => '请提交合法的id'
                ]
            ]
        ],

        'editname' => [
            'POST' => [
                'id' => [
                    'type' => 'absdigit',
                    ''
                ]
            ],
        ],

        'login' => [
            'POST' => [

            ]
        ]
    ]
];
