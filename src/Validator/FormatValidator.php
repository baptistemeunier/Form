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

namespace Berlioz\Form\Validator;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Validator\Constraint\FormatConstraint;

/**
 * Class FormatValidator.
 *
 * @package Berlioz\Form\Validator
 */
class FormatValidator extends AbstractValidator implements ValidatorInterface
{
    /** @var string Format */
    private $format;

    /**
     * FormatValidator constructor.
     *
     * @param string $format Format (REGEX)
     * @param string $constraint Constraint class
     *
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function __construct(string $format, string $constraint = FormatConstraint::class)
    {
        parent::__construct($constraint);
        $this->format = $format;
    }

    /**
     * @inheritdoc
     */
    public function validate(ElementInterface $element): array
    {
        $value = $element->getValue();
        $constraints = [];

        if (preg_match($this->format, $value) !== 1) {
            $constraints[] =
                new $this->constraint(
                    [
                        'format' => $this->format,
                    ]
                );
        }

        return $constraints;
    }
}