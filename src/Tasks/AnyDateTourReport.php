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

class AnyDateTourReport extends MonthlyTourReport
{

    /**
     * @var string
     */
    private static $segment = 'anydaytourreport';
    /**
     * @var string
     */
    protected $title = 'Any Date Tour Report. You can specify using ?from=2011-11-23&until=2012-07-23';

    /**
     * @var string
     */
    protected $description = 'Generates Any Date Tour Report';

    public function setStartAndEndDate(?HTTPRequest $request = null)
    {
        if ($request) {
            $this->request = $request;
            $this->startDate = $this->request->getVar('from') ?: date('Y-m-d');
            $this->endDate = $this->request->getVar('until') ?: date('Y-m-d');
        } else {
            $year = date('Y');
            $this->startDate = $year . "-01-01";
            $this->endDate = $year . "-12-31";
        }
    }
}
