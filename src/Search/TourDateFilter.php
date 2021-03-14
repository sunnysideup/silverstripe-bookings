<?php

namespace Sunnysideup\Bookings\Search;

use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\Filters\ExactMatchFilter;

class TourDateFilter extends ExactMatchFilter
{
    /**
     * @param DataQuery $query
     * @return DataQuery
     **/
    public function apply(DataQuery $query)
    {
        $value = $this->getValue();
        if ($value) {
            $date = new DBDate();
            $date->setValue($value);
            $formattedDate = $date->format('Y-m-d');
            $query->where(
                ['Date' => $formattedDate]
            );
        }
        return $query;
    }
}
