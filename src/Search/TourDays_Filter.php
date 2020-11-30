<?php

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
