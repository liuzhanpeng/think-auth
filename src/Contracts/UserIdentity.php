<?php
namespace Lzpeng\Auth\Contracts;

/**
 * 认证用户对象接口
 */
interface UserIdentity
{
    /**
     * 获取用户标识
     * 
     * @return mixed
     */
    public function getId();

    /**
     * 获取用户密码
     * 密码比较特别, 一般需要hash检查
     * 
     * @return string
     */
    public function getPassword();
}