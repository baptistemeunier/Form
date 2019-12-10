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

namespace Berlioz\Form\Validator;

use Berlioz\Form\Element\ElementInterface;

/**
 * Interface ValidatorInterface.
 *
 * @package Berlioz\Form\Validator
 */
interface ValidatorInterface
{
    /**
     * Validate.
     *
     * @param \Berlioz\Form\Element\ElementInterface $value
     *
     * @return \Berlioz\Form\Validator\Constraint\ConstraintInterface[]
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function validate(ElementInterface $value): array;
}