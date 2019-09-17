<?php
return [
    'default' => 'admin',   // 默认使用的认证器标识

    'authenticators' => [

        'admin' => [                                            // 认证器标识
            'driver'     => 'session',                          // 认证器驱动; 内置支持 sesison 和 token
            'sessionKey' => 'AdminIdentity',                    // 会话key
            'provider'   => [                                   // 用户提供器配置
                'driver'     => 'model',                        // 用户提供器驱动; 内置支持 model 和 database
                'modelClass' => 'app\common\model\Admin',       // 实现UserIdentity接口的模型类
                'hasher' => [
                    'driver' => 'bcrypt'
                ]
            ],
            'behaviors' => [                                    // 认证过程中的行为绑定; 实现了AuthBehavior接口
                'login_before' => [
                    'app\behaivor\checkAttempt',
                ],
                'login_success' => [],
                'login_fail' => [],
                'logout_before' => [],
                'logout_after' => [],
            ]
        ],

        'user' => [
            'driver' => 'token',
            'tokenKey' => 'User-Token',
            'provider' => [
                'driver' => 'database',
                'table' => 'user',              // 表名
                'idKey' => 'id',                // 用户标识key
                'passwordKey' => 'password',       
            ],
            'cache' => [    // 与think\Cache配置相同
                // 驱动方式
                'type'   => 'File',
                // 缓存保存目录
                'path'   => '',
                // 缓存前缀
                'prefix' => '',
                // 缓存有效期 0表示永久缓存
                'expire' => 0,
            ]
        ]

    ]
];