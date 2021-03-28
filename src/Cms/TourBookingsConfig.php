<?php

namespace Sunnysideup\Bookings\Cms;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use Sunnysideup\Bookings\Model\DateInfo;
use Sunnysideup\Bookings\Model\ReferralOption;
use Sunnysideup\Bookings\Model\TimesForTour;
use Sunnysideup\Bookings\Model\TourBookingSettings;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class TourBookingsConfig extends ModelAdmin
{
    public $showImportForm = false;

    public $showSearchForm = [
        DateInfo::class,
    ];

    private static $managed_models = [
        TourBookingSettings::class,
        TimesForTour::class,
        DateInfo::class,
        ReferralOption::class,
    ];

    private static $url_segment = 'tour-bookings-config';

    private static $menu_title = 'Tour Config';

    private static $menu_icon = 'sunnysideup/bookings: client/dist/images/icons/TourBookingsAdmin.png';

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
            if (($gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) !== null) {
                if ($gridField instanceof GridField) {
                    $config = $gridField->getConfig();
                    $config->removeComponentsByType(GridFieldExportButton::class);
                    $config->removeComponentsByType(GridFieldPrintButton::class);
                    $config->removeComponentsByType(GridFieldImportButton::class);
                    $config->removeComponentsByType(GridFieldFilterHeader::class);
                    $config->removeComponentsByType(GridFieldSortableHeader::class);
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
}
