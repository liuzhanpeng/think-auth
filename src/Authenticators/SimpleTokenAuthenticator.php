<?php
namespace Lzpeng\Auth\Authenticators;

use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\UserIdentity;
use Lzpeng\Auth\AbstractAuthenticator;
use think\Request;
use think\Cache;
use think\Hook;

/**
 * 简单的api token用户认证器
 * 内部使用think\Cache类做用户认证对象持久化, 并支持独立配置
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class SimpleTokenAuthenticator extends AbstractAuthenticator
{
    /**
     * token名称
     * 
     * @var string
     */
    protected $tokenKey;

    /**
     * 缓存
     * 
     * @var think\Cache;
     */
    protected $cache;

    /**
     * 请求对象
     * 
     * @var think\Request
     */
    protected $request;

    /**
     * 构造函数
     * 
     * @param string $name 认证器名称
     * @param string $tokenKey token名称
     * @param think\Cache $cache 缓存
     * @param think\Request $request 请求对象
     * @param UserProvider $provider 用户认证对象提供器
     * @param think\Hook $hook 钩子
     * @return void
     */
    public function __construct(
        string $name,
        string $tokenKey = 'User-Token', 
        Cache $cache, 
        Request $request, 
        UserProvider $provider,
        Hook $hook
    ) {
        $this->tokenKey = $tokenKey;
        $this->cache = $cache;
        $this->request = $request;

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

        $token = $this->getRequestToken();
        if (empty($token)) {
            return null;
        }

        if ($this->cache->has($token)) {
            return $this->cache->get($token);
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

        $token = $this->getRequestToken();
        if (empty($token)) {
            return;
        }
        $id = $this->getId();
        if (!is_null($id)) {
            $user = $this->provider->findById($id);

            // 更新过期时间
            $this->cache->set($token, $user->id);

            return $user;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function persistUser(UserIdentity $user)
    {
        $token = $this->generateToken();

        $this->cache->set($token, $user->id);

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function cleanUser()
    {
        $token = $this->getRequestToken();
        if (empty($token)) {
            return;
        }

        $this->cache->rm($token);
    }

    /**
     * 从请求对象中查找token
     * 需要不同的获取方式，可直接继承类重写此方法
     *
     * @return string|null
     */
    protected function getRequestToken()
    {
        return $this->request->header($this->tokenKey);
    }

    /**
     * 生成令牌
     * 需要不同的生成方式，可直接继承类重写此方法
     *
     * @return string
     */
    protected function generateToken()
    {
        return hash_hmac('sha1', uniqid(microtime(true), true), '');
    }
}