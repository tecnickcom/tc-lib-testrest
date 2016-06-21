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

namespace Com\Tecnick\TestRest;

use \Com\Tecnick\TestRest\Exception;
use \Behat\Gherkin\Node\PyStringNode;

/**
 * Com\Tecnick\TestRest\HeaderContext
 *
 * @category    Library
 * @package     Com\Tecnick\TestRest
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 MediaSift Ltd. <http://datasift.com>, 2016 Tecnick.com LTD <http://www.tecnick.com>
 * @license     https://opensource.org/licenses/MIT The MIT License (MIT) - see the LICENSE file
 * @link        https://github.com/tecnickcom/tc-lib-testrest
 */
class HeaderContext extends \Com\Tecnick\TestRest\InputContext
{
    /**
     * Verify the value of the HTTP response status code.
     *
     * Example:
     *     Then the response status code should be "200"
     *
     * @param int $httpStatus Expected HTTP status code.
     *
     * @Then /^the response status code should be "(\d+)"$/
     */
    public function theResponseStatusCodeShouldBe($httpStatus)
    {
        if ((string)$this->response->getStatusCode() !== (string)$httpStatus) {
            throw new Exception(
                'HTTP code does not match '.$httpStatus.
                ' (actual: '.$this->response->getStatusCode().')'
            );
        }
    }

    /**
     * Check if the value of the http status code matches the defined regular expression pattern
     *
     * Example:
     *     Then the response status code matches the pattern "/^20[0-9]$/"
     *
     * @param string $pattern Regular expression pattern to match
     *
     * @Then /^the response status code matches the pattern "([^\n]*)"$/
     */
    public function theResponseStatusCodeMatchesThePattern($pattern)
    {
        $value = $this->response->getStatusCode();
        $result = preg_match($pattern, $value);
        if (empty($result)) {
            throw new Exception(
                'The value of HTTP status code is \''.$value
                .'\' and does not matches the pattern \''.$pattern.'\'!'."\n"
            );
        }
    }

    /**
     * Check the value of an header property.
     *
     * Example:
     *     Then the "Connection" header property equals "close"
     *
     * @param string $propertyName  Name of the header property to check.
     * @param string $propertyValue Expected value of the header property.
     *
     * @Then /^the "([^"]+)" header property equals "([^\n]*)"$/
     */
    public function theHeaderPropertyEquals($propertyName, $propertyValue)
    {
        $value = $this->response->getHeader($propertyName);
        if (($value === null) && ($propertyValue == 'null')) {
            return;
        }
        // compare values
        if ((string)$value !== (string)$propertyValue) {
            throw new Exception('Property value mismatch! (given: '.$propertyValue.', match: '.$value.')');
        }
    }

    /**
     * Check if the value of the specified header property matches the defined regular expression pattern
     *
     * Example:
     *     Then the value of the "Location" header property matches the pattern "/^\/api\/[1-9][0-9]*$/"
     *
     * @param string $propertyName Name of the header property to check.
     * @param string $pattern      Regular expression pattern to match
     *
     * @Then /^the value of the "([^"]*)" header property matches the pattern "([^\n]*)"$/
     */
    public function theValueOfTheHeaderPropertyMatchesThePattern($propertyName, $pattern)
    {
        $value = $this->response->getHeader($propertyName);
        $result = preg_match($pattern, $value);
        if (empty($result)) {
            throw new Exception(
                'The value of header \''.$propertyName.'\' is \''.$value
                .'\' and does not matches the pattern \''.$pattern.'\'!'."\n"
            );
        }
    }
}
