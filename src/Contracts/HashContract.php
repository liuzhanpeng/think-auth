<?php
namespace Lzpeng\Auth\Contracts;

/**
 * 密码hash接口
 */
interface HashContract
{
    /**
     * 生成hash密码
     * 
     * @param string $password 原始密码
     * @return string
     */
    public function create(string $password);

    /**
     * 检测密码hash值是否正确
     * 
     * @param string $password 待检查的密码
     * @param string $hash 密码hash值
     * @return bool
     */
    public function check(string $password, string $hash);
}