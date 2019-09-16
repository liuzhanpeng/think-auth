<?php
namespace Lzpeng\Auth\Contracts;

/**
 * 认证行为接口
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
interface AuthBehavior
{
    /**
     * 登录前事件名称 
     */
    const EVENT_LOGIN_BEFORE = 'login_before';

    /**
     * 登录成功事件名称 
     */
    const EVENT_LOGIN_SUCCESS = 'login_success';

    /**
     * 登录失败事件名称 
     */
    const EVENT_LOGIN_FAILED = 'login_failed';

    /**
     * 登出前事件名称
     */
    const EVENT_LOGOUT_BEFORE = 'logout_before';

    /**
     * 登出后事件名称
     */
    const EVENT_LOGOUT_AFTER = 'logout_after';

    /**
     * 添加行为
     *
     * @param string $event 事件名称
     * @param mixed $behavior 添加的行为
     * @return void
     */
    public function addBehavior(string $event, $behavior);
}