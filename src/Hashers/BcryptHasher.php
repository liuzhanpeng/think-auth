<?php
namespace Lzpeng\Auth\Hashers;

use Lzpeng\Auth\Contracts\Hasher;

/**
 * 基于bcrypt算法的hash处理器
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class BcryptHasher implements Hasher
{
    /**
     * @inheritDoc
     */
    public function hash(string $value)
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }

    /**
     * @inheritDoc
     */
    public function check(string $value, string $hashedValue)
    {
        return password_verify($value, $hashedValue);
    } 
}