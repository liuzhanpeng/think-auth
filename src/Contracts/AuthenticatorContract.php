<?php
namespace Lzpeng\Auth\Contracts;

use Lzpeng\Auth\Exceptions\AuthenticationException;

/**
 * 认证器接口
 * 负责处理用户认证对象的持久化逻辑; 认证业务逻辑由UserProvider处理
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
interface AuthenticatorContract
{
    /**
     * 认证登录
     *
     * @param array $credentials 用户凭证
     * @return UserIdentity 返回用户认证对象
     * @throws AuthenticationException
     */
    public function login(array $credentials);

    /**
     * 判断当前用户是否已认证
     *
     * @return boolean
     */
    public function isLogined();

    /**
     * 获取用户标识
     * 当前用户未认证将返回null
     *
     * @return mixed
     */
    public function getId();

    /**
     * 获取当前用户认证对象
     * 当前用户未认证将返回false
     *
     * @return UserIdentity | null
     */
    public function getUser();

    /**
     * 登出
     *
     * @return void
     */
    public function logout();
}