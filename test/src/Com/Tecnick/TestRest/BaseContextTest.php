<?php
/**
 * This file is part of Com\Tecnick\TestRest project.
 *
 * @category    Library
 * @package     Com\Tecnick\TestRest
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 MediaSift Ltd. <http://datasift.com>
 * @license     https://opensource.org/licenses/MIT The MIT License (MIT) - see the LICENSE file
 * @link        https://github.com/tecnickcom/tc-lib-testrest
 */

namespace Test;

class BaseContextTest extends \PHPUnit_Framework_TestCase
{
    protected $obj = null;
    
    public function setUp()
    {
        //$this->markTestSkipped(); // skip this test

        $parameters = array(
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
            )
        );
        $this->obj = new \Com\Tecnick\TestRest\BaseContext($parameters);
    }

    public function testGetParameter()
    {
        $this->assertEquals('beta', $this->obj->getParameter('alpha'));
        $this->assertEquals(123, $this->obj->getParameter('gamma'));
        $this->assertTrue($this->obj->getParameter('delta'));
        $this->assertCount(8, $this->obj->getParameter('db'));
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
        $obj = $this->obj;
        $obj::setupEnvironment();

        $obj = new \Com\Tecnick\TestRest\BaseContext(array());
        $obj::setupEnvironment();
    }
}
