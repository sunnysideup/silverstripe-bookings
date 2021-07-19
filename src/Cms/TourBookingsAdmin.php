<?php

namespace Sunnysideup\Bookings\Cms;

use Colymba\BulkManager\BulkAction\EditHandler;
use Colymba\BulkManager\BulkManager;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldPaginator;

use SilverStripe\Forms\FieldList;
use Sunnysideup\Bookings\Forms\Actions\CloseAction;
use Sunnysideup\Bookings\Forms\Actions\OpenAction;
use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\Waitlister;
use Sunnysideup\Bookings\Model\TimesForTour;
use Sunnysideup\Bookings\Model\TourBookingSettings;

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

    private static $url_segment = 'tour-bookings-admin';

    private static $menu_title = 'Tour Bookings';

    private static $menu_icon = 'sunnysideup/bookings: client/dist/images/icons/TourBookingsAdmin.png';

    public function getList()
    {
        $list = parent::getList();
        if (Tour::class === $this->modelClass) {
            $mysqlDate = date('Y-m-d', strtotime('-2 days'));
            $list = $list->filter(['Date:GreaterThan' => $mysqlDate]);
        }

        return $list;
    }

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        //This check is simply to ensure you are on the managed model you want adjust accordingly
        if (Tour::class === $this->modelClass && $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $bmConfig = (new BulkManager([], false))
                    ->addBulkAction(EditHandler::class)
                    ->addBulkAction(CloseAction::class)
                    ->addBulkAction(OpenAction::class)
                ;
                $gridField->getConfig()->addComponent($bmConfig);
                $paginator = $gridField->getConfig()->getComponentByType(GridFieldPaginator::class);
                if ($paginator) {
                    $paginator->setItemsPerPage(10);
                }
            }
        }
        if (TourBookingSettings::class === $this->modelClass) {

                    $bookingSingleton = Injector::inst()->get(Booking::class);
                    $timesForTour = Injector::inst()->get(TimesForTour::class);
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
                        $bookingSingleton->AddLink()
                    );

                    $this->AddUsefulLinkToFields(
                        $fields,
                        'Add adhoc tour at regular time',
                        $timesForTour->AddLink(),
                        'Add new tour date(s) with all the details and then create the tours using the <a href="' . $createToursLink . '">create tours button</a>.'
                    );

                    $this->AddUsefulLinkToFields(
                        $fields,
                        'Add adhoc tour at irregular time',
                        '/admin/tour-bookings-config/Sunnysideup-Bookings-Model-TimesForTour/EditForm/field/Sunnysideup-Bookings-Model-TimesForTour/item/new',
                        'Add the new time first and then add the tour dates.
                        After that you will have to create the tours using the <a href="' . Injector::inst()->get(TourBuilder::class)->Link() . '">create tours button</a>.'
                    );

                    $this->AddUsefulLinkToFields(
                        $fields,
                        'Find out what tour date rule applies on a certain day',
                        '/admin/tour-bookings-admin/Sunnysideup-Bookings-Model-Tour/',
                        'Click on the magnifying glass and search for a particular day.'
                    );

        }

        return $form;
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
