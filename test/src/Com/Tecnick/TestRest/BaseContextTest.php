<?php
/**
 * This file is part of Com\Tecnick\TestRest project.
 *
 * @category    Library
 * @package     Com\Tecnick\TestRest
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015 MediaSift Ltd. <http://datasift.com>, 2016 Tecnick.com LTD <http://www.tecnick.com>
 * @license     https://opensource.org/licenses/MIT The MIT License (MIT) - see the LICENSE file
 * @link        https://github.com/tecnickcom/tc-lib-testrest
 */

namespace Test;

class BaseContextTest extends \PHPUnit_Framework_TestCase
{
    protected static $mysqlparams = array(
        'alpha'    => 'beta',
        'gamma'    => 123,
        'delta'    => true,
        'base_url' => 'http://localhost:8081',
        'db' => array(
            'driver'     => 'mysql',
            'database'   => 'testrest_test',
            'host'       => '127.0.0.1',
            'port'       => 3306,
            'username'   => 'testrest',
            'password'   => 'testrest',
            'sql_schema' => '/../../../../test/resources/database/schema.sql',
            'sql_data'   => '/../../../../test/resources/database/data.sql'
        ),
        'memcached' => array(
            'host' => '127.0.0.1',
            'port' => 11211
        ),
    );
    
    protected static $sqliteparams = array(
        'alpha'    => 'beta',
        'gamma'    => 123,
        'delta'    => true,
        'base_url' => 'http://localhost:8081',
        'db' => array(
            'driver'     => 'sqlite',
            'path'       => '/../../../../target/test.sqlite',
            'sql_schema' => '/../../../../test/resources/database/sqliteschema.sql',
            'sql_data'   => '/../../../../test/resources/database/data.sql'
        ),
        'memcached' => array(
            'host' => '127.0.0.1',
            'port' => 11211
        ),
    );

    public function setUp()
    {
        //$this->markTestSkipped(); // skip this test
    }

    public function testGetParameter()
    {
        $obj = new \Com\Tecnick\TestRest\BaseContext(self::$mysqlparams);
        $this->assertEquals('beta', $obj->getParameter('alpha'));
        $this->assertEquals(123, $obj->getParameter('gamma'));
        $this->assertTrue($obj->getParameter('delta'));
        $this->assertCount(8, $obj->getParameter('db'));
    }

    public function testGetParameterMissing()
    {
        $obj = new \Com\Tecnick\TestRest\BaseContext(array());
        $this->setExpectedException(
            'Exception',
            'Context Parameters not loaded!'
        );
        $obj->getParameter('missing');
    }

    public function testSetupEnvironment()
    {
        $obj = new \Com\Tecnick\TestRest\BaseContext(self::$mysqlparams);
        $obj::setupEnvironment();
        
        $obj = new \Com\Tecnick\TestRest\BaseContext(self::$sqliteparams);
        $obj::setupEnvironment();

        $obj = new \Com\Tecnick\TestRest\BaseContext(array());
        $obj::setupEnvironment();
    }
}
