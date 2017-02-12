<?php
/**
 * This file is part of Com\Tecnick\TestRest project.
 *
 * @category    Library
 * @package     Com\Tecnick\TestRest
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015 MediaSift Ltd. <http://datasift.com>, 2016-2017 Tecnick.com LTD <http://www.tecnick.com>
 * @license     https://opensource.org/licenses/MIT The MIT License (MIT) - see the LICENSE file
 * @link        https://github.com/tecnickcom/tc-lib-testrest
 */

namespace Test;

use PHPUnit\Framework\TestCase;

class RestContextTest extends TestCase
{
    protected $obj = null;

    public function setUp()
    {
        //$this->markTestSkipped(); // skip this test

        $parameters = array(
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
        $this->obj = new \Com\Tecnick\TestRest\RestContext($parameters);
        $this->mockClient();
    }

    protected function getProperty($property)
    {
        $reflectionClass = new \ReflectionClass('\Com\Tecnick\TestRest\BaseContext');
        $prop = $reflectionClass->getProperty($property);
        $prop->setAccessible(true);
        return $prop;
    }

    protected function getPropertyValue($property)
    {
        $prop = $this->getProperty($property);
        $prop->getValue();
    }

    protected function setPropertyValue($property, $value)
    {
        $prop = $this->getProperty($property);
        $prop->setValue($this->obj, $value);
    }

    protected function mockClient()
    {
        $mockResponse = new \Guzzle\Http\Message\Response(200);
        $mockResponse->setBody(
            '{"hello":"world","0":[{"alpha":null},{"gamma":3}],"1":{"echo":"foxtrot","\"quote\"":true}}',
            'application/json'
        );
        $mockResponse->setHeaders(
            array(
                'Host'         => 'ms-lib-testrest',
                'User-Agent'   => 'test',
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'Location'     => 'hello'
            )
        );
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse($mockResponse);
        $client = new \Guzzle\Service\Client();
        $client->setDefaultOption('exceptions', false);
        $client->addSubscriber($plugin);
        $this->setPropertyValue('client', $client);
        $this->setPropertyValue('response', $mockResponse);
    }

    public function testThatHeaderPropertyIs()
    {
        $this->obj->thatHeaderPropertyIs('alpha', 'null');
        $this->obj->thatHeaderPropertyIs('beta', '123');
        $this->assertNotNull($this->obj);
    }

    public function testThatPropertyIs()
    {
        $this->obj->thatPropertyIs('alpha', 'null');
        $this->obj->thatPropertyIs('beta', 'gamma');
        $this->obj->thatPropertyIs('one[3].two', '1.23');
        $this->assertNotNull($this->obj);
    }

    public function testThatTheBodyIsValidJson()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode('{"hello":"world"}', 1);
        $this->obj->thatTheRequestBodyIsValidJson($json);
        $this->assertNotNull($this->obj);
    }

    public function testTheResponseBodyIsEmpty()
    {
        $this->obj->theResponseBodyIsEmpty('not ');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheResponseBodyIsEmptyEx()
    {
        $this->obj->theResponseBodyIsEmpty('');
    }

    public function testTheResponseBodyEquals()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode(
            '{"hello":"world","0":[{"alpha":null},{"gamma":3}],"1":{"echo":"foxtrot","\"quote\"":true}}',
            1
        );
        $this->obj->theResponseBodyEquals($json);
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheResponseBodyEqualsEx()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode('{"hello":"world"}', 1);
        $this->obj->theResponseBodyEquals($json);
    }

    public function testTheResponseBodyMatchesThePattern()
    {
        $this->obj->theResponseBodyMatchesThePattern('/fox[a-z]{4}/');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheResponseBodyMatchesThePatternEx()
    {
        $this->obj->theResponseBodyMatchesThePattern('/[~]{10}/');
    }

    /**
     * @expectedException \Exception
     */
    public function testThatTheBodyIsValidJsonEx()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode('{"hello":"world', 1);
        $this->obj->thatTheRequestBodyIsValidJson($json);
    }

