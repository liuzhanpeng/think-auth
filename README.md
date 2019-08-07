# think-auth

基于thinkphp5.1的认证系统

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
composer require peng/think-auth=dev-master
```

## 配置

项目config目录下创建auth.php配置文件

```
return [
    'authenticator' => [ 
        // 内置认证器有两种: 
        // 1. session为基于session认证器, 一般用于账号/密码登录
        'driver' => 'session',
        // session key
        'sessionKey' => 'UserIdentity',
        'provider' => [
            // 只有一种内置用户提供器 genericModel
            'driver' => 'genericModel',
            // 实现UserIdentity接口的模型类
            'model' => '\app\model\User',
            // 密码hasher
            'hasher' => [
                // 内置只有default
                'driver' => 'default',
                // 只支持PASSWORD_DEFAULT和PASSWORD_BCRYPT
                'algo' => PASSWORD_DEFAULT,
            ]
        ],

        // 2. apiToken为基于api token的认证器
        'driver' => 'apiToken',
        // http头token的名称
        'tokenKey' => 'token',
        // token缓存时间
        'cacheExpire' => 1200,
        'provider' => [
            'driver' => 'genericModel',
            'model' => '\app\model\User',
        ],
    ]
]
```

## 使用例子

```
use Lzpeng\Auth\Auth;

$result = Auth::Login([
    'username' => 'test',
    'password' => 'password',
]);
if (!$result->isValid()) {
    // 登录失败
}
// 如果是apiToken认证器，可以通过 $result->token 获取内部生成的token
```

## API

Auth::login(array $credentials) : Result;   // 通过用户凭证登录

Auth::isLogined() : bool;                          // 判断当前用户是否已登录

Auth::getUser() : UserIdentity;                            // 获取当前认证用户对象，未登录返回null

Auth::logout();                             // 当前用户登出

Auth::setUser(UserIdentity $user);                       // 直接认证用户对象，用于实现单次登录

## 扩展

通过实现Lzpeng\Auth\Contracts\Authenticator接口可以实现自定义认证器

通过实现Lzpeng\Auth\Contracts\UserProvider接口可以实现自定义用户提供器

通过实现Lzpeng\Auth\Contracts\UserIdentity接口可以实现自定义用户对象

通过实现Lzpeng\Auth\Contracts\PasswordHasherContract接口可以实现自定义密码hasher

