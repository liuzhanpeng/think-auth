<?php
namespace Tests\Users;

use Lzpeng\Auth\Contracts\UserIdentity;
use think\Model;

class User extends Model implements UserIdentity
{
    public function getId()
    {
        return $this->id;
    }

    public function getPassword()
    {
        return $this->password;
    }
}