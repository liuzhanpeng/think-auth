<?php
namespace Lzpeng\Auth;

use Lzpeng\Auth\Contracts\Authenticator;
use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\UserIdentity;
use Lzpeng\Auth\Contracts\AuthBehavior;
use Lzpeng\Auth\Exceptions\AuthenticationException;
use think\Hook;

/**
 * 抽象认证类
 * 直接使用think\Hook机制监听认证过程中的相关事件
 * 自定义认证器可直接继承此类
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
abstract class AbstractAuthenticator implements Authenticator, AuthBehavior
{
    /**
     * 用户认证对象
     * 
     * @var UserIdentity
     */
    protected $user;

    /**
     * 认证器名称
     *
     * @var string
     */
    protected $name;

    /**
     * 用户认证对象提供器
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
     * @param string $name 认证器名称
     * @param UserProvider $provider 用户认证对象提供器
     * @param think\Hook $hook 钩子对象
     * @return void
     */
    public function __construct(string $name, UserProvider $provider, Hook $hook)
    {
        $this->name = $name;
        $this->provider = $provider;
        $this->hook = $hook;
    }

    /**
     * @inheritDoc
     */
    public function login(array $credentials)
    {
        $this->listen(self::EVENT_LOGIN_BEFORE, $credentials);

        try {
            $user = $this->provider->findByCredentials($credentials);
            if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
                $result = $this->persistUser($user);

                $this->listen(self::EVENT_LOGIN_SUCCESS, [
                    'user' => $user,
                    'result' => $result,
                ]);

                return $result;
            } else {
                throw new AuthenticationException('认证失败', 401);
            }
        } catch (AuthenticationException $exception) {
            $this->listen(self::EVENT_LOGIN_FAILURE, [
                'credentials' => $credentials,
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    /**
     * @inheritDoc
     */
    public function isLogined()
    {
        return !is_null($this->getUser());
    }

    /**
     * @inheritDoc
     */
    public function logout()
    {
        $this->listen(self::EVENT_LOGOUT_BEFORE, $this->getUesr());
        $this->cleanUser();
        $this->user = null;
        $this->listen(self::EVENT_LOGOUT_AFTER);
    }

    /**
     * @inheritDoc
     */
    public function setUser(UserIdentity $user)
    {
        try {
            $result = $this->persistUser($user);

            $this->listen(self::EVENT_LOGIN_SUCCESS, [
                'user' => $user,
                'result' => $result,
            ]);

            return $result;
        } catch (AuthenticationException $exception) {
            $this->listen(self::EVENT_LOGIN_FAILURE, [
                'credentials' => $credentials,
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    /**
     * 用户认证对象持久化逻辑
     *
     * @param UserIdentity $user
     * @return void
     */ 
    protected function persistUser(UserIdentity $user)
    {
        throw new \Exception('未实现方法');
    }

    /**
     * 清除用户认证对象
     *
     * @return void
     */
    protected function cleanUser()
    {
        throw new \Exception('未实现方法');
    }

    /**
     * @inheritDoc
     */
    public function attachBehavior(string $event, $behavior)
    {
        $events = [self::EVENT_LOGIN_BEFORE, self::EVENT_LOGIN_SUCCESS, self::EVENT_LOGIN_FAILURE, self::EVENT_LOGOUT_BEFORE, self::EVENT_LOGOUT_AFTER];
        if (!in_array($event, $events)) {
            throw new \Exception(sprintf('无效认证事件[%s]', $event));
        }

        $this->hook->add($this->getEventName($event), $behavior);
    }

    /**
     * 监听认证器事件
     *
     * @param string $event 事件名称
     * @param array | null $param 参数
     * @return void
     */
    private function listen(string $event, $param = null)
    {
        $this->hook->listen($this->getEventName($event), $param);
    }

    /**
     * 返回认证器唯一的事件名称
     *
     * @param string $event 事件名称
     * @return string
     */
    private function getEventName(string $event)
    {
        return $this->name . '_' . $event;
    }
}