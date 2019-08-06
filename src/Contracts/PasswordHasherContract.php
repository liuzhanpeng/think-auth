<?php
namespace Lzpeng\Auth\Contracts;

/**
 * 密码hash处理器接口
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
interface PasswordHasherContract
{
    /**
     * hash密码
     * 
     * @param string $password 原始密码
     * @return string
     */
    public function hash(string $password);

    /**
     * 检测密码hash值是否正确
     * 
     * @param string $password 待检查的密码
     * @param string $hash 密码hash值
     * @return bool
     */
    public function check(string $password, string $hash);
}