    public function testThatThePropertiesInTheJson()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode('{"hello":"world"}', 1);
        $this->obj->thatThePropertiesInThe('JSON', $json);
        $this->assertNotNull($this->obj);
    }

    public function testThatThePropertiesInTheTable()
    {
        $table = new \Behat\Gherkin\Node\TableNode("|property|value|\n|alpha|beta|\n|gamma|delta|");
        $this->obj->thatThePropertiesInThe('TABLE', $table);
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testThatThePropertiesInTheEx()
    {
        $this->obj->thatThePropertiesInThe('ERROR', '');
    }

    public function testthatTheRequestBodyIs()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode('{"hello":"world"}', 1);
        $this->obj->thatTheRequestBodyIs($json);
        $this->assertNotNull($this->obj);
    }

    public function testthatThePropertiesAreImportedFromTheJsonFile()
    {
        $file = 'test/resources/data.json';
        $this->obj->thatThePropertiesAreImportedFromTheJsonFile($file);
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testthatThePropertiesAreImportedFromTheJsonFileException()
    {
        $file = 'test/resources/error.json';
        $this->obj->thatThePropertiesAreImportedFromTheJsonFile($file);
    }

    public function testthatTheRequestBodyIsImportedFromTheFile()
    {
        $file = 'test/resources/data.json';
        $this->obj->thatTheRequestBodyIsImportedFromTheFile($file);
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testthatTheRequestBodyIsImportedFromTheFileException()
    {
        $file = 'test/resources/error.json';
        $this->obj->thatTheRequestBodyIsImportedFromTheFile($file);
    }

    public function testIRequest()
    {
        $this->obj->thatPropertyIs('var', 'value');
        $this->obj->iRequest('get', '/');
        $this->assertNotNull($this->obj);
    }

    public function testIRequestAppend()
    {
        $this->obj->thatPropertyIs('var', 'value');
        $this->obj->iRequest('get', '/?a=b');
        $this->assertNotNull($this->obj);
    }

    public function testIRequestPost()
    {
        $this->obj->thatHeaderPropertyIs('beta', '1234');
        $this->obj->iRequest('post', '/');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testIRequestGetException()
    {
        $this->setPropertyValue('client', new \Guzzle\Service\Client());
        $this->obj->iRequest('get', '/');
    }

    public function testTheHeaderPropertyEquals()
    {
        $this->obj->theHeaderPropertyEquals('User-Agent', 'test');
        $this->obj->theHeaderPropertyEquals('missing', 'null');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheHeaderPropertyEqualsEx()
    {
        $this->obj->theHeaderPropertyEquals('User-Agent', 'wrong');
    }

    public function testTheValueOfTheHeaderPropertyMatchesThePattern()
    {
        $this->obj->theValueOfTheHeaderPropertyMatchesThePattern('Location', '/^[a-z]{5}$/');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheValueOfTheHeaderPropertyMatchesThePatternEx()
    {
        $this->obj->theValueOfTheHeaderPropertyMatchesThePattern('Location', '/^[0-9]+$/');
    }

    /**
     * @expectedException \Exception
     */
    public function testTheValueOfTheHeaderPropertyMatchesThePatternEx2()
    {
        $this->obj->theValueOfTheHeaderPropertyMatchesThePattern('missing', '/aaaa/');
    }

    public function testGetResponseData()
    {
        $data = json_decode(json_encode($this->obj->getResponseData()), true);
        $this->assertEquals(
            array(
                'hello' => 'world',
                0 => array(array('alpha' => null), array('gamma' => 3)),
                1 => array('echo' => 'foxtrot', '"quote"' => true),
            ),
            (array)$data
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testGetResponseDataException()
    {
        $mockResponse = new \Guzzle\Http\Message\Response(200);
        $mockResponse->setBody('simple text', 'application/text');
        $this->setPropertyValue('response', $mockResponse);
        $this->obj->getResponseData();
    }

    public function testTheResponseBodyContainsTheJsonData()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode(
            '{"hello":"world","0":[{"alpha":null},{"gamma":3}],"1":{"echo":"foxtrot","\"quote\"":true}}',
            1
        );
        $this->obj->theResponseBodyContainsTheJsonData($json);
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheResponseBodyContainsTheJsonDataEx()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode(
            '{"hello":"world","0":[{"alpha":null},{"gamma":3}],"1":{"\"quote\"":false,"missing":true}}',
            1
        );
        $this->obj->theResponseBodyContainsTheJsonData($json);
    }

    public function testTheResponseBodyJsonEquals()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode(
            '{"hello":"world","0":[{"alpha":null},{"gamma":3}],"1":{"echo":"foxtrot","\"quote\"":true}}',
            1
        );
        $this->obj->theResponseBodyJsonEquals($json);
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheResponseBodyJsonEqualsEx()
    {
        $json = new \Behat\Gherkin\Node\PyStringNode(
            '{"hello":"world","0":[{"alpha":null},{"gamma":3}],"1":{"echo":"foxtrot"}}',
            1
        );
        $this->obj->theResponseBodyJsonEquals($json);
    }

    public function testGetObjectValue()
    {
        $data = $this->obj->getObjectValue('hello');
        $this->assertEquals('world', $data);

        $data = $this->obj->getObjectValue('0[1].gamma');
        $this->assertEquals(3, $data);

        $data = $this->obj->getObjectValue('1.echo');
        $this->assertEquals('foxtrot', $data);

        $data = $this->obj->getObjectValue('1.quote');
        $this->assertTrue($data);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetObjectValueEx()
    {
        $this->obj->getObjectValue('0[1].error');
    }

    public function testThePropertyEquals()
    {
        $this->obj->thePropertyEquals('hello', 'world');
        $this->obj->thePropertyEquals('0[1].gamma', '3');
        $this->obj->thePropertyEquals('1.echo', 'foxtrot');
        $this->obj->thePropertyEquals('1.quote', 'true');
        $this->obj->thePropertyEquals('null', 'null');
        $this->obj->thePropertyEquals('0', '[{"alpha":null},{"gamma":3}]');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testThePropertyEqualsEx1()
    {
        $this->obj->thePropertyEquals('something', 'missing');
    }

    /**
     * @expectedException \Exception
     */
    public function testThePropertyEqualsEx2()
    {
        $this->obj->thePropertyEquals('0', '[{"alpha":"b"},{"gamma":3}]');
    }

    /**
     * @expectedException \Exception
     */
    public function testThePropertyEqualsEx3()
    {
        $this->obj->thePropertyEquals('hello', 'wrong');
    }

    public function testThePropertyIsAnWithItems()
    {
        $this->obj->thePropertyIsAnWithItems('0', 'array', 2);
        $this->obj->thePropertyIsAnWithItems('1', 'object', 2);
        $this->obj->thePropertyIsAnWithItems('missing', 'array', 'null');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testThePropertyIsAnWithItemsEx1()
    {
        $this->obj->thePropertyIsAnWithItems('0', 'array', 3);
    }

    /**
     * @expectedException \Exception
     */
    public function testThePropertyIsAnWithItemsEx2()
    {
        $this->obj->thePropertyIsAnWithItems('missing', 'array', 'error');
    }

    public function testTheTypeOfThePropertyShouldBe()
    {
        $this->obj->theTypeOfThePropertyShouldBe('hello', 'string');
        $this->obj->theTypeOfThePropertyShouldBe('0', 'array');
        $this->obj->theTypeOfThePropertyShouldBe('1', 'object');
        $this->obj->theTypeOfThePropertyShouldBe('0[1].gamma', 'integer');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheTypeOfThePropertyShouldBeEx()
    {
        $this->obj->theTypeOfThePropertyShouldBe('1', 'string');
    }

    public function testTheLengthOfThePropertyShouldBe()
    {
        $this->obj->theLengthOfThePropertyShouldBe('hello', 5);
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheLengthOfThePropertyShouldBeEx()
    {
        $this->obj->theLengthOfThePropertyShouldBe('hello', 6);
    }

    public function testTheValueOfThePropertyMatchesThePattern()
    {
        $this->obj->theValueOfThePropertyMatchesThePattern('hello', '/^[a-z]{5}$/');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheValueOfThePropertyMatchesThePatternEx()
    {
        $this->obj->theValueOfThePropertyMatchesThePattern('hello', '/^[0-9]+$/');
    }

    /**
     * @expectedException \Exception
     */
    public function testTheValueOfThePropertyMatchesThePatternEx2()
    {
        $this->obj->theValueOfThePropertyMatchesThePattern(-1, -0);
    }

    public function testTheResponseStatusCodeShouldBe()
    {
        $this->obj->theResponseStatusCodeShouldBe(200);
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheResponseStatusCodeShouldBeException()
    {
        $this->obj->theResponseStatusCodeShouldBe(0);
    }

    public function testTheResponseStatusCodeMatchesThePattern()
    {
        $this->obj->theResponseStatusCodeMatchesThePattern('/^20[0-9]$/');
        $this->assertNotNull($this->obj);
    }

    /**
     * @expectedException \Exception
     */
    public function testTheResponseStatusCodeMatchesThePatternException()
    {
        $this->obj->theResponseStatusCodeMatchesThePattern('/300/');
    }

    public function testWaitSeconds()
    {
        $start = microtime(true);
        $this->obj->waitSeconds(1);
        $elapsed = (microtime(true) - $start);
        $this->assertGreaterThanOrEqual(1, $elapsed);
    }

    public function testEchoLastResponse()
    {
        ob_start();
        $this->obj->echoLastResponse();
        $out = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('cb804d80e3221a3cbd9e14a5b1e9b2f4', md5($out));
    }
}
