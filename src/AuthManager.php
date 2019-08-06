<?php
namespace Lzpeng\Auth;

use Lzpeng\Auth\Contracts\Authenticator;
use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\PasswordHasherContract;
use Lzpeng\Auth\Authenticators\SessionAuthenticator;
use Lzpeng\Auth\Authenticators\ApiTokenAuthenticator;
use Lzpeng\Auth\UserProviders\GenericModelUserProvider;
use Lzpeng\Auth\Hashers\DefaultPasswordHasher;
use think\Config;
use think\Container;

/**
 * 认证管理类
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class AuthManager
{
    /**
     * 当前认证器
     * 
     * @var Authenticator
     */
    private $authenticator;

    /**
     * 内部认证器列表
     *
     * @var array
     */
    private $innerAuthenticators = [
        'session' => SessionAuthenticator::class,
        'apiToken' => ApiTokenAuthenticator::class,
    ];

    /**
     * 内部用户提供器列表
     *
     * @var array
     */
    private $innerUserProviders = [
        'genericModel' => GenericModelUserProvider::class,
    ];
   
    /**
     * 内部密码hasher
     *
     * @var array
     */
    private $innerHashers = [
        'default' => DefaultPasswordHasher::class,
    ];

    /**
     * 构造函数
     *
     * @param Config $cfg 配置对象
     * @return void
     */
    public function __construct(Config $cfg)
    {
        $config = $cfg->get('auth.authenticator');

        $this->bindContracts($config);

        $this->authenticator = $this->getAuthenticator($config);
    }

    /**
     * 绑定实现到接口
     *
     * @param array $config
     * @return void
     */
    private function bindContracts(array $config)
    {
        if (!isset($config['driver'])) {
            throw new \Exception('无效authenticator配置');
        }
        $this->bindAuthenticator($config['driver']);

        if (!isset($config['provider']) || !isset($config['provider']['driver'])) {
            throw new \Exception('无效provider配置');
        }
        $this->bindUserProvider($config['provider']['driver']);

        if (isset($config['provider']['hasher'])) {
            $this->bindHasher($config['provider']['hasher']['driver']);
        } else {
            $this->bindHasher('default');
        }
    }

    /**
     * 根据配置信息绑定认证器
     *
     * @param string $driver 接口的实现驱动
     * @return void
     */
    private function bindAuthenticator(string $driver)
    {
        if (array_key_exists($driver, $this->innerAuthenticators)) {
            $authenticatorClass = $this->innerAuthenticators[$driver];
        } else {
            $authenticatorClass = $driver;
        }

        Container::getInstance()->bindTo(Authenticator::class, $authenticatorClass);
    }

    /**
     * 根据配置信息绑定用户提供器
     *
     * @param string $driver 接口的实现驱动
     * @return void
     */
    private function bindUserProvider(string $driver)
    {
        if (array_key_exists($driver, $this->innerUserProviders)) {
            $providerClass = $this->innerUserProviders[$driver];
        } else {
            $providerClass = $driver;
        }

        Container::getInstance()->bindTo(UserProvider::class, $providerClass);
    }

    /**
     * 绑定密码hasher
     *
     * @param string $driver 接口的实现驱动
     * @return void
     */
    private function bindHasher(string $driver)
    {
        if (array_key_exists($driver, $this->innerHashers)) {
            $hasherClass = $this->innerHashers[$driver];
        } else {
            $hasherClass = $driver;
        }

        Container::getInstance()->bindTo(PasswordHasherContract::class, $hasherClass);
    }

    /**
     * 根据配置信息获取认证器
     *
     * @param array $config
     * @return Authenticator
     */
    private function getAuthenticator(array $config)
    {
        if (isset($config['provider']['hasher'])) {
            Container::get(PasswordHasherContract::class, $config['provider']['hasher']);
        }

        Container::get(UserProvider::class, $config['provider']);
        return Container::get(Authenticator::class, $config);
    }

    /**
     * 调用authenticator的实例方法
     */
    public function __call($method, $arguments)
    {
        return $this->authenticator->{$method}(...$arguments);
    }
}