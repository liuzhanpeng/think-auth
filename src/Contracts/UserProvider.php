<?php
namespace Lzpeng\Auth\Contracts;

/**
 * 用户提供器接口
 */
interface UserProvider
{
    /**
     * 通过用户标识获取认证用户对象
     * 
     * @param mixed $id 用户标识
     * @return UserIdentity
     */
    public function findById($id);

    /**
     * 通过用户凭证获取认证用户对象
     * 
     * @param array $credentials 用户凭证
     * @return UserIdentity
     */
    public function findByCredentials(array $credentials);

    /**
     * 检查用户凭证是否有效
     * 
     * @param UserIdentity $user 认证用户对象
     * @param array $credentials 用户凭证
     * @return bool
     */
    public function validateCredentials(UserIdentity $user, array $credentials);
}