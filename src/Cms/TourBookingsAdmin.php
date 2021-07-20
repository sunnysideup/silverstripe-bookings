<?php

namespace Sunnysideup\Bookings\Cms;

use Colymba\BulkManager\BulkAction\EditHandler;
use Colymba\BulkManager\BulkManager;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use Sunnysideup\Bookings\Forms\Actions\CloseAction;
use Sunnysideup\Bookings\Forms\Actions\OpenAction;
use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\Waitlister;

class TourBookingsAdmin extends ModelAdmin
{
    public $showImportForm = false;

    public $showSearchForm = [
        Tour::class,
        Booking::class,
        Waitlister::class,
    ];

    private static $managed_models = [
        // Tour::class => [
        //     'dataClass' => Tour::class,
        //     'title' => 'Today',
        // ],
        // Tour::class => [
        //     'dataClass' => Tour::class,
        //     'title' => 'This week',
        // ],
        Tour::class => [
            'dataClass' => Tour::class,
            'title' => 'All future tours',
        ],
        Booking::class => [
            'dataClass' => Booking::class,
            'title' => 'All future bookings',
        ],
        Waitlister::class => [
            'dataClass' => Waitlister::class,
            'title' => 'All future Waitlisters',
        ],
    ];

    private static $url_segment = 'tour-bookings-admin';

    private static $menu_title = 'Tour Bookings';

    private static $menu_icon = 'sunnysideup/bookings: client/dist/images/icons/TourBookingsAdmin.png';

    public function getList()
    {
        $list = parent::getList();
        if (TourBookingsConfig::is_model_class($this->modelClass, Tour::class)) {
            $mysqlDate = date('Y-m-d', strtotime('-2 days'));
            $list = $list->filter(['Date:GreaterThan' => $mysqlDate]);
        }

        return $list;
    }

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);
        if (TourBookingsConfig::is_model_class($this->modelClass, Tour::class)) {
            $fields = $form->Fields();
            $gridField = $fields->dataFieldByName($this->sanitiseClassName($this->modelClass));

            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField && $gridField instanceof GridField) {
                $gridFieldConfig = $gridField->getConfig();
                $bmConfig = (new BulkManager([], false))
                    ->addBulkAction(EditHandler::class)
                    ->addBulkAction(CloseAction::class)
                    ->addBulkAction(OpenAction::class)
                ;
                $gridFieldConfig->addComponent($bmConfig);
                $paginator = $gridFieldConfig->getComponentByType(GridFieldPaginator::class);
                if ($paginator) {
                    $paginator->setItemsPerPage(10);
                }
                $gridFieldConfig->getComponentByType(GridFieldAddNewButton::class)->setButtonName('Add one-off tour');

                $toursList1 = $fields->fieldByName('Sunnysideup-Bookings-Model-Tour');
                $fields->removeByName('Sunnysideup-Bookings-Model-Tour');

                $toursList2 = new GridField('Sunnysideup-Bookings-Model-Tour-Todays', '', $toursList1->getList()->filter('Date', date('Y-d-m')), $toursList1->getConfig());
                $toursList2->setForm($form);

                $fields->insertAfter(
                    'Sunnysideup-Bookings-Model-Tour-All',
                    new TabSet(
                        'ToursSetInner',
                        new Tab('TodayTours', "Today's Tours", $toursList1),
                        new Tab('UpcomingTours', 'All Upcoming Tours', $toursList2)
                    )
                );

                $fields->addFieldsToTab(
                    'ToursSetInner.UpcomingTours',
                    [
                        LiteralField::create(
                            'Bookings-Model-Tour-Description',
                            '<p style="margin-top: 15px">Above is a list of all the <strong>upcoming tours</strong> for each day, some are auto-generated, some have been manually added.</p>
                                <p><strong>To set up regular tours</strong> (auto-generated), use the Tour Generator - Rules" tab in "Tour Config".</p>
                                <p><strong>Deleting tours:</strong> To delete a tour as a once off, just close the tour. To delete a recurring tour, you need to change the rules first then manually close any tours that have already been added.</p>'
                        ),
                    ]
                );
            }

            $bookingGridfield = $fields->fieldByName('Sunnysideup-Bookings-Model-Booking');

            // Update form fields under 'Bookings'
            if ($bookingGridfield) {
                $fields->insertAfter(
                    'Sunnysideup-Bookings-Model-Booking',
                    LiteralField::create(
                        'Bookings-Model-Booking-Description',
                        '<p style="margin-top: 15px">Above is a list of all the bookings for upcoming tours.</p>
                            <p><strong>To only see bookings for individual tours:</strong></p>
                            <p>Click on a tour under the "Upcoming Tours" tab and click on the "Bookings" tab. From here you can click on a booking to see their personal details</p>'
                    )
                );
            }
        }

        return $form;
    }
}
