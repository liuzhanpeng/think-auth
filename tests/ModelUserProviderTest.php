<?php
namespace Tests;

use Lzpeng\Auth\Hashers\BcryptHasher;
use Lzpeng\Auth\UserProviders\ModelUserProvider;
use Lzpeng\Auth\Exceptions\AuthenticationException;
use Tests\Users\User;

class ModelUserProviderTest extends BaseTestCase
{
    public function testFindId()
    {
        $provider = $this->getProvider(User::class, 'id', 'password', true);

        $notExistsUser = $provider->findById(999);

        $this->assertNull($notExistsUser);

        $user = $provider->findById(1);

        $this->assertNotNull($user);
        $this->assertEquals($user->username, 'test');
    }

    public function testfindByCredentials()
    {
        $provider = $this->getProvider(User::class, 'id', 'password', true);

        $notExistsUser = $provider->findByCredentials([
            'username' => 'notexists',
            'password' => '123654'
        ]);

        $this->assertNull($notExistsUser);

        $user = $provider->findByCredentials([
            'username' => 'test',
            'password' => '123654'
        ]);

        $this->assertNotNull($user);
        $this->assertEquals($user->username, 'test');

        $user2 = $provider->findByCredentials([
            'username' => 'test',
            'remark' => '测试员',
            'type' => [1, 2],
            'password' => '123654'
        ]);

        $this->assertNotNull($user2);
        $this->assertEquals($user2->username, 'test');
    }

    public function testValidateCredentials()
    {
        $provider = $this->getProvider(User::class, 'id', 'password', true);

        $credentials = [
            'username' => 'test',
            'password' => '123654'
        ];

        $user = $provider->findByCredentials($credentials);

        $this->assertNotNull($user);

        $result = $provider->validateCredentials($user, $credentials);

        $this->assertTrue($result);

        $credentials['password'] = 'wrong';

        $result = $provider->validateCredentials($user, $credentials);

        $this->assertFalse($result);
    }

    public function testForceValidatePassword()
    {
        $provider = $this->getProvider(User::class, 'id', 'password', false);

        $credentials = [
            'username' => 'test',
            'password' => '123654'
        ];

        $user = $provider->findByCredentials($credentials);

        $this->assertNotNull($user);

        $result = $provider->validateCredentials($user, $credentials);

        $this->assertTrue($result);

        unset($credentials['password']);

        $result = $provider->validateCredentials($user, $credentials);

        $this->assertTrue($result);
    }

    public function testInvalidModelClass()
    {
        $this->expectException(AuthenticationException::class);
        $hasher = new BcryptHasher();

        $provider = $this->getProvider('\test\InvalidUser', 'id', 'password', true);
        
        $user = $provider->findById(1);
    }

    public function testInvalidIdKey()
    {
        $this->expectException(\think\exception\PDOException::class);

        $provider = $this->getProvider(User::class, 'id1', 'password', true);

        $user = $provider->findById(1);
    }

    private function getProvider($modelClass, $idKey, $passwordKey, $forceValidatePassword)
    {
        $hasher = new BcryptHasher();

        return new ModelUserProvider($modelClass, $idKey, $passwordKey, $forceValidatePassword, $hasher);
    }
}