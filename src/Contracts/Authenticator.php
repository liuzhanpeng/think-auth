<?php
namespace Lzpeng\Auth\Contracts;

use Lzpeng\Auth\Contracts\UserIdentity;
use Lzpeng\Auth\Exceptions\AuthenticationException;

/**
 * 认证器接口
 * 负责处理用户认证对象的持久化逻辑; 认证业务逻辑由UserProvider处理
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
interface Authenticator
{
    /**
     * 认证登录
     *
     * @param array $credentials 用户凭证
     * @return mixed 根据不同需要返回不同的数据; 例如: SimpleTokenAuthenticator返回token
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
     * 跳过认证，直接设置认证用户
     *
     * @param UserIdentity $user
     * @return mixed
     * @throws AuthenticationException
     */
    public function setUser(UserIdentity $user);

    /**
     * 登出
     *
     * @return void
     */
    public function logout();
}