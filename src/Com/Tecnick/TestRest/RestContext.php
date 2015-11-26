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

namespace Com\Tecnick\TestRest;

use \Com\Tecnick\TestRest\Exception;
use \Behat\Gherkin\Node\PyStringNode;

/**
 * Com\Tecnick\TestRest\RestContext
 *
 * @category    Library
 * @package     Com\Tecnick\TestRest
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 MediaSift Ltd. <http://datasift.com>
 * @license     https://opensource.org/licenses/MIT The MIT License (MIT) - see the LICENSE file
 * @link        https://github.com/tecnickcom/tc-lib-testrest
 */
class RestContext extends \Com\Tecnick\TestRest\HeaderContext
{
    /**
     * Check if the response has the specified property.
     * Get the object value given the property name in dot notation.
     *
     * Example:
     *     Then the response has a "field.name" property
     *
     * @param string   $property  Property name in dot-separated format.
     * @param StdClass $obj       Object to process.
     *
     * @return Object value
     *
     * @Then /^the response has a "([^"]*)" property$/
     */
    public function getObjectValue($property, $obj = null)
    {
        if ($obj === null) {
            $obj = $this->getResponseData();
        }
        // explode property name
        $keys = explode('.', $property);
        foreach ($keys as $key) {
            // extract the array index (if any)
            $kdx = explode('[', $key);
            unset($idx);
            if (!empty($kdx[1])) {
                $key = $kdx[0];
                $idx = substr($kdx[1], 0, -1);
            }
            if (!isset($obj->$key)) {
                $key = '"'.$key.'"';
                if (!isset($obj->$key)) {
                    throw new Exception('Property \''.$property.'\' is not set!');
                }
            }
            $obj = $obj->$key;
            if (isset($idx)) {
                $obj = $obj[$idx];
            }
        }
        return $obj;
    }

    /**
     * Check if the response body content correspond to the specified string.
     *
     * Examples:
     *     Then the the response body equals
     *     """
     *     name@example.com
     *     """
     *
     * @param PyStringNode $value Expected response body content.
     *
     * @Then /^the response body equals$/
     */
    public function theResponseBodyEquals(PyStringNode $value)
    {
        $data = trim($this->response->getBody(true));
        $value = trim((string)$value);
        if ($value !== $data) {
            throw new Exception('Response body value mismatch! (given: '.$value.', match: '.$data.')');
        }
    }

    /**
     * Check if the provided pattern matches the response body string.
     *
     * Example:
     *     Then the response body matches the pattern "/[a-z]+@example\.com/"
     *
     * @param string $pattern Regular expression pattern to search.
     *
     * @Then /^the response body matches the pattern "([^\n]*)"$/
     */
    public function theResponseBodyMatchesThePattern($pattern)
    {
        $value = trim($this->response->getBody(true));
        $result = preg_match($pattern, $value);
        if (empty($result)) {
            throw new Exception(
                'The response body does not matches the pattern \''.$pattern.'\'!'."\n"
            );
        }
    }

    /**
     * Verify if the response is in valid JSON format.
     *
     * Example:
     *     Then the response is JSON
     *
     * @Then /^the response is JSON$/
     */
    public function getResponseData()
    {
        $data = json_decode($this->response->getBody(true));
        if (empty($data)) {
            throw new Exception('Response was not JSON:'."\n\n".$this->response->getBody(true));
        }
        return $data;
    }

    /**
     * Check if the response body content contains the specified JSON data.
     *
     * Examples:
     *     Then the response body contains the JSON data
     *     """
     *     {
     *          "field":"value",
     *          "count":1
     *     }
     *     """
     *
     * @param PyStringNode $value JSON string containing the data expected in the response body.
     *
     * @return array Array containing the actual returned data and the expected one.
     *
     * @Then /^the response body contains the JSON data$/
     */
    public function theResponseBodyContainsTheJsonData(PyStringNode $value)
    {
        $data = json_decode($this->response->getBody(true), true);
        $value = json_decode((string)$value, true);
        $diff = $this->getArrayDiff($value, $data);
        if (!empty($diff)) {
            throw new Exception('Response body value mismatch! Missing item(s):'."\n".print_r($diff, true));
        }
        return array($data, $value);
    }

    /**
     * Check if the response body JSON structure and contents exactly matches the provided one.
     *
     * Examples:
     *     Then the response body JSON equals
     *     """
     *     {
     *          "field":"value",
     *          "count":1
     *     }
     *     """
     *
     * @param PyStringNode $value JSON string containing the data expected in the response body.
     *
     * @Then /^the response body JSON equals$/
     */
    public function theResponseBodyJsonEquals(PyStringNode $value)
    {
        list($data, $value) = $this->theResponseBodyContainsTheJsonData($value);
        $diff = $this->getArrayDiff($data, $value);
        if (!empty($diff)) {
            throw new Exception('Response body value mismatch! Extra item(s):'."\n".print_r($diff, true));
        }
    }

