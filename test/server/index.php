<?php
// @codingStandardsIgnoreFile
/**
 * Test Server main example
 *
 * @category    Library
 * @package     Com\Tecnick\TestRest
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 MediaSift Ltd. <http://datasift.com>, 2016 Nicola Asuni Tecnick.com LTD
 * @license     https://opensource.org/licenses/MIT The MIT License (MIT) - see the LICENSE file
 * @link        https://github.com/tecnickcom/tc-lib-testrest
 */

/**
 * Fetch all HTTP request headers.
 * This has been added as "getallheaders" do not work with php 5.4.
 *
 * @return array
 */
function getAllHttpHeaders()
{
    $headers = '';
    foreach ($_SERVER as $name => $value) {
        if (strpos($name, 'HTTP_') === 0) {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
}

// data to be returned
$data = json_encode(
    array(
        'success'   => true,
        'timestamp' => time(),
        'datetime'  => gmdate('Y-m-d H:i:s'),
        'header'    => getAllHttpHeaders(),
        'data'      => $_REQUEST,
        'raw'       => file_get_contents('php://input') // read raw data from the request body
    )
);

header('Content-Type: application/json');
header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
header('Pragma: public');
header('Expires: Thu, 04 jan 1973 00:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Content-Disposition: inline; filename="'.md5($data).'.json";');
echo $data;
