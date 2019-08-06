<?php
namespace Lzpeng\Auth;

use Lzpeng\Auth\Contracts\Authenticator;
use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\UserIdentity;
use Lzpeng\Auth\Exceptions\InvalidCredentialsException;
use think\Hook;

/**
 * 认证器抽象类
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
abstract class AbstractAuthenticator implements Authenticator
{
    /**
     * 登录前事件名称 
     */
    const EVENT_LOGIN_BEFORE = 'login_before';

    /**
     * 登录后事件名称 
     */
    const EVENT_LOGIN_AFTER = 'login_after';

    /**
     * 登出前事件名称
     */
    const EVENT_LOGOUT_BEFORE = 'logout_before';

    /**
     * 登出后事件名称
     */
    const EVENT_LOGOUT_AFTER = 'logout_after';

    /**
     * 认证用户对象
     * 
     * @var UserIdentity
     */
    protected $user;

    /**
     * 认证用户对象提供器
     * 
     * @var UserProvider
     */
    protected $provider;

    /**
     * 钩子
     *
     * @var think\Hook
     */
    protected $hook;

    /**
     * 构造函数
     *
     * @param think\Hook $hook 钩子
     * @return void
     */
    public function __construct(UserProvider $provider, Hook $hook)
    {
        $this->provider = $provider;
        $this->hook = $hook;
    }

    /**
     * @inheritDoc
     */
    public function login(array $credentials)
    {
        $this->hook->listen(self::EVENT_LOGIN_BEFORE, $credentials);

        $user = $this->provider->findByCredentials($credentials);
        if (is_null($user) || !$this->provider->validateCredentials($user, $credentials)) {
            throw new InvalidCredentialsException('无效用户凭证');
        }

        $this->forceLogin($user);

        $this->hook->listen(self::EVENT_LOGIN_AFTER, $user);
    }

    /**
     * 登录逻辑
     * 
     * @param UserIdentity $user 认证用户对象
     * @return void
     */
    protected function forceLogin(UserIdentity $user)
    {
        throw new \Exception('未实现方法');
    }

    /**
     * @inheritDoc
     */
    public function isLogined()
    {
        return !is_null($this->getUser());
    }

    /**
     * 获取认证用户提供器
     *
     * @return UserProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * 设置认证用户提供器
     *
     * @param UserProvider $provider 认证用户提供器
     * @return void
     */
    public function setProvider(UserProvider $provider)
    {
        $this->provider = $provider;
    }

}