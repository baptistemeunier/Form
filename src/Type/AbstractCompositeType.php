<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\Type;

use Berlioz\Form\Element\AbstractElement;
use Berlioz\Form\Group;

/**
 * Class AbstractCompositeType.
 *
 * @package Berlioz\Form\Type
 */
abstract class AbstractCompositeType extends Group implements TypeInterface
{
    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritdoc
     */
    public function getFinalValue()
    {
        return AbstractElement::getFinalValue();
    }
}