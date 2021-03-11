<?php
namespace Sunnysideup\Bookings\Forms\Fields;

use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\Filters\ExactMatchFilter;

class TourDayFilterField extends DropdownField
{

    public function getSource()
    {
        return [
            0 => '-- Any Day of the week --',
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday',
        ];
    }

}
