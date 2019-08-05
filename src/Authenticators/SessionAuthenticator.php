<?php
namespace Lzpeng\Auth\Authenticators;

use Lzpeng\Auth\Contracts\Authenticator;
use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\UserIdentity;
use Lzpeng\Auth\AbstractAuthenticator;
use think\Session;
use think\Hook;

/**
 * 基于session的用户认证器
 */
class SessionAuthenticator implements AbstractAuthenticator
{
    /**
     * 会话key
     * 
     * @var string
     */
    private $sessionKey;

    /**
     * session对象
     * 
     * @var think\Session
     */
    private $session;

    /**
     * 钩子
     * 
     * @var think\Hook
     */
    private $hook;

    /**
     * 构造函数
     * 
     * @param string $sessionKey 会话key
     * @param think\Session thinkphp的Session对象
     * @param think\Hook thinkphp的Hook对象
     * @param UserProvider $provider 认证用户对象提供器
     * @return void
     */
    public function __construct(string $sessionKey, Session $session, Hook $hook, UserProvider $provider)
    {
        $this->sessionKey = $sessionKey;
        $this->session = $session;
        $this->hook = $hook;
        $this->provider = $provider;
    }

    /**
     * @inheritDoc
     */
    public function forceLogin(UserIdentity $user)
    {
        $this->session->set($this->sessionKey, $user->getId());
        $this->user = $user;
    }

    /**
     * @inheritDoc
     */
    public function getUser()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        if ($this->session->has($this->sessionKey)) {
            $id = $this->session->get($this->sessionKey);
            $user = $this->provider->findById();

            return $user;
        }
    }

    /**
     * @inheritDoc
     */
    public function logout()
    {
        $this->session->delete($this->sessionKey);
        $this->user = null;
    }
}