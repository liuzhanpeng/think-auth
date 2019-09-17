<?php
namespace Lzpeng\Auth;

use think\Facade;

/**
 * 认证器管理器Facade
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class Auth extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Lzpeng\Auth\AuthManager';
    }
}