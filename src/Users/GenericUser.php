<?php
namespace Lzpeng\Auth\Users;

use Lzpeng\Auth\Contracts\UserIdentity;

/**
 * 通用用户对象
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class GenericUser implements UserIdentity
{
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
     * 用户数据
     *
     * @var array
     */
    protected $data;

    /**
     * 构造函数
     *
     * @param array $data 用户数据
     * @param string $idKey 用户标识key
     * @param string $passwordKey 用户密码key
     * @return void
     */
    public function __construct(array $data, string $idKey = 'id', string $passwordKey = 'password')
    {
        $this->data = $data;
        $this->idKey = $idKey;
        $this->passwordKey = $passwordKey;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->data[$this->idKey];
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->data[$this->passwordKey];
    }

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }
}