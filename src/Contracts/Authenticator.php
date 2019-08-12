<?php
namespace Lzpeng\Auth\Contracts;

/**
 * 认证器接口
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
interface Authenticator
{
    /**
     * 登录验证
     * 
     * @param array $credentials 认证凭证
     * @return Result
     */
    public function login(array $credentials);

    /**
     * 获取用户标识
     *
     * @return mixed
     */
    public function getId();

    /**
     * 获取当前认证用户对象
     * 
     * @return UserIdentity|null
     */
    public function getUser();

    /**
     * 设置当前认证用户对象
     *
     * @param UserIdentity $user 认证用户对象
     * @return void
     */
    public function setUser(UserIdentity $user);

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