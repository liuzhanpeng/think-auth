<?php
namespace Lzpeng\Auth\Middlewares;

use Lzpeng\Auth\Auth;
use Lzpeng\Auth\Exceptions\AuthenticationException;
use think\Request;

/**
 * 认证检查
 */
class AuthenticationCheck
{
    public function handle(Request $request, \Closure $next)
    {
        if (Auth::isLogined()) {
            throw new AuthenticationException('认证失败');
        }

        return $next($request);
    }
}