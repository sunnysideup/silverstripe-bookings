<?php

namespace Sunnysideup\Bookings\Search;

use ExactMatchFilter;
use DataQuery;
use Date;


class TourDate_Filter extends ExactMatchFilter
{

    /**
     *@return SQLQuery
     **/
    public function apply(DataQuery $query)
    {
        $value = $this->getValue();
        if ($value) {
            $date = new Date();
            $date->setValue($value);
            $formattedDate = $date->format('Y-m-d');
            $query->where(
                ['Date' => $formattedDate]
            );
        }
        return $query;
    }
}

