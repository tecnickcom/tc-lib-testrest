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
use \Behat\Behat\Context\BehatContext;

/**
 * Com\Tecnick\TestRest\BaseContext
 *
 * @category    Library
 * @package     Com\Tecnick\TestRest
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015 MediaSift Ltd. <http://datasift.com>, 2016 Tecnick.com LTD <http://www.tecnick.com>
 * @license     https://opensource.org/licenses/MIT The MIT License (MIT) - see the LICENSE file
 * @link        https://github.com/tecnickcom/tc-lib-testrest
 */
class BaseContext extends BehatContext
{
    /**
     * Context parameters defined in behat.yml (default.context.parameters...)
     *
     * @var array
     */
    protected static $parameters = array();

    /**
     * Guzzle Client used fot HTTP requests
     *
     * @var \Guzzle\Service\Client
     */
    protected $client = null;

    /**
     * Object containing the data to exchange
     *
     * @var stdClass
     */
    protected $restObj = null;

    /**
     * Array containing the header data to send
     *
     * @var array
     */
    protected $reqHeaders = array();

    /**
     * HTTP method (get, head, delete, post, put, patch)
     *
     * @var string
     */
    protected $restObjMethod = 'get';

    /**
     * Response object
     *
     * @var stdClass
     */
    protected $response = null;

    /**
     * The URL of the request
     *
     * @var string
     */
    protected $requestUrl = null;

    /**
     * Initializes the BeHat context for every scenario
     *
     * @param array $parameters Context parameters defined in behat.yml (default.context.parameters...)
     */
    public function __construct(array $parameters)
    {
        $this->client = new \Guzzle\Service\Client();
        $this->client->setDefaultOption('exceptions', false); // disable exceptions: we want to test error responses
        self::$parameters = $parameters;
        $this->restObj = new \stdClass();
    }

    /**
     * Get the value of the specified parameter.
     * The context parameters are defined in behat.yml (default.context.parameters...).
     *
     * @param string $name Parameter name
     *
     * @return mixed Parameter value
     */
    public function getParameter($name)
    {
        if (empty(self::$parameters)) {
            throw new Exception('Context Parameters not loaded!');
        }
        return ((isset(self::$parameters[$name])) ? self::$parameters[$name] : null);
    }

    /**
     * Setup the environment before every feature:
     *     - clear cache;
     *     - setup database;
     *
     * @BeforeFeature
     */
    public static function setupEnvironment()
    {
        self::clearCache();
        self::setupDatabase();
    }

    /**
     * Flush the memcached before every scenario (if configured).
     * The memcache settings are defined in behat.yml
     *
     * @BeforeScenario
     */
    public static function clearCache()
    {
        // clean the APC cache (if any)
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache('user');
        }

        if (!empty(self::$parameters['memcached'])) {
            $mmd = new \Memcached();
            $mmd->addServer(self::$parameters['memcached']['host'], self::$parameters['memcached']['port']);
            $mmd->flush();
        }
    }

    /**
     * Setup the database before every feature (if configured).
     * The database settings are defined in behat.yml
     */
    public static function setupDatabase()
    {
        if (empty(self::$parameters['db'])) {
            // no database defined
            return;
        }

        // load the SQL queries to process
        $sql = "\n".file_get_contents(__DIR__ . self::$parameters['db']['sql_schema'])
            ."\n".file_get_contents(__DIR__ . self::$parameters['db']['sql_data'])."\n";

        // split sql string into single line SQL statements
        $sql = str_replace("\r", '', $sql);                         // remove CR
        $sql = preg_replace("/\/\*([^\*]*)\*\//si", ' ', $sql);     // remove comments (/* ... */)
        $sql = preg_replace("/\n([\s]*)\#([^\n]*)/si", '', $sql);   // remove comments (lines starting with '#')
        $sql = preg_replace("/\n([\s]*)\-\-([^\n]*)/si", '', $sql); // remove comments (lines starting with '--')
        $sql = preg_replace("/;([\s]*)\n/si", ";\r", $sql);         // mark valid new lines
        $sql = str_replace("\n", ' ', $sql);                        // replace new lines with a space character
        $sql = preg_replace("/(;\r)$/si", '', $sql);                // remove last ";\r"
        $sql_queries = explode(";\r", trim($sql));

        // connect to the database
        if (!empty(self::$parameters['db']['path'])) {
            // filesystem-style databases (e.g. SQLite)
            $dsn = self::$parameters['db']['driver']
                .':'. __DIR__ . self::$parameters['db']['path'];
            self::$parameters['db']['username'] = null;
            self::$parameters['db']['password'] = null;
        } else {
            $dsn = self::$parameters['db']['driver']
                .':dbname='.self::$parameters['db']['database']
                .';host='.self::$parameters['db']['host']
                .';port='.self::$parameters['db']['port'];
        }
        $dbtest = new \PDO($dsn, self::$parameters['db']['username'], self::$parameters['db']['password']);

        // try to wrap queries
        $dbtest->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        @$dbtest->query('BEGIN');
        @$dbtest->query('SET FOREIGN_KEY_CHECKS=0');

        // execute all queries
        $dbtest->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        foreach ($sql_queries as $query) {
            if (!trim($query, "; \t\n\r\0\x0B")) {
                continue;
            }
            try {
                $dbtest->query($query);
            } catch (Exception $exc) {
                throw new Exception('Error executing the query: '.$query.' -- '.$exc);
            }
        }

        // close query wrappers
        $dbtest->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        @$dbtest->query('SET FOREIGN_KEY_CHECKS=1');
        @$dbtest->query('COMMIT');

        $dbtest = null; // close the database connection
    }

    /**
     * Delays the program execution for the given number of seconds.
     *
     * Examples:
     *     Then wait "1" second
     *     Then wait "3" seconds
     *
     * @param int $delay Halt time in seconds.
     *
     * @Then /^wait "(\d+)" second[s]?$/
     */
    public function waitSeconds($delay)
    {
        sleep($delay);
    }

    /**
     * Print the last raw response.
     *
     * Example:
     *     Then echo last response
     *
     * @Then /^echo last response$/
     */
    public function echoLastResponse()
    {
        $this->printDebug($this->requestUrl."\n\n".$this->response);
    }

    /**
     * Returns the difference of two arrays
     *
     * @param array $arr1 The array to compare from.
     * @param array $arr2 The array to compare against.
     *
     * @return array Returns an array containing all the entries from $arr1 that are not present in $arr2.
     */
    protected function getArrayDiff(array $arr1, array $arr2)
    {
        $diff = array();
        foreach ($arr1 as $key => $val) {
            if (array_key_exists($key, $arr2)) {
                if (is_array($val)) {
                    $tmpdiff = $this->getArrayDiff($val, $arr2[$key]);
                    if (!empty($tmpdiff)) {
                        $diff[$key] = $tmpdiff;
                    }
                } elseif ($arr2[$key] !== $val) {
                    $diff[$key] = $val;
                }
            } elseif (!is_int($key) || !in_array($val, $arr2)) {
                $diff[$key] = $val;
            }
        }
        return $diff;
    }
}
