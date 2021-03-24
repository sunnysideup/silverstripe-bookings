<?php

namespace Sunnysideup\Bookings\Search;

use SilverStripe\ORM\DataQuery;
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
            $value = strtotime($value);
            $query->where(
                ['Date' => Date('Y-m-d', $value)]
            );
        }
        return $query;
    }
}
