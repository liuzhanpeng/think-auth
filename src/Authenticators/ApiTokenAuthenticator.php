<?php
namespace Lzpeng\Auth\Authenticators;

use Lzpeng\Auth\Contracts\Authenticator;
use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\UserIdentity;
use Lzpeng\Auth\AbstractAuthenticator;
use think\Request;
use think\Cache;
use think\Hook;

/**
 * 基于请求头token的用户认证器
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class ApiTokenAuthenticator extends AbstractAuthenticator
{
    /**
     * token名称
     * 
     * @var string
     */
    private $tokenKey;

    /**
     * 缓存时间
     *
     * @var int
     */
    private $cacheExpire;

    /**
     * 请求对象
     * 
     * @var think\Request
     */
    private $request;

    /**
     * 缓存
     * 
     * @var think\Cache;
     */
    private $cache;

    /**
     * 构造函数
     * 
     * @param string $tokenKey token名称
     * @param int $cacheExpire 缓存时间
     * @param think\Request $request 请求对象
     * @param think\Cache $cache 缓存
     * @param think\Hook $hook 钩子
     * @param UserProvider $provider 认证用户对象提供器
     * @return void
     */
    public function __construct(
        string $tokenKey, 
        int $cacheExpire, 
        Request $request, 
        Cache $cache, 
        Hook $hook, 
        UserProvider $provider)
    {
        $this->tokenKey = $tokenKey;
        $this->cacheExpire = $cacheExpire;
        $this->request = $request;
        $this->cache = $cache;

        parent::__construct($provider, $hook);
    }

    /**
     * @inheritDoc
     */
    protected function validate(UserIdentity $user, array $credentials)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function forceLogin(UserIdentity $user)
    {
        $token = $this->generateToken();

        $this->cache->set($token, $user->id, $this->cacheExpire);
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

        $token = $this->getRequestToken();
        if (empty($token)) {
            return;
        }

        if (!$this->cache->has($token)) {
            return;
        }

        $id = $this->cache->get($token);
        $user = $this->provider->findById($id);

        return $user;
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
        $token = $this->getRequestToken();
        if (empty($token)) {
            return;
        }

        $this->cache->rm($token);
        $this->user = null;
    }

    /**
     * 从请求查找token
     *
     * @return string|null
     */
    private function getRequestToken()
    {
        return $this->request->header($this->tokenKey);
    }

    /**
     * 生成令牌
     *
     * @return string
     */
    private function generateToken()
    {
        return hash_hmac('sha1', uniqid(microtime(true), true), '');
    }
}