<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute to flat array processor.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeToFlatArrayProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /** @staticvar string */
    const ITEM_SEPARATOR = ',';

    /** @var LocaleManager */
    protected $localeManager;

    /** @var NormalizerInterface */
    protected $transNormalizer;

    /**
     * @param NormalizerInterface $transNormalizer
     * @param LocaleManager       $localeManager
     */
    public function __construct(NormalizerInterface $transNormalizer, LocaleManager $localeManager)
    {
        $this->transNormalizer = $transNormalizer;
        $this->localeManager   = $localeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($attribute)
    {
        $context = [
            'locales' => $this->localeManager->getActiveCodes(),
        ];

        $flatAttribute = [
                'type' => $attribute->getAttributeType(),
                'code' => $attribute->getCode()
            ] + $this->transNormalizer->normalize($attribute, null, $context);

        $flatAttribute = array_merge(
            $flatAttribute,
            [
                'group'                   => ($attribute->getGroup()) ? $attribute->getGroup()->getCode() : null,
                'unique'                  => (int) $attribute->isUnique(),
                'useable_as_grid_filter'  => (int) $attribute->isUseableAsGridFilter(),
                'allowed_extensions'      => implode(self::ITEM_SEPARATOR, $attribute->getAllowedExtensions()),
                'metric_family'           => $attribute->getMetricFamily(),
                'default_metric_unit'     => $attribute->getDefaultMetricUnit(),
                'localizable'             => (int) $attribute->isLocalizable(),
                'scopable'                => (int) $attribute->isScopable(),
                'families'                => $this->getAttributeFamilyCodes($attribute),
            ]
        );

        return $flatAttribute;
    }

    /**
     * Return the list of all the family codes of the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return string
     */
    protected function getAttributeFamilyCodes(AttributeInterface $attribute)
    {
        $families = $attribute->getFamilies();

        $familyCodes = [];
        if (null !== $families) {
            foreach ($families as $family) {
                $familyCodes[] = $family->getCode();
            }
        }

        return implode(self::ITEM_SEPARATOR, $familyCodes);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
