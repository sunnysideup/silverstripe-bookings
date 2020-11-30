<?php

namespace Sunnysideup\Bookings\Cms;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldExportButton;
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
        DateInfo::class,
        Tour::class,
        Booking::class,
        Waitlister::class,
    ];

    private static $managed_models = [
        TourBookingSettings::class,
        TimesForTour::class,
        DateInfo::class,
        Tour::class,
        Booking::class,
        Waitlister::class,
        ReferralOption::class,
    ];

    private static $url_segment = 'tour-bookings';

    private static $menu_title = 'Tour Bookings';

    private static $menu_icon = 'sunnysideup:bookings: client/images/icons/TourBookingsAdmin.png';

    public function getList()
    {
        $list = parent::getList();
        if ($this->modelClass === Tour::class) {
            $mysqlDate = date('Y-m-d', strtotime('-2 days'));
            $list = $list->filter(['Date:GreaterThan' => $mysqlDate]);
        }

        return $list;
    }

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        //This check is simply to ensure you are on the managed model you want adjust accordingly
        if ($this->modelClass === TimesForTour::class && $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->removeComponentsByType(GridFieldExportButton::class);
            }
        }

        if ($this->modelClass === DateInfo::class && $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->removeComponentsByType(GridFieldExportButton::class);
                $gridField->getConfig()->removeComponentsByType(GridFieldPrintButton::class);
                $gridField->getConfig()->addComponent(new GridFieldSortableRows('SortOrder'));
            }

            $gridField->setDescription(
                '<h3>
                    The TOUR BUILDER automatically generates tours based on the rules in the table above.<br />
                    Only one rule is applied to each day.  The applicable rule is found by checking the rules (starting from the bottom of the table) above until a match is found.
                    <br />
                    <br />
                    To find out what rule applies for a certain day, click on the magnifying glass and search for a particular day.
                </h3>'
            );
        }

        if (is_subclass_of($this->modelClass, TourBookingSettings::class) || $this->modelClass === TourBookingSettings::class) {
            $record = DataObject::get_one(TourBookingSettings::class);
            if ($record && $record->exists()) {
                return $this->oneItemForm($record);
            }
            if ($gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
                if ($gridField instanceof GridField) {
                    $config = $gridField->getConfig();
                    $config->removeComponentsByType(GridFieldExportButton::class);
                    $config->removeComponentsByType(GridFieldPrintButton::class);
                }
            }
        }

        if ($this->modelClass === ReferralOption::class && $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->removeComponentsByType(GridFieldExportButton::class);
                $gridField->getConfig()->removeComponentsByType(GridFieldPrintButton::class);
                $gridField->getConfig()->addComponent(new GridFieldSortableRows('SortOrder'));
            }
        }

        return $form;
    }

    /**
     * @param DataObject $record
     *
     * @return Form
     */
    public function oneItemForm($record)
    {
        Config::modify()->update(LeftAndMain::class, 'tree_class', $record->ClassName);
        $form = LeftAndMain::getEditForm($record);
        $idField = HiddenField::create('ID')->setValue($record->ID);
        $cssField = LiteralField::create(
            'oneItemFormCSS',
            '
                <style>
                    .cms-content-view .ui-tabs-nav {
                        margin-left: 0!important;
                    }
                    .cms-content-view .Actions {
                        position: fixed;
                        bottom: 16px;
                        right:  16px;
                    }
                </style>
            '
        );
        $form->Fields()->push($idField);
        $form->Fields()->push($cssField);
        return $form;
    }
}
