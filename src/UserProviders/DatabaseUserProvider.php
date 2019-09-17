<?php
namespace Lzpeng\Auth\UserProviders;

use Lzpeng\Auth\Contracts\UserProvider;
use Lzpeng\Auth\Contracts\UserIdentity;
use Lzpeng\Auth\Contracts\Hasher;
use Lzpeng\Auth\Users\GenericUser;
use think\Db;

/**
 * 基于think\Db的用户提供器
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class DatabaseUserProvider implements UserProvider
{
    /**
     * 用户表
     *
     * @var string
     */
    private $table;

    /**
     * 用户表中的用户标识属性名称
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
     * 是否强制验证密码
     *
     * @var bool
     */
    private $forceValidatePassword; 

    /**
     * hasher 
     * 
     * @var Hasher
     */
    private $hasher;

    /**
     * 构造函数
     * 
     * @param string $table 用户表
     * @param string $idKey 模型id属性名称
     * @param string $passwordKey 用户凭证数组里的密码key
     * @param bool $forceValidatePassword 是否强制验证密码
     * @param Hasher $hasher 密码hash处理器
     */
    public function __construct(
        string $table,
        string $idKey = 'id',
        string $passwordKey = 'password',
        bool $forceValidatePassword = true,
        Hasher $hasher
    ) {
        $this->table = $table;
        $this->idKey = $options['idKey'] ?? 'id';
        $this->passwordKey = $options['passwordKey'] ?? 'password';
        $this->forceValidatePassword = $forceValidatePassword;
        $this->hasher = $hasher;
    }

    /**
     * @inheritDoc
     */
    public function findById($id)
    {
        $result = Db::name($this->table)->where($this->idKey, $id)->find();

        if (!$result) {
            return null;
        }

        return new GenericUser($result, $this->idKey, $this->passwordKey);
    }

    /**
     * @inheritDoc
     */
    public function findByCredentials(array $credentials)
    {
        if (empty($credentials) || (count($credentials) === 1 && array_key_exists($this->passwordKey, $credentials))) {
            return;
        }

        $query = Db::name($this->table);

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

        $result =  $query->find();
        if (!$result) {
            return null;
        }

        return new GenericUser($result, $this->idKey, $this->passwordKey);
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
}