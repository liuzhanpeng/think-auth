<?php
namespace Lzpeng\Auth\Authenticators;

use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\AbstractAuthenticator;
use think\Session;
use think\Hook;

/**
 * 基于think\Session的用户认证器
 * 继承AbstractAuthenticator, 支持添加事件行为
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
    protected $sessionKey;

    /**
     * session对象
     * 
     * @var think\Session
     */
    protected $session;

    /**
     * 构造函数
     * 
     * @param string $name 认证器名称
     * @param string $sessionKey 会话key
     * @param think\Session thinkphp的Session对象
     * @param UserProvider $provider 用户认证对象提供器
     * @param think\Hook thinkphp的钩子对象
     * @return void
     */
    public function __construct(
        string $name, 
        string $sessionKey = 'UserIdentity', 
        Session $session, 
        UserProvider $provider, 
        Hook $hook
    ) {
        $this->sessionKey = $sessionKey;
        $this->session = $session;

        parent::__construct($name, $provider, $hook);
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

        $id = $this->getId();
        if (!is_null($id)) {
            return $this->provider->findById($id);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function persistUser(UserIdentity $user)
    {
        $this->session->set($this->sessionKey, $user->getId());
    }

    /**
     * @inheritDoc
     */
    public function cleanUser()
    {
        $this->session->delete($this->sessionKey);
    }
}