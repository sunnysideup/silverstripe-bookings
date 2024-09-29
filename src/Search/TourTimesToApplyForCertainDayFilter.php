<?php

namespace Sunnysideup\Bookings\Search;

use DateTime;
use Exception;
use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\Filters\ExactMatchFilter;
use Sunnysideup\Bookings\Model\DateInfo;

class TourTimesToApplyForCertainDayFilter extends ExactMatchFilter
{
    /**
     * @return DataQuery
     */
    public function apply(DataQuery $query)
    {
        $value = $this->getValue();
        if ($value) {
            $where = '"DateInfo"."ID" = 0';
            if (false === $value || null === $value || (is_string($value) && ! strlen((string) $value))) {
                // don't try to evaluate empty values with strtotime() below, as it returns "1970-01-01" when it should be
                // saved as NULL in database
                $this->value = null;

                return $query;
            }

            // @todo This needs tidy up (what if you only specify a month and a year, for example?)
            if (is_array($value)) {
                if (! empty($value['Day']) && ! empty($value['Month']) && ! empty($value['Year'])) {
                    $this->value = $value['Year'] . '-' . $value['Month'] . '-' . $value['Day'];

                    return;
                }
                // return nothing (so checks below don't fail on an empty array)
                return $query;
            }

            // Default to NZ date format - strtotime expects a US date
            if (preg_match('#^(\d+)/(\d+)/(\d+)$#', $value, $parts)) {
                $value = "{$parts[2]}/{$parts[1]}/{$parts[3]}";
            }

            if (is_numeric($value)) {
                $value = date('Y-m-d', $value);
            } elseif (is_string($value)) {
                try {
                    $date = new DateTime($value);
                    $value = $date->Format('Y-m-d');
                } catch (Exception $exception) {
                    return $query;
                }
            }
            $date = new DBDate();
            $date->setValue($value);
            $formattedDate = $date->format('y-MM-dd');

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: strtotime($
  * EXP: SS5 change
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            $dateTS = strtotime((string) $formattedDate);
            if ($dateTS > time()) {
                $dateInfo = DateInfo::best_match_for_date($dateTS);
                if ($dateInfo) {
                    $where = '"DateInfo"."ID" = ' . $dateInfo->ID;
                }
            }
            $query->where($where);
        }

        return $query;
    }
}
