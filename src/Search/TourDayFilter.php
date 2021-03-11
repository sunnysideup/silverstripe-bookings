<?php

namespace Sunnysideup\Bookings\Search;

use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\Filters\ExactMatchFilter;

class TourDayFilter extends ExactMatchFilter
{
    /**
     *@return SQLQuery
     **/
    public function apply(DataQuery $query)
    {
        $value = $this->getValue();
        if ($value) {
            $query->where('DAYOFWEEK("Date") = '.$value.'');
        }
        return $query;
    }
}
