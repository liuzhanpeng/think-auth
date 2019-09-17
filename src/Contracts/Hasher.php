<?php
namespace Lzpeng\Auth\Contracts;

/**
 * 密码哈希处理器
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
interface Hasher
{
    /**
     * 将字符串hash
     * 
     * @param string $value 待hash的原始字符串
     * @return string
     */
    public function hash(string $value);

    /**
     * 检测字符串hash值是否正确
     * 
     * @param string $value 待检查的字符串
     * @param string $hashedValue 待比较的hash值
     * @return bool
     */
    public function check(string $value, string $hashedValue);
}