    /**
     * Check the type of the specified property.
     *
     * Examples:
     *     Then the type of the "field.name" property should be "string"
     *     Then the type of the "field.count" property should be "integer"
     *
     * @param string $propertyName  Name of the property to check.
     * @param string $type          Expected type of the property (boolean, integer, double, float, string, array)
     *
     * @Then /^the type of the "([^"]*)" property should be "([^"]+)"$/
     */
    public function theTypeOfThePropertyShouldBe($propertyName, $type)
    {
        $value = $this->getObjectValue($propertyName);
        $valueType = gettype($value);
        if ($valueType !== $type) {
            throw new Exception(
                'Property \''.$propertyName.'\' is of type \''.$valueType
                .'\' and not \''.$type.'\'!'."\n"
            );
        }
    }

    /**
     * Check the value of the specified property.
     * NOTE: the dot notation is supported for the property name (e.g. parent.child[0].value).
     *
     * Examples:
     *     Then the "success" property equals "true"
     *     Then the "database[0].hostname" property equals "127.0.0.1"
     *
     * @param string $propertyName  Name of the property to check.
     * @param string $propertyValue Expected value of the property.
     *
     * @Then /^the "([^"]+)" property equals "([^\n]*)"$/
     */
    public function thePropertyEquals($propertyName, $propertyValue)
    {
        try {
            $value = $this->getObjectValue($propertyName);
        } catch (Exception $e) {
            if ($propertyValue == 'null') {
                return;
            }
            throw new Exception($e->getMessage());
        }
        // cast boolean values
        if (($propertyValue === 'true') || ($propertyValue === 'false')) {
            $value = ((bool)$value ? 'true' : 'false');
        } elseif (is_array($value)) {
            $apv = json_decode($propertyValue, true);
            $asv = json_decode(json_encode($value), true);
            // compare arrays
            if ($apv == $asv) {
                return;
            }
            throw new Exception(
                'Property value mismatch! (given: '.$propertyValue.', match: '.json_encode($value).')'
            );
        }
        // compare values
        $value = (string)$value;
        if ($value !== $propertyValue) {
            throw new Exception('Property value mismatch! (given: '.$propertyValue.', match: '.$value.')');
        }
    }

    /**
     * Check if the specified property is an array or object with the indicated number of items.
     *
     * Examples:
     *     Then the "data" property is an "array" with "5" items
     *     Then the "data" property is an "object" with "10" items
     *
     * @param string $propertyName  Name of the property to check.
     * @param string $type          Type of property ("array" or "object").
     * @param string $numitems      Expected number of elements in the array or object.
     *
     * @Then /^the "([^"]*)" property is an "(array|object)" with "(null|\d+)" item[s]?$/
     */
    public function thePropertyIsAnWithItems($propertyName, $type, $numitems)
    {
        try {
            $this->theTypeOfThePropertyShouldBe($propertyName, $type);
        } catch (Exception $e) {
            if ($numitems == 'null') {
                return;
            }
            throw new Exception($e->getMessage());
        }
        $value = count((array)$this->getObjectValue($propertyName));
        if ($value != $numitems) {
            throw new Exception('Property count mismatch! (given: '.$numitems.', match: '.$value.')');
        }
    }

    /**
     * Check the length of the property value.
     *
     * Example:
     *     Then the length of the "datetime" property should be "19"
     *
     * @param string $propertyName  Name of the property to check.
     * @param string $type          Expected string length of the property.
     *
     * @Then /^the length of the "([^"]*)" property should be "(\d+)"$/
     */
    public function theLengthOfThePropertyShouldBe($propertyName, $length)
    {
        $value_length = strlen($this->getObjectValue($propertyName));
        if ($value_length !== (int)$length) {
            throw new Exception(
                'The lenght of property \''.$propertyName.'\' is \''.$value_length
                .'\' and not \''.$length.'\'!'."\n"
            );
        }
    }

    /**
     * Check if the value of the specified property matches the defined regular expression pattern
     *
     * Example:
     *     Then the value of the "datetime" property matches the pattern
     *     "/^[0-9]{4}[\-][0-9]{2}[\-][0-9]{2} [0-9]{2}[:][0-9]{2}[:][0-9]{2}$/"
     *
     * @param string $propertyName  Name of the property to check.
     * @param string $pattern       Expected regular expression pattern of the property.
     *
     * @Then /^the value of the "([^"]*)" property matches the pattern "([^\n]*)"$/
     */
    public function theValueOfThePropertyMatchesThePattern($propertyName, $pattern)
    {
        $value = (string)$this->getObjectValue($propertyName);
        $result = preg_match($pattern, $value);
        if (empty($result)) {
            throw new Exception(
                'The value of property \''.$propertyName.'\' is \''.$value
                .'\' and does not matches the pattern \''.$pattern.'\'!'."\n"
            );
        }
    }
}
