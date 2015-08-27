<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;

/**
 * Override of the date filter to allow the use of the time part
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilter extends DateFilter
{
    /** @staticvar string */
    const GREATER_THAN_OR_EQUALS_WITH_TIME = ">= WITH TIME";

    /**
     * Override to add new operator and to work on time
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (static::GREATER_THAN_OR_EQUALS_WITH_TIME === $operator) {
            if ($value instanceof \DateTime) {
                $dateTimeValue = $value;
            } else {
                try {
                    $dateTimeValue = new \DateTime($value);
                } catch (\Exception $e) {
                    throw InvalidArgumentException::expected(
                        $field,
                        'DateTime object or new DateTime() compatible string. Error:'.$e->getMessage(),
                        'filter',
                        'date_time',
                        is_string($value) ? $value : gettype($value)
                    );
                }
            }

            $normalizedField = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
            $this->qb->field($normalizedField)->gte($dateTimeValue->getTimestamp());
        } else {
            parent::addFieldFilter($field, $operator, $value, $locale, $scope, $options);
        }
    }
}
