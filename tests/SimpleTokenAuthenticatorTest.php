<?php
namespace Tests;

use Lzpeng\Auth\Authenticators\SimpleTokenAuthenticator;
use Lzpeng\Auth\Contracts\AuthBehavior;
use Lzpeng\Auth\UserProviders\ModelUserProvider;
use Lzpeng\Auth\Hashers\BcryptHasher;
use Lzpeng\Auth\Exceptions\AuthenticationException;
use Tests\Users\User;

class SimpleTokenAuthenticatorTest extends BaseTestCase
{
    public function testLogin()
    {
        $authenticator = $this->getAuthenticator('test1', 'User-Token');

        $this->assertFalse($authenticator->isLogined());
        $this->assertNull($authenticator->getId());
        $this->assertNull($authenticator->getUser());

        $token = $authenticator->login([
            'username' => 'test',
            'password' => '123654'
        ]);

        $this->assertNotNull($token);
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
        $authenticator = $this->getAuthenticator('test1', 'User-Token');

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
        $authenticator = $this->getAuthenticator('test1', 'User-Token');

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
        $authenticator = $this->getAuthenticator('test1', 'User-Token');
        $authenticator->setUser($user);

        $this->assertTrue($authenticator->isLogined());
        $this->assertEquals($authenticator->getId(), 1);
        $this->assertInstanceOf(User::class, $authenticator->getUser());
        $this->assertEquals($authenticator->getUser()->username, 'test');
    }

    private function getAuthenticator($name, $tokenKey)
    {
        $cache = $this->container->get('cache');
        $cache->init([
            'type'   => 'File',
            'path'   => '',
            'prefix' => '',
            'expire' => 0,
        ]);
        $request = $this->container->get('request');
        $hook = $this->container->get('hook');
        $hasher = new BcryptHasher();
        $provider = new ModelUserProvider(User::class, 'id', 'password', true, $hasher);
        
        return new SimpleTokenAuthenticator($name, $tokenKey, $cache, $request, $provider, $hook);

    }
}