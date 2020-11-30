<?php

namespace Sunnysideup\Bookings\Search;





use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\FieldType\DBDate;
use Sunnysideup\Bookings\Model\DateInfo;
use SilverStripe\ORM\Filters\ExactMatchFilter;



class TourTimesToApplyForCertainDay_Filter extends ExactMatchFilter
{

    /**
     *@return SQLQuery
     **/
    public function apply(DataQuery $query)
    {
        $value = $this->getValue();
        if ($value) {
            $where = '"DateInfo"."ID" = 0';
            $date = new DBDate();
            $date->setValue($value);
            $formattedDate = $date->format('Y-m-d');
            $dateTS = strtotime($formattedDate);
            if ($dateTS > time()) {
                $dateInfo = DateInfo::best_match_for_date($dateTS);
                if ($dateInfo) {
                    $where = '"DateInfo"."ID" = '.$dateInfo->ID;
                }
            }
            $query->where($where);
        }
        return $query;
    }
}

