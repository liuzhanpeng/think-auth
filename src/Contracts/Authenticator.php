<?php
namespace Lzpeng\Auth\Contracts;

/**
 * 认证器接口
 */
interface Authenticator
{
    /**
     * 登录验证
     * 
     * @param array $credentials 认证凭证
     * @return bool
     */
    public function login(array $credentials);

    /**
     * 获取当前认证用户对象
     * 
     * @return UserIdentity|null
     */
    public function getUser();

    /**
     * 判断当前用户是否已通过认证
     * 
     * @return bool
     */
    public function isLogined();

    /**
     * 登出
     * 
     * @return void
     */
    public function logout();
}