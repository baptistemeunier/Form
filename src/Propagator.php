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

namespace Berlioz\Form;

use Berlioz\Form\Exception\PropagationException;
use Berlioz\Form\Type\AbstractType;

class Propagator
{
    /** @var \Berlioz\Form\Form Form */
    private $form;

    /**
     * Propagator constructor.
     *
     * @param \Berlioz\Form\Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * Get form.
     *
     * @return \Berlioz\Form\Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * Get or create mapped object.
     *
     * @param \Berlioz\Form\ElementInterface $formElement
     * @param object|array $mapped
     * @param bool $created New element?
     *
     * @return mixed
     * @throws \Berlioz\Form\Exception\PropagationException
     * @throws \ReflectionException
     */
    private function getOrCreateMappedObject(ElementInterface $formElement, $mapped, bool &$created = null)
    {
        $created = false;
        $exists = false;
        $value = b_get_property_value($mapped, $formElement->getName(), $exists);

        if (!$exists) {
            throw new PropagationException(sprintf('Unable to find property "%s" in mapped object for "%s" form element', $formElement->getName(), $formElement->getName()));
        }

        if (is_null($value)) {
            $created = true;

            return $this->createMappedObject($formElement);
        }

        return $value;
    }

    /**
     * Create mapped object.
     *
     * @param \Berlioz\Form\ElementInterface $formElement
     *
     * @return null|object
     * @throws \Berlioz\Form\Exception\PropagationException
     */
    private function createMappedObject(ElementInterface $formElement)
    {
        if (!is_null($dataType = $formElement->getOption('data_type'))) {
            try {
                return (new \ReflectionClass($dataType))->newInstance();
            } catch (\ReflectionException $e) {
                throw new PropagationException(sprintf('Unable to create object of type "%s" to do propagation in mapped object', $dataType), 0, $e);
            }
        }

        return null;
    }

    /**
     * Propagate values of group to mapped object given.
     *
     * @param \Berlioz\Form\Group $group
     * @param object|array $mapped
     *
     * @throws \Berlioz\Form\Exception\PropagationException
     */
    private function propagateGroup(Group $group, &$mapped)
    {
        if ($group->getOption('mapped', false, true)) {
            if (is_null($mapped)) {
                $mapped = [];
            }

            // Handle group transformer
            $transformer = $group->getTransformer();
            $transformedMapped = null;
            $isTransformed = false;

            if (!empty($transformer)) {
                $isTransformed = true;
                $transformedMapped = $mapped;
                $mapped = [];
            }

            /** @var \Berlioz\Form\ElementInterface $item */
            foreach ($group as $key => $item) {
                if ($item instanceof Group) {
                    $mappedObjectCreated = false;
                    $subMapped = $mapped;

                    if (!is_null($item->getName())) {
                        if (!$isTransformed) {
                            $subMapped = $this->getOrCreateMappedObject($item, $subMapped, $mappedObjectCreated);
                        }

                        if (!isset($subMapped)) {
                            $subMapped = [];
                        }
                    }

                    $this->propagateGroup($item, $subMapped);

                    if (!is_null($item->getName()) && $mappedObjectCreated) {
                        $this->propagateValue($mapped, $item->getName(), $subMapped);
                    }
                } elseif ($item instanceof Collection) {
                    $this->propagateCollection($item, $mapped);
                } elseif ($item instanceof AbstractType) {
                    $this->propagateField($item, $mapped);
                } else {
                    throw new PropagationException;
                }
            }

            if ($isTransformed) {
                $transformedMapped = $transformer->fromForm($mapped);
                $mapped = $transformedMapped;
            }
        }
    }

    /**
     * Propagate values of collection to mapped object given.
     *
     * @param \Berlioz\Form\Collection $collection
     * @param object|array $mapped
     *
     * @throws \Berlioz\Form\Exception\PropagationException
     */
    private function propagateCollection(Collection $collection, &$mapped)
    {
        if ($collection->getOption('mapped', false, true)) {
            if (is_array($mapped)) {
                $subMapped = [];
            } else {
                $subMapped = $this->getOrCreateMappedObject($collection, $mapped);

                if (is_null($subMapped)) {
                    $subMapped = [];
                }
            }

            if (!(is_array($subMapped) || $subMapped instanceof \Traversable)) {
                throw new PropagationException;
            }

            if (is_null($prototype = $collection->getOption('prototype'))) {
                throw new PropagationException;
            }

            $treatedChildren = [];
            /** @var \Berlioz\Form\ElementInterface $item */
            foreach ($collection as $i => $item) {
                if (!is_null($item->getValue())) {
                    // Get mapped
                    if (!array_key_exists($i, $subMapped) || empty($itemMapped = $subMapped[$i])) {
                        $itemMapped = $this->createMappedObject($item);
                    }

                    if ($item instanceof Group) {
                        $this->propagateGroup($item, $itemMapped);
                        $subMapped[$i] = $itemMapped;
                    } elseif ($item instanceof AbstractType) {
                        if (!$item->getOption('data_type')) {
                            $subMapped[$i] = $item->getValue();
                        } else {
                            $this->propagateField($item, $itemMapped);
                            $subMapped[$i] = $itemMapped;
                        }
                    }
                } else {
                    unset($subMapped[$i]);
                }

                $treatedChildren[] = $i;
            }
            //$this->propagateValue($mapped, $collection->getName(), $subMapped);

            // Unset old elements from sub mapped
            foreach ($subMapped as $i => $subSubMapped) {
                if (!in_array($i, $treatedChildren)) {
                    unset($subMapped[$i]);
                }
            }
        }
    }

    /**
     * Propagate value to mapped object given.
     *
     * @param \Berlioz\Form\Type\AbstractType $type
     * @param object|array $mapped
     * @param mixed|null $value
     *
     * @throws \Berlioz\Form\Exception\PropagationException
     */
    private function propagateField(AbstractType $type, &$mapped, $value = null)
    {
        if ($type->getOption('mapped', false, true)) {
            $this->propagateValue($mapped, $type->getName(), $value ?? $type->getValue());
        }
    }

    /**
     * Set value to an object.
     *
     * @param object $mapped
     * @param string $property
     * @param mixed $value
     *
     * @throws \Berlioz\Form\Exception\PropagationException
     * @throws \ReflectionException
     */
    private function propagateValue(&$mapped, string $property, $value)
    {
        if (is_array($mapped)) {
            $mapped[$property] = $value;
        } else {
            if (!b_set_property_value($mapped, $property, $value)) {
                throw new PropagationException(sprintf('Unable to set property "%s" on object "%s"', $property, get_class($mapped)));
            }
        }
    }

    /**
     * Propagate values to mapped object.
     *
     * @throws \Berlioz\Form\Exception\PropagationException
     */
    public function propagate()
    {
        if (!is_null($mapped = $this->getForm()->getMappedObject())) {
            $this->propagateGroup($this->getForm(), $mapped);
        }
    }
}
