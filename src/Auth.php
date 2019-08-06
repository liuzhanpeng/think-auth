<?php
namespace Lzpeng\Auth;

use think\Facade;

class Auth extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Lzpeng\Auth\AuthManager';
    }
}