<?php
namespace Lzpeng\Auth;

use Lzpeng\ServiceProvider\AbstractServiceProvider;
use Lzpeng\Auth\AuthManager;

/**
 * 认证服务组件提供器
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class AuthServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->container->bindTo(AuthManager::class, function() {

            return new AuthManager();
        });
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {

    }
}