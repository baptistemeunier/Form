<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2019 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\View;

use ArrayAccess;
use IteratorAggregate;

/**
 * Interface TraversableViewInterface.
 *
 * @package Berlioz\Form\View
 */
interface TraversableViewInterface extends ViewInterface, IteratorAggregate, ArrayAccess
{
}