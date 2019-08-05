<?php
namespace Lzpeng\Auth;

use Lzpeng\Auth\Contracts\Authenticator;

/**
 * 
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
     * @inheritDoc
     */
    public function login(array $credentials)
    {
        $this->hook->listen(self::EVENT_LOGIN_BEFORE, $credentials);

        $user = $this->provider->findByCredentials($credentials);
        if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            $this->forceLogin($user);

            return true;
        }

        $this->hook->listen(self::EVENT_LOGIN_AFTER, $user);

        return false;
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
}