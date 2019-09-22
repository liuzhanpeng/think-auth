<?php
namespace Lzpeng\Auth\UserProviders;

use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\UserIdentity;
use Lzpeng\Auth\Contracts\Hasher;
use Lzpeng\Auth\Exceptions\AuthenticationException;

/**
 * 基于think\Model的通用用户提供器
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class ModelUserProvider implements UserProvider
{
    /**
     * 模型类
     * 
     * @var string
     */
    protected $modelClass;

    /**
     * 模型用户标识属性名称
     * 
     * @var string
     */
    protected $idKey;

    /**
     * 用户凭证数组里的密码key
     * 
     * @var string
     */
    protected $passwordKey;

    /**
     * 是否强制验证密码
     *
     * @var bool
     */
    protected $forceValidatePassword; 

    /**
     * hasher 
     * 
     * @var Hasher
     */
    protected $hasher;

    /**
     * 构造函数
     * 
     * @param string $modelClass 模型类
     * @param string $idKey 模型id属性名称
     * @param string $passwordKey 用户凭证数组里的密码key
     * @param bool $forceValidatePassword 是否强制验证密码; 如果是ture但没传入密码凭证，凭证就验证失败; false的话不传密码凭证就忽略密码凭证的验证
     * @param Hasher $hasher 密码hash处理器
     */
    public function __construct(
        string $modelClass, 
        string $idKey = 'id',
        string $passwordKey = 'password',
        bool $forceValidatePassword = true,
        Hasher $hasher
    ) {
        $this->modelClass = $modelClass;
        $this->idKey = $idKey;
        $this->passwordKey = $passwordKey;
        $this->forceValidatePassword = $forceValidatePassword;
        $this->hasher = $hasher;
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
        if (empty($credentials) || (count($credentials) === 1 && array_key_exists($this->passwordKey, $credentials))) {
            return null;
        }

        $model = $this->createModel();
        $query = $model::field('*');

        // 循环设置查询条件
        foreach ($credentials as $key => $val) {
            if ($key == $this->passwordKey) {
                continue;
            }

            if (is_array($val)) {
                $query->whereIn($key, $val);
            } else {
                $query->where($key, $val);
            }
        }

        return $query->find();
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials(UserIdentity $user, array $credentials)
    {
        if (!isset($credentials[$this->passwordKey])) {
            return $this->forceValidatePassword ? false : true;
        }

        return $this->hasher->check($credentials[$this->passwordKey], $user->getPassword());
    }

    /**
     * 创建模型实例
     */
    private function createModel()
    {
        $class = '\\'.ltrim($this->modelClass, '\\');

        if (!class_exists($class)) {
            throw new AuthenticationException(sprintf('找不到模型类: %s', $class));
        }

        return new $class;
    }
}