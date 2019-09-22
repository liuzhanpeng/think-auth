<?php
namespace Lzpeng\Auth;

use Lzpeng\Auth\Contracts\Authenticator;
use Lzpeng\Auth\Contracts\AuthBehavior;
use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\Hasher;
use Lzpeng\Auth\Authenticators\SessionAuthenticator;
use Lzpeng\Auth\Authenticators\SimpleTokenAuthenticator;
use Lzpeng\Auth\UserProviders\ModelUserProvider;
use Lzpeng\Auth\UserProviders\DatabaseUserProvider;
use think\Container;

class AuthManager
{
    /**
     * 服务组件容器
     *
     * @var think\Container
     */
    protected $container;

    /**
     * 已创建的认证器实例列表
     *
     * @var array
     */
    protected $authenticators = [];

    /**
     * 自定义认证器创建者列表
     * 认证器创建者为Closure, 返回认证器实例
     *
     * @var array
     */
    protected $customAuthenticatorCreators = [];

    /**
     * 自定义用户提供器创建者列表
     *
     * @var array
     */
    protected $customUserProviderCreators = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->container = Container::getInstance();
    }

    /**
     * 创建认证器
     *
     * @param string | null $name 认证器配置标识
     * @return Authenticator
     * @throws Exception
     */
    public function make($name = null)
    {
        if (is_null($name)) {
            $name = $this->container->make('config')->get('auth.default');
            if (is_null($name)) {
                throw new \InvalidArgumentException('请配置默认认证器');
            }
        }

        // 如果已存在实例，直接返回
        if (isset($this->authenticators[$name]))  {
            return $this->authenticators[$name];
        }

        return $this->authenticators[$name] = $this->createAuthentiator($name);
    }

    /**
     * 注册自定义认证器创建者
     *
     * @param string $driver 认证器驱动
     * @param \Closure $callback 回调函数
     * @return void
     */
    public function registerAuthenticatorCreator(string $driver, \Closure $callback)
    {
        $this->customAuthenticatorCreators[$driver] = $callback;
    }

    /**
     * 注册自定义用户提供器创建者
     *
     * @param string $driver 用户提供器驱动
     * @param \Closure $callback 回调函数
     * @return void
     */
    public function registerUserProviderCreator(string $driver, \Closure $callback)
    {
        $this->customUserProviderCreators[$driver] = $callback;
    }

    /**
     * 获取指定标识的认证器配置
     *
     * @param string $name 认证器标识
     * @return array
     */
    private function getAuthenticatorConfig(string $name)
    {
        $config = $this->container->make('config')->get('auth.');
        
        if (is_null($config)) {
            throw new \InvalidArgumentException('找不到配置auth');
        }

        if (!isset($config['authenticators'][$name])) {
            throw new \InvalidArgumentException(sprintf('认证器配置[%s]无效', $name));
        }

        return $config['authenticators'][$name];
    }

    /**
     * 创建认证器
     *
     * @param string $name 认证器配置标识
     * @return Authenticator
     */
    private function createAuthentiator(string $name)
    {
        $config = $this->getAuthenticatorConfig($name);

        if (!isset($config['driver'])) {
            throw new \InvalidArgumentException(sprintf('请配置认证器[%s]驱动driver', $name));
        }

        $driver = $config['driver'];
        if (isset($this->customAuthenticatorCreators[$driver])) {
            $authenticator = $this->customAuthenticatorCreators[$driver]($this->container, $name, $config);
            if (!$authenticator instanceof Authenticator) {
                throw new \InvalidArgumentException(sprintf('自定义认证器驱动[%s]未实现Authenticator接口', $driver));
            }

            return $authenticator;
        }

        switch ($driver) {
            case 'session':
                return $this->createSessionAuthenticator($name, $config);
            case 'simpleToken':
                return $this->createTokenAuthenticator($name, $config);
            default:
                throw new \InvalidArgumentException(sprintf('不支持的认证器驱动[%s]', $driver));
        }
    }

    /**
     * 创建SessionAuthenticator
     *
     * @param string $name 认证器标识
     * @param array $config 配置
     * @return SessionAuthenticator
     */
    private function createSessionAuthenticator(string $name, array $config)
    {
        if (!isset($config['sessionKey'])) {
            throw new \InvalidArgumentException('找不到配置sessionKey');
        }

        $userProvider = $this->createUserProvider($config['provider']);

        $authenticator = new SessionAuthenticator(
            $name, 
            $config['sessionKey'], 
            $this->container->make('session'),
            $userProvider,
            $this->container->make('hook')
        );

        if (isset($config['behaviors'])) {
            $this->attachBehaviors($authenticator, $config['behaviors']);
        }

        return $authenticator;
    }

    /**
     * 创建Tokenthenticator
     *
     * @param string $name 认证器标识
     * @param array $config 配置
     * @return SimpleTokenAuthenticator
     */
    private function createTokenAuthenticator(string $name, array $config)
    {
        if (!isset($config['tokenKey'])) {
            throw new \InvalidArgumentException('找不到配置tokenKey');
        } 

        $userProvider = $this->createUserProvider($config['provider']);

        $authenticator = new SimpleTokenAuthenticator(
            $name, 
            $config['tokenKey'], 
            new \think\Cache($config['cache'] ?? []),
            $this->container->make('request'),
            $userProvider,
            $this->container->make('hook')
        );

        if (isset($config['behaviors'])) {
            $this->attachBehaviors($authenticator, $config['behaviors']);
        }

        return $authenticator;
    }

    /**
     * 认证器附加行为
     *
     * @param Authenticator $authenticator 认证器
     * @param array $behaviors 行为配置数组
     * @return void
     */
    private function attachBehaviors(Authenticator $authenticator, array $behaviors)
    {
        if ($authenticator instanceof AuthBehavior) {
            foreach ($behaviors as $event => $items) {
                foreach ($items as $item) {
                    $authenticator->attachBehavior($event, $item);
                }
            }
        }
    }

    /**
     * 创建用户提供器
     *
     * @param array $config 配置
     * @return UserProvider
     */
    private function createUserProvider(array $config)
    {
        $driver = $config['driver'];
        if (isset($this->customUserProviderCreators[$driver])) {
            $userProvider = $this->customUserProviderCreators[$driver]($this->container, $config);
            if (!$userProvider instanceof UserProvider) {
                throw new \InvalidArgumentException(sprintf('自定义用户提供器驱动[%s]未实现UserProvider接口', $driver));
            }

            return $userProvider;
        }
    
        switch ($driver) {
            case 'model':
                return $this->createModelUserProvider($config);
            case 'database':
                return $this->createDatabaseUserProvider($config);
            default:
                throw new \InvalidArgumentException(sprintf('不支持的用户提供器驱动配置[%s]', $driver));
        }
    }

    /**
     * 创建ModelUserProvider
     *
     * @param array $config 配置
     * @return ModelUserProvider
     */
    private function createModelUserProvider(array $config)
    {
        if (!isset($config['modelClass'])) {
            throw new \InvalidArgumentException(sprintf('用户提供器[%s]缺少配置项modelClass', $config['driver']));
        }

        $modelClass = $config['modelClass'];
        $idKey = $config['idKey'] ?? 'id';
        $passwordKey = $config['passwordKey'] ?? 'password';
        $forceValidatePassword = $config['forceValidatePassword'] ?? true;

        $hasher = $this->createHasher($config['hasher']);

        return new ModelUserProvider($modelClass, $idKey, $passwordKey, $forceValidatePassword, $hasher);
    }

    /**
     * 创建DatabaseUserProvider
     *
     * @param array $config 配置
     * @return DatabaseUserProvider
     */
    private function createDatabaseUserProvider(array $config)
    {
        if (!isset($config['table'])) {
            throw new \InvalidArgumentException(sprintf('用户提供器[%s]缺少配置项table', $config['driver']));
        }
        if (!isset($config['hasher'])) {
            throw new \InvalidArgumentException(sprintf('用户提供器[%s]缺少配置项hasher', $config['driver']));
        }

        $table = $config['table'];
        $idKey = $config['idKey'] ?? 'id';
        $passwordKey = $config['passwordKey'] ?? 'password';
        $forceValidatePassword = $config['forceValidatePassword'] ?? true;

        $hasher = $this->createHasher($config['hasher']);

        return new DatabaseUserProvider($table, $idKey, $passwordKey, $forceValidatePassword, $hasher);
    }

    /**
     * 创建hasher
     *
     * @param mixed $config 配置
     * @return Hasher
     */
    private function createHasher($config)
    {
        $driver = $config['driver'];
        if (substr($driver, 0, 1) === '\\') {
            $className = $driver;
        } else {
            $driver = ucfirst($driver);
            $className = "\\Lzpeng\\Auth\\Hashers\\{$driver}Hasher";
        }

        unset($config['driver']);

        return $this->container->make($className, $config);
    }

    /**
     * 调用默认authenticator的实例方法
     * 
     * @param string $method 方法名称
     * @param array $arguments 方法参数
     */
    public function __call($method, $arguments)
    {
        return $this->make()->{$method}(...$arguments);
    }
}