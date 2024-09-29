<?php

namespace Sunnysideup\Bookings\Search;

use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\Filters\ExactMatchFilter;

class TourDateFilter extends ExactMatchFilter
{
    /**
     * @return DataQuery
     */
    public function apply(DataQuery $query)
    {
        $value = $this->getValue();
        if ($value) {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: strtotime($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            $value = strtotime((string) $value);
            $query->where(
                ['Date' => date('Y-m-d', $value)]
            );
        }

        return $query;
    }
}
