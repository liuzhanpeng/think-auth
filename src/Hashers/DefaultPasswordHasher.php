<?php
namespace Lzpeng\Auth\Hashers;

use Lzpeng\Auth\Contracts\PasswordHasherContract;

/**
 * 默认密码hasher
 */
class DefaultPasswordHasher implements PasswordHasherContract
{
    /**
     * hash算法
     */
    private $algo;

    /**
     * 构造函数
     * 
     * @param int $algo 算法常量
     * @return void
     */
    public function __construct(int $algo = PASSWORD_DEFAULT)
    {
        $this->algo = $algo;
    }

    /**
     * @inheritDoc
     */
    public function hash(string $password)
    {
        return password_hash($password, $this->algo);
    }

    /**
     * @inheritDoc
     */
    public function check(string $password, string $hash)
    {
        return password_verify($password, $hash);
    }
}