<?php
namespace Lzpeng\Auth;

use Lzpeng\Auth\Contracts\ResultContract;

/**
 * 认证结果
 * 
 * @author 刘展鹏 <liuzhanpeng@gmail.com>
 */
class Result
{
    /**
     * 成功状态
     * 
     * @var int
     */
    const STATUS_SUCCESS = 1;

    /**
     * 失败
     * 
     * @var int
     */
    const STATUS_FAILURE = 0;

    /**
     * 状态
     * 
     * @var int
     */
    private $status;

    /**
     * 扩展数据
     *
     * @var array
     */
    private $data;

    /**
     * 构造函数
     *
     * @param integer $status 状态
     * @param array $data 扩展数据
     */
    public function __construct(int $status = ResultContract::STATUS_FAILURE, array $data = [])
    {
        $this->status = $status;
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function isValid()
    {
        return $this->status === ResultContract::STATUS_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 获取指定key用户数据
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key) 
    {
        return $this->data[$key];
    }

    /**
     * 判断指定key数据是否存在
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }
}