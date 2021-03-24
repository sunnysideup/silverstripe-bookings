<?php

namespace Sunnysideup\Bookings\Forms\Fields;

use SilverStripe\Forms\TextField;

class TourDateFilterField extends TextField
{
    public function getAttributes($attributes = null)
    {
        $attr = parent::getAttributesHTML($attributes);
        $attr['placeholder'] = 'Use Next Tue or 11 Jan 2033';

        return $attr;
    }
}
