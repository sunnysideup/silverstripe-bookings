<?php

namespace Sunnysideup\Bookings\Cms;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Core\Injector\Injector;
use Sunnysideup\Bookings\Model\DateInfo;
use Sunnysideup\Bookings\Model\ReferralOption;
use Sunnysideup\Bookings\Model\TimesForTour;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\TourBookingSettings;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use Colymba\BulkManager\BulkAction\EditHandler;
use Colymba\BulkManager\BulkManager;
use SilverStripe\Forms\GridField\GridFieldPaginator;

use SilverStripe\Forms\FieldList;
use Sunnysideup\Bookings\Forms\Actions\CloseAction;
use Sunnysideup\Bookings\Forms\Actions\OpenAction;
use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\Waitlister;
use Sunnysideup\Bookings\Tasks\TourBuilder;
use Sunnysideup\Bookings\Tasks\MonthlyTourReport;
use Sunnysideup\Bookings\Pages\TourBookingPage;


class TourBookingsConfig extends ModelAdmin
{
    public $showImportForm = false;

    public $showSearchForm = [
        DateInfo::class,
    ];

    private static $managed_models = [
        TourBookingSettings::class => [
            'dataClass' => TourBookingSettings::class,
            'title' => 'Settings',
        ],
        DateInfo::class => [
            'dataClass' => DateInfo::class,
            'title' => 'Tour Generator - Rules',
        ],
        TimesForTour::class => [
            'dataClass' => TimesForTour::class,
            'title' => 'Tour Times',
        ],
        ReferralOption::class => [
            'dataClass' => ReferralOption::class,
            'title' => 'Referral options',
        ],
        Tour::class => [
            'dataClass' => Tour::class,
            'title' => 'Tour Archive',
        ],
        Booking::class => [
            'dataClass' => Tour::class,
            'title' => 'Booking Archive',
        ],
        Waitlister::class => [
            'dataClass' => Waitlister::class,
            'title' => 'Waitlists Archive',
        ]
    ];

    private static $url_segment = 'tour-bookings-config';

    private static $menu_title = 'Tour Config';

    private static $menu_icon = 'sunnysideup/bookings: client/dist/images/icons/TourBookingsAdmin.png';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        //This check is simply to ensure you are on the managed model you want adjust accordingly
        if (TimesForTour::class === $this->modelClass && $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->removeComponentsByType(GridFieldExportButton::class);
            }
        }

        if (DateInfo::class === $this->modelClass && $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->removeComponentsByType(GridFieldExportButton::class);
                $gridField->getConfig()->removeComponentsByType(GridFieldPrintButton::class);
                $gridField->getConfig()->addComponent(new GridFieldSortableRows('SortOrder'));
            }
            $form->Fields()->insertBefore(
                $this->sanitiseClassName($this->modelClass),
                LiteralField::create(
                    'Explanation',
                    '
                        <p>
                        - The TOUR BUILDER automatically generates tours based on the rules in the table below.<br />
                        - Only one rule is applied to each day.  The applicable rule is found by checking the rules (starting from the bottom of the table) below until a match is found.<br />
                        - This means the most standard rules - i.e. business as usual shows at the top, and specific rules, such as "closed for Xmas", are at the bottom.
                        - To find out what rule applies for a certain day, click on the magnifying glass and search for a particular day.
                        </p>
                    '
                )
            );
        }

        if (is_subclass_of($this->modelClass, TourBookingSettings::class) || TourBookingSettings::class === $this->modelClass) {
            $fields = $form->Fields();
            $gridField = $fields->dataFieldByName($this->sanitiseClassName($this->modelClass));
            if ($gridField && $gridField instanceof GridField) {
                $config = $gridField->getConfig();
                $config->removeComponentsByType(GridFieldExportButton::class);
                $config->removeComponentsByType(GridFieldPrintButton::class);
                $config->removeComponentsByType(GridFieldImportButton::class);
                $config->removeComponentsByType(GridFieldFilterHeader::class);
                $config->removeComponentsByType(GridFieldSortableHeader::class);
            }
            $this->addConfigExplanations($fields);

            return $form;
        }

        if (ReferralOption::class === $this->modelClass && $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->removeComponentsByType(GridFieldExportButton::class);
                $gridField->getConfig()->removeComponentsByType(GridFieldPrintButton::class);
                $gridField->getConfig()->addComponent(new GridFieldSortableRows('SortOrder'));
            }
        }

        return $form;
    }


    protected function addConfigExplanations($fields)
    {
        $bookingSingleton = Injector::inst()->get(Booking::class);
        $timesForTourSingleton = Injector::inst()->get(TimesForTour::class);
        $dateInfoSingleton = Injector::inst()->get(DateInfo::class);
        $tourSingleton = Injector::inst()->get(Tour::class);
        $createToursLink = Injector::inst()->get(TourBuilder::class)->Link();
        $page = TourBookingPage::get()->first();
        if($page) {
            $this->AddUsefulLinkToFields(
                $fields,
                'Open Tour Booking Page',
                $page->Link()
            );
        }

        $this->AddUsefulLinkToFields(
            $fields,
            'Create Future Tours Now',
            $createToursLink,
            'This task runs regularly, but you can run it now by clicking above link.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Monthly Tour Report',
            Injector::inst()->get(MonthlyTourReport::class)->Link(),
            'This task runs once a month, but you can get the report sent now by clicking above link.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Add New Booking',
            $page->Link(),
            'The best way to add a booking is to use the front-end of the website.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Add new tour at an existing time slot, using your rules',
            $dateInfoSingleton->CMSAddLink(),
            'Add new tour date(s) with all the details and then create the tours using the <a href="' . $createToursLink . '">create tours button</a>.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Add new tour at an a new time slot, using your rules',
            $timesForTourSingleton->CMSAddLink(),
            'Add the new time first and then add the tour dates.
            After that you will have to create the tours using the <a href="' . Injector::inst()->get(TourBuilder::class)->Link() . '">create tours button</a>.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Find out what tour date rule applies on a certain day',
            $dateInfoSingleton->CMSListLink(),
            'Click on the magnifying glass and search for a particular day.'
        );

    }

    protected function AddUsefulLinkToFields(FieldList $fields, string $title, string $link, ?string $explanation = '')
    {
        $name = preg_replace('#[^A-Za-z0-9 ]#', '', $title);
        $fields->push(
            LiteralField::create(
                $name . '_UseFulLink',
                '<h2>› <a href="' . $link . '">' . $title . '</a></h2><p>' . $explanation . '</p>'
            ),
        );
    }
}
