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

namespace Berlioz\Form\Hydrator;

use ArrayAccess;
use Berlioz\Form\Collection;
use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\HydratorException;
use Exception;

/**
 * Class CollectionHydrator.
 *
 * @package Berlioz\Form\Hydrator
 */
class CollectionHydrator extends AbstractHydrator
{
    /** @var \Berlioz\Form\Collection Collection */
    private $collection;

    /**
     * CollectionHydrator constructor.
     *
     * @param \Berlioz\Form\Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @inheritdoc
     * @return \Berlioz\Form\Collection
     */
    public function getElement(): ElementInterface
    {
        return $this->collection;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(&$mapped)
    {
        $subMapped = $this->getSubMapped($this->getElement(), $mapped);
        $isArray = is_array($subMapped);
        $isArrayAccess = $subMapped instanceof ArrayAccess;

        if (!$isArray && !$isArrayAccess) {
            throw new HydratorException('Collection field must be an array or an \ArrayAccess object');
        }

        // Prototype
        $prototype = $this->collection->getPrototype();

        /** @var \Berlioz\Form\Element\ElementInterface $element */
        foreach ($this->collection as $key => $element) {
            if (!isset($subMapped[$key])) {
                if (is_null($subMapped[$key] = $this->createObject($prototype))) {
                    $subMapped[$key] = $element->getFinalValue();
                    continue;
                }
            }

            $hydrator = $this->locateHydrator($element);
            $hydrator->hydrate($subMapped[$key]);
        }

        // Is array? Need to set array, because no reference on arrays.
        if ($isArray) {
            $propertyName = $this->getElement()->getName();

            try {
                b_set_property_value($mapped, $propertyName, $subMapped);
            } catch (Exception $e) {
                throw new HydratorException(sprintf('Unable to find property setter of "%s" on object "%s"', $propertyName, get_class($mapped)), 0, $e);
            }
        }
    }
}