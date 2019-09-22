<?php
namespace Tests;

use Lzpeng\Auth\Authenticators\SessionAuthenticator;
use Lzpeng\Auth\Contracts\AuthBehavior;
use Lzpeng\Auth\UserProviders\ModelUserProvider;
use Lzpeng\Auth\Hashers\BcryptHasher;
use Lzpeng\Auth\Exceptions\AuthenticationException;
use Tests\Users\User;

class SessionAuthenticatorTest extends BaseTestCase
{
    public function testLogin()
    {
        $authenticator = $this->getAuthenticator('test1', 'UserIdentity');

        $this->assertFalse($authenticator->isLogined());
        $this->assertNull($authenticator->getId());
        $this->assertNull($authenticator->getUser());

        $authenticator->login([
            'username' => 'test',
            'password' => '123654'
        ]);

        $this->assertTrue($authenticator->isLogined());
        $this->assertEquals($authenticator->getId(), 1);
        $this->assertInstanceOf(User::class, $authenticator->getUser());
        $this->assertEquals($authenticator->getUser()->username, 'test');

        $authenticator->logout();

        $this->assertFalse($authenticator->isLogined());
        $this->assertNull($authenticator->getId());
        $this->assertNull($authenticator->getUser());
    }

    public function testLoginWithWrongUsername()
    {
        $this->expectException(AuthenticationException::class);
        $authenticator = $this->getAuthenticator('test1', 'UserIdentity');

        $authenticator->login([
            'username' => 'wrong',
            'password' => '123654'
        ]);

        $this->assertFalse($authenticator->isLogined());
        $this->assertNull($authenticator->getId());
        $this->assertNull($authenticator->getUser());
    }

    public function testLoginWithWrongPassword()
    {
        $this->expectException(AuthenticationException::class);
        $authenticator = $this->getAuthenticator('test1', 'UserIdentity');

        $authenticator->login([
            'username' => 'test',
            'password' => 'wrong'
        ]);

        $this->assertFalse($authenticator->isLogined());
        $this->assertNull($authenticator->getId());
        $this->assertNull($authenticator->getUser());
    }

    public function testSetUser()
    {
        $user = User::get(1);
        $authenticator = $this->getAuthenticator('test1', 'UserIdentity');
        $authenticator->setUser($user);

        $this->assertTrue($authenticator->isLogined());
        $this->assertEquals($authenticator->getId(), 1);
        $this->assertInstanceOf(User::class, $authenticator->getUser());
        $this->assertEquals($authenticator->getUser()->username, 'test');
    }

    public function testMultipleAuthenticator()
    {
        $userAuthenticator = $this->getAuthenticator('test1', 'UserIdentity');
        $adminAuthenticator = $this->getAuthenticator('test2', 'AdminIdentity');

        $userAuthenticator->login([
            'username' => 'test',
            'password' => '123654'
        ]);

        $this->assertTrue($userAuthenticator->isLogined());
        $this->assertEquals($userAuthenticator->getId(), 1);
        $this->assertInstanceOf(User::class, $userAuthenticator->getUser());
        $this->assertEquals($userAuthenticator->getUser()->username, 'test');

        $this->assertFalse($adminAuthenticator->isLogined());
        $this->assertNull($adminAuthenticator->getId());
        $this->assertNull($adminAuthenticator->getUser());

        $adminAuthenticator->login([
            'username' => 'test',
            'password' => '123654'
        ]);

        $this->assertTrue($adminAuthenticator->isLogined());
        $this->assertEquals($adminAuthenticator->getId(), 1);
        $this->assertInstanceOf(User::class, $adminAuthenticator->getUser());
        $this->assertEquals($adminAuthenticator->getUser()->username, 'test');

        $adminAuthenticator->logout();

        $this->assertTrue($userAuthenticator->isLogined());
        $this->assertEquals($userAuthenticator->getId(), 1);
        $this->assertInstanceOf(User::class, $userAuthenticator->getUser());
        $this->assertEquals($userAuthenticator->getUser()->username, 'test');

        $this->assertFalse($adminAuthenticator->isLogined());
        $this->assertNull($adminAuthenticator->getId());
        $this->assertNull($adminAuthenticator->getUser());
    }

    public function testAttachBehavior()
    {
        $authenticator = $this->getAuthenticator('test1', 'UserIdentity');

        $authenticator->attachBehavior(AuthBehavior::EVENT_LOGIN_BEFORE, function ($credentials) {
            $this->assertEquals($credentials['username'], 'test');
        });

        $authenticator->attachBehavior(AuthBehavior::EVENT_LOGIN_SUCCESS, function ($params) {
            $user = $params['user'];
            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals($user->username, 'test');
        });

        $authenticator->attachBehavior(AuthBehavior::EVENT_LOGOUT_BEFORE, function ($user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals($user->username, 'test');
        });

        $authenticator->login([
            'username' => 'test',
            'password' => '123654'
        ]);

        $authenticator->logout();
    }

    public function testAttachBehaviorWithFailure()
    {
        $this->expectException(AuthenticationException::class);
        $authenticator = $this->getAuthenticator('test1', 'UserIdentity');

        $authenticator->attachBehavior(AuthBehavior::EVENT_LOGIN_BEFORE, function ($credentials) {
            $this->assertEquals($credentials['username'], 'test');
        });

        $authenticator->attachBehavior(AuthBehavior::EVENT_LOGIN_FAILURE, function ($params) {
            $this->assertInstanceOf(AuthenticationException::class, $params['exception']);
            $this->assertEquals($params['credentials']['username'], 'test');
        });

        $authenticator->login([
            'username' => 'test',
            'password' => 'wrong'
        ]);
    }

    public function testAttachBehaviorWithInvalidEvent()
    {
        $this->expectException(AuthenticationException::class);
        $authenticator = $this->getAuthenticator('test1', 'UserIdentity');

        $authenticator->attachBehavior('InvaildEvent', function ($credentials) {
            $this->assertEquals($credentials['username'], 'test');
        });

        $authenticator->login([
            'username' => 'test',
            'password' => '123654'
        ]);
    }

    private function getAuthenticator($name, $sessionKey)
    {
        $session = $this->container->get('session');
        $hook = $this->container->get('hook');
        $hasher = new BcryptHasher();
        $provider = new ModelUserProvider(User::class, 'id', 'password', true, $hasher);
        
        return new SessionAuthenticator($name, $sessionKey, $session, $provider, $hook);

    }
}