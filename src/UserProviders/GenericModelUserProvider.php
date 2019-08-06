<?php
namespace Lzpeng\Auth\UserProviders;

use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\UserIdentity;
use Lzpeng\Auth\Contracts\PasswordHasherContract;
use think\Model;

/**
 * 基于think\Model的用户提供器
 * 处理普通的账号和密码认证逻辑
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class GenericModelUserProvider implements UserProvider
{
    /**
     * 模型标识
     * 
     * @var string
     */
    private $model;

    /**
     * hasher 
     * 
     * @var PasswordHasherContract
     */
    private $hasher;

    /**
     * 模型id属性名称
     * 
     * @var string
     */
    private $idKey;

    /**
     * 用户凭证数组里的密码key
     * 
     * @var string
     */
    private $passwordKey;

    /**
     * 构造函数
     * 
     * @param string $model 模型标识
     * @param string $idKey 模型id属性名称
     * @param string $passwordKey 用户凭证数组里的密码key
     * @param PasswordHasherContract $hasher 密码hash处理器
     */
    public function __construct(
        string $model, 
        PasswordHasherContract $hasher,
        array $options = [])
    {
        $this->model = $model;
        $this->hasher = $hasher;
        $this->idKey = $options['idKey'] ?? 'id';
        $this->passwordKey = $options['passwordKey'] ?? 'password';
    }

    /**
     * @inheritDoc
     */
    public function findById($id)
    {
        return $this->createModel()->where($this->idKey, $id)->find();
    }

    /**
     * @inheritDoc
     */
    public function findByCredentials(array $credentials)
    {
        $query = $this->createModel();

        // 循环设置查询条件
        foreach ($credentials as $key => $val) {
            if ($key == $this->passwordKey) {
                continue;
            }

            $query = $query->where($key, $val);
        }

        return $query->find();
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials(UserIdentity $user, array $credentials)
    {
        if (!isset($credentials[$this->passwordKey])) {
            return false;
        }

        return $this->hasher->check($credentials[$this->passwordKey], $user->getPassword());
    }

    /**
     * 创建模型实例
     */
    private function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }
}