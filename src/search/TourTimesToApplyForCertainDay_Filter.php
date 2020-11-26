<?php

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
            $date = new Date();
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
