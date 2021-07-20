<?php

namespace Sunnysideup\Bookings\Pages;

use Page;

use SilverStripe\Core\Config\Config;

use SilverStripe\Core\Injector\Injector;

use SilverStripe\ORM\DataObject;

use Sunnysideup\Bookings\Pages\TourBookingPageController;

class TourBookingPage extends Page
{


    /**
     * @param string $action
     *
     * @return string
     */
    public static function find_link(?string $action = null): ?string
    {
        $actionToTest = null;
        $allowedActions = [];
        if ($action) {
            $allowedActions = Config::inst()->get(TourBookingPageController::class, 'allowed_actions');
            $actionToTest = explode('/', $action)[0];
        }
        if (null === $actionToTest || isset($allowedActions[$actionToTest])) {
            $obj = DataObject::get_one(TourBookingPage::class);
            if($obj) {
                return $obj->Link($action);
            } else {
                user_error('You need to create a TourBookingPage');
            }
        }
        user_error('Action ' . $action . ' is not found. Available actions are: ' . implode(', ', array_keys($allowedActions)));

        return 'error';
    }

    public function CalendarLink()
    {
        return $this->Link('calendar');
    }

}
