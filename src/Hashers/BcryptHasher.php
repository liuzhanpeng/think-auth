<?php
namespace Lzpeng\Auth\Hashers;

use Lzpeng\Auth\Contracts\HashContract;

class BcryptHasher implements HashContract
{
    /**
     * @inheritDoc
     */
    public function create(string $password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @inheritDoc
     */
    public function check(string $password, string $hash)
    {
        return password_verify($password, $hash);
    }
}