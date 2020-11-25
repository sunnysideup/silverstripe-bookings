<?php

namespace Sunnysideup\Bookings\Tasks;

use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\TourBookingSettings;
use Sunnysideup\Geoip\Geoip;

class YearlyTourReport extends MonthlyTourReport
{
    /**
     * @var string
     */
    private static $segment = 'yearlytourreport';

    /**
     * @var string
     */
    protected $title = 'Yearly Tour Report';

    /**
     * @var string
     */
    protected $description = 'Generates Yearly Tour Report';

    public function setStartAndEndDate(?HTTPRequest $request = null)
    {
        if ($request) {
            $this->request = $request;
            $year = $this->request->getVar('year') ?: date('Y');
        } else {
            $year = date('Y');
        }
        $this->startDate = $year . "-01-01";
        $this->endDate = $year . "-12-31";
    }
}
