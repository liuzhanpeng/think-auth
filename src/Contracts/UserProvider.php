<?php
namespace Lzpeng\Auth\Contracts;

/**
 * 用户提供器接口
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
interface UserProvider
{
    /**
     * 通过用户标识获取认证用户对象
     * 用于通过凭证验证后获取用户对象
     * 
     * @param mixed $id 用户标识
     * @return UserIdentity | null
     */
    public function findById($id);

    /**
     * 通过用户凭证获取认证用户对象
     * 
     * @param array $credentials 用户凭证
     * @return UserIdentity | null
     */
    public function findByCredentials(array $credentials);

    /**
     * 检查用户凭证是否有效
     * 某些凭证信息(如密码)可能进行了hash处理，需要接口提供方法给用户实现具体hash验证逻辑
     * 
     * @param UserIdentity $user 认证用户对象
     * @param array $credentials 用户凭证
     * @return bool
     */
    public function validateCredentials(UserIdentity $user, array $credentials);
}