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

use \Behat\Behat\Context\BehatContext;

/**
 * FeatureContext
 *
 * @category    Library
 * @package     Com\Tecnick\TestRest
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 MediaSift Ltd. <http://datasift.com>
 * @license     https://opensource.org/licenses/MIT The MIT License (MIT) - see the LICENSE file
 * @link        https://github.com/tecnickcom/tc-lib-testrest
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes the BeHat feature context.
     *
     * @param array $parameters Context parameters defined in behat.yml (default.context.parameters...)
     */
    public function __construct(array $parameters)
    {
        $this->useContext('CustomContext', new CustomContext($parameters));
    }
}
