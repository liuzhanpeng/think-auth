# think-auth

**1.0 版本已废弃, 请使用1.1版本**

基于thinkphp5.1的用户认证系统。

## 安装

composer.json 添加以下内容

```
"repositories": [
    {
        "type": "git",
        "url": "http://172.0.6.108:3000/lzpeng/think-auth"
    }
],

// 设置非安全链接
"config": {
    ...
    "secure-http": false
}
```

然后命令行运行

```
composer require lzpeng/think-auth=1.1.*
```

## 配置

在项目应用或模块的config目录下创建auth.php配置文件

```php
return [
    'default' => 'test1',                                   // 默认使用的认证器标识

    'test1' => [                                        // 认证器标识
        'driver' => 'session',                          // 认证器驱动; 内置支持session和simpleToken
        'sessionKey' => 'UserIdentity',                 // 会话key
        'provider' => [                                 // 认证所使用的用户提供器
            'driver' => 'model',                        // 用户提供器驱动; 内置支持model和database
            'modelClass' => 'app\common\model\User',    // 模型类; 需要实现Lzpeng\Auth\Contracts\UserIdentity接口
            'idKey' => 'id',                            // 模型对应的用户标识属性名称; 可选; 默认为'id'
            'passwordKey' => 'password',                // 用户凭证数组里的密码key; 可选; 默认为'password'
            'forceValidatePassword' => true,            // 是否强制验证密码; 如果是ture但没传入密码凭证，凭证就验证失败; false的话不传密码凭证就忽略密码凭证的验证; 可选; 默认为true
            'hasher' => [
                'driver' => 'bcrypt',                    // 密码hasher; 内置只支持bcrypt

                // 'driver' => '\test\HMacHasher'        // 只支持自定义driver; 需实现Lzpeng\Auth\Contracts\Hasher接口
                // 'algo' => 'md5',
                // 'salt' => 'xxxxxx',
            ]
        ],

        // 以下是simpleToken认证器配置例子
        // 'driver' => 'simpleToken',
        // 'tokenKey' => 'User-Token',             // token名称
        // 'cache' => [                            // 缓存配置; 支持thinkphp的缓存配置 可选; 不设置使用框架的cache配置;
        //     'type'   => 'File',
        //     'path'   => '',
        //     'prefix' => '',
        //     'expire' => 1200,
        // ],

        // 以下的database用户提供器配置例子
        // 'provider' => [
        //     'driver' => 'database',
        //     'table' => 'user',
        //     'passwordKey' => 'password',                // 用户凭证数组里的密码key; 可选; 默认为'password'
        //     'forceValidatePassword' => true,            // 是否强制验证密码; 如果是ture但没传入密码凭证，凭证就验证失败; false的话不传密码凭证就忽略密码凭证的验证; 可选; 默认为true
        //     'hasher' => [
        //         'driver' => 'bcrypt',                    // 密码hasher; 内置只支持bcrypt
        //     ]
        // ]

        'behaviors' => [                                    // 认证过程中的行为绑定; 实现了AuthBehavior接口
            'login_before' => [
                'app\behaivor\checkAttempt',
            ],
            'login_success' => [],
            'login_failure' => [],
            'logout_before' => [],
            'logout_after' => [],
        ]

    ],

    'test2' => [
        ...
    ],
]
```

## 使用例子

用户凭证登录:

```php
use Lzpeng\Auth\Auth;
use Lzpeng\Auth\Exceptions\AuthenticationException;

try {
    $result = Auth::login(['username' => 'test', 'password' => 'password']);
    // session认证器，$result返回为null
    // simpleToken认证器，$result返回为token, 用于返回给客户端

    // 认证成功处理逻辑
} catch (AuthenticationException $ex) {
    // 异常处理
}
```

跳过认证，直接设置认证用户

```php
try {
    $user = fromOtherSystem($id);
    $result = Auth::setUser($user);
    // 认证成功处理逻辑
} catch (AuthenticationException $ex) {
    // 异常处理
}
```

判断当前用户是否已登录:

```php
if (Auth::isLogined()) {
    // 用户已认证登录
}
```

获取当前用户标识

```php
$id = Auth::getId();     // 如果未认证登录将返回null
```

获取当前用户对象：

```php
$user = Auth::getUser();    // 如果未认证登录将返回null
```

用户登出:

```php
Auth::logout(); 
```

多认证器使用:
用于单一模块有多个用户系统的时候

```php
Auth::make('test1')->login(['username' => 'xxx', 'password' = 'xxx']);
Auth::make('test2')->login(['admin' => 'xxx', 'password' => 'xxxx]);

$user = Auth::make('test1')->getUser();
$admin = Auth::make('test2')->getUser();
```

## 扩展

通过实现Lzpeng\Auth\Contracts\Authenticator接口可以实现自定义认证器

通过实现Lzpeng\Auth\Contracts\UserProvider接口可以实现自定义用户提供器

通过实现Lzpeng\Auth\Contracts\UserIdentity接口可以实现自定义用户对象
