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
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class SessionAuthenticator extends AbstractAuthenticator
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
     * 构造函数
     * 
     * @param string $sessionKey 会话key
     * @param think\Session thinkphp的Session对象
     * @param think\Hook thinkphp的钩子对象
     * @param UserProvider $provider 认证用户提供器
     * @return void
     */
    public function __construct(
        string $sessionKey = 'UserIdentity', 
        Session $session, 
        Hook $hook, 
        UserProvider $provider)
    {
        $this->sessionKey = $sessionKey;
        $this->session = $session;

        parent::__construct($provider, $hook);
    }

    /**
     * @inheritDoc
     */ 
    protected function validate(UserIdentity $user, array $credentials)
    {
        return $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * @inheritDoc
     */
    public function forceLogin(UserIdentity $user)
    {
        $this->session->set($this->sessionKey, $user->getId());
        $this->user = $user;

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        if (!is_null($this->user)) {
            return $this->user->getId();
        }

        if ($this->session->has($this->sessionKey)) {
            return $this->session->get($this->sessionKey);
        }

        return null;
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
            $user = $this->provider->findById($id);

            return $user;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function setUser(UserIdentity $user)
    {
        $this->user = $user;
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