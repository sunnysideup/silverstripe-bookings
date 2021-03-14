<?php

namespace Sunnysideup\Bookings\Cms;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataObject;
use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\DateInfo;
use Sunnysideup\Bookings\Model\ReferralOption;
use Sunnysideup\Bookings\Model\TimesForTour;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\TourBookingSettings;
use Sunnysideup\Bookings\Model\Waitlister;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class TourBookingsAdmin extends ModelAdmin
{
    public $showImportForm = false;

    public $showSearchForm = [
        Tour::class,
        Booking::class,
        Waitlister::class,
    ];

    private static $managed_models = [
        Tour::class,
        Booking::class,
        Waitlister::class,
    ];

    private static $url_segment = 'tour-bookings';

    private static $menu_title = 'Tour Bookings';

    private static $menu_icon = 'sunnysideup/bookings: client/dist/images/icons/TourBookingsAdmin.png';

    public function getList()
    {
        $list = parent::getList();
        if ($this->modelClass === Tour::class) {
            $mysqlDate = date('Y-m-d', strtotime('-2 days'));
            $list = $list->filter(['Date:GreaterThan' => $mysqlDate]);
        }

        return $list;
    }


}
