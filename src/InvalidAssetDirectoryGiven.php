<?php
/**
 * Exception class for an invalid directory
 *
 * (c) Moisés Barquín <moises.barquin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * PHP version 7.0
 *
 * @package    reactSlim
 * @subpackage reactSlim
 * @author     Nigel Greenway <github@futurepixels.co.uk>
 * @copyright  (c) 2016, Moisés Barquín <moises.barquin@gmail.com>
 * @version    GIT: $Id$
 */
namespace mbarquin\reactSlim;

final class InvalidAssetDirectoryGiven extends \InvalidArgumentException
{
    /**
     * @param string $directory
     */
    public function __construct($directory)
    {
        return new parent(
            sprintf(
                'The directory \'%s\' given does not exist',
                $directory
            )
        );
    }
}