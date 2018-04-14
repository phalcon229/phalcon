<?php
// include __DIR__ . '/BaseRules.php';
$rules['updateMoney'] = [
        '_method' => [
             'post' => ['id', 'state', 'money', 'reason'],
        ],
        'id' => [
            'required' => 1,
            'filter' => 'int',
            'msg' => 'Invalid Data'
        ],
        'state' => [
            'required' => 1,
            'filter' => 'int',
            'msg' => 'Invalid Data'
        ],
        'money' => [
            'required' => 1,
            'filter' => 'float32',
            'msg' => 'Invalid Data'
        ],
        'reason' => [
            'required' => 1,
            'filter' => 'trim',
            'msg' => 'Invalid Data'
        ],
];
return $rules;
