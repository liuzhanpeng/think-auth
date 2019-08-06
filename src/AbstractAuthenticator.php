<?php
namespace Lzpeng\Auth;

use Lzpeng\Auth\Contracts\Authenticator;
use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\UserIdentity;
use think\Hook;

/**
 * 抽象认证类
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
     * 登录成功事件名称 
     */
    const EVENT_LOGIN_SUCCESS = 'login_success';

    /**
     * 登录失败事件名称 
     */
    const EVENT_LOGIN_FAILED = 'login_failed';

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
     * thinkphp的钩子对象
     * 
     * @var think\Hook
     */
    protected $hook;

    /**
     * 构造函数
     *
     * @param UserProvider $provider 认证用户提供器
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
        if (!is_null($user) && $this->validate($user, $credentials)) {
            $this->forceLogin($user);

            $this->hook->listen(self::EVENT_LOGIN_SUCCESS, [
                'credentials' => $credentials,
                'user' => $user,
            ]);
            return true;
        }

        $this->hook->listen(self::EVENT_LOGIN_FAILED, $credentials);
        return false;
    }

    /**
     * 验证逻辑
     *
     * @param UserIdentity $user 认证用户对象
     * @param array $credentials 用户凭证
     * @return bool
     */
    protected function validate(UserIdentity $user, array $credentials)
    {
        return false;
    }

    /**
     * 保存认证对象逻辑
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