<?php
namespace Lzpeng\Auth\UserProviders;

use Lzpeng\Auth\Contracts\UserProvider;
use think\Model;

/**
 * 基于think\Model的用户提供器
 */
class ModelUserProvider implements UserProvider
{
    /**
     * 模型对象
     * 
     * @var think\Model
     */
    private $model;

    /**
     * hasher 
     * 
     * @var HasherContract
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
     * @param think\Model $model 模型对象
     * @param HasherContract $hasher 密码hash处理器
     * @param string $idKey 模型id属性名称
     * @param string $passwordKey 用户凭证数组里的密码key
     */
    public function __construct(
        Model $model, 
        HasherContract $hasher, 
        string $idKey = 'id',
        string $passwordKey = 'password')
    {
        $this->model = $model;
        $this->hasher = $hasher;
        $this->idKey = $idKey;
        $this->passwordKey = $passwordKey;
    }

    /**
     * @inheritDoc
     */
    public function findId($id)
    {
        return $this->model->buildQuery()->where($this->idKey, $id)->find();
    }

    /**
     * @inheritDoc
     */
    public function findByCredentials(array $credentials)
    {
        $query = $this->model->buildQuery();

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
        if (!in_array($credentials[$this->passwordKey])) {
            return false;
        }

        return $this->hasher->check($user->getPassword(), $credentials[$this->passwordKey]);
    }
}