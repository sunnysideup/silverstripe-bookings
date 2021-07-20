<?php

namespace Sunnysideup\Bookings\Pages;

use Page;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\DataObject;

class TourBookingPage extends Page
{
    private static $table_name = 'TourBookingPage';

    private static $db = [
        'OnLocationCheckinMessage' => 'Text',
        'NotOnLocationCheckinMessage' => 'Text',
    ];

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
            if ($obj) {
                return $obj->Link($action);
            }
            user_error('You need to create a TourBookingPage');
        }
        user_error('Action ' . $action . ' is not found. Available actions are: ' . implode(', ', array_keys($allowedActions)));

        return 'error';
    }

    public function CalendarLink()
    {
        return $this->Link('calendar');
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab(
            'Root.Messages',
            [
                TextareaField::create(
                    'OnLocationCheckinMessage',
                    'Checkin Message on location'
                )
                    ->setDescription('e.g. Complete the form below to check in for your tour.'),
                TextareaField::create(
                    'NotOnLocationCheckinMessage',
                    'Checkin Message NOT on location'
                )
                    ->setDescription('e.g. You can only check-in on location usign our amazing wifi. Please make your way ...'),
            ]
        );

        return $fields;
    }
}
