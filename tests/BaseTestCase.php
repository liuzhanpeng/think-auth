<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use think\Container;
use think\Db;

abstract class BaseTestCase extends TestCase
{
    protected $container;

    protected $connection;

    public static function setUpBeforeClass(): void
    {
        require_once 'vendor/topthink/framework/base.php';

        Db::init([
            'type' => 'mysql',
            'hostname' => $GLOBALS['DB_HOSTNAME'],
            'database' => $GLOBALS['DB_NAME'],
            'username' => $GLOBALS['DB_USER'],
            'password' => $GLOBALS['DB_PASSWD']
        ]);
    }

    public function setUp(): void
    {
        $this->container = Container::getInstance();
    }

    public function tearDown(): void
    {
        $this->container = null;
    }
}