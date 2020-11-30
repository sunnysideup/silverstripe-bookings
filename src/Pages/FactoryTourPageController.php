<?php

namespace Sunnysideup\Bookings\Pages;

class FactoryTourPageController extends TourBookingPageController
{
    /**
     * provides a links to Google Maps to search for directions
     * @return string
     */
    public function DirectionsLink()
    {
        return 'https://www.google.com/maps/dir//' . urlencode('49 Saxton Road, Stoke, Nelson 7011, New Zealand');
    }

    public function IntroLeftOrShowSpecialInfo()
    {
        return $this->IntroLeft || $this->ShowSpecialInfo;
    }
}
