<?php
/**
 * Test Server
 *
 * @category    Library
 * @package     Com\Tecnick\TestRest
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 MediaSift Ltd. <http://datasift.com>
 * @license     https://opensource.org/licenses/MIT The MIT License (MIT) - see the LICENSE file
 * @link        https://github.com/tecnickcom/tc-lib-testrest
 */

$data = json_encode(
    array(
        'success'   => true,
        'timestamp' => time(),
        'datetime'  => gmdate('Y-m-d H:i:s'),
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
