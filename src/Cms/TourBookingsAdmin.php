<?php

namespace Sunnysideup\Bookings\Cms;

use ModelAdmin;
use GridField;
use GridFieldSortableRows;
use DataObject;
use Config;
use LeftAndMain;
use HiddenField;
use LiteralField;


class TourBookingsAdmin extends ModelAdmin
{
    private static $managed_models = [
        'TourBookingSettings',
        'TimesForTour',
        'DateInfo',
        'Tour',
        'Booking',
        'Waitlister',
        'ReferralOption'
    ];

    private static $url_segment = 'tour-bookings';

    private static $menu_title = 'Tour Bookings';

    private static $menu_icon = 'tour_bookings/images/icons/TourBookingsAdmin.png';

    public $showImportForm = false;

    public $showSearchForm  = [
        'DateInfo',
        'Tour',
        'Booking',
        'Waitlister',
    ];

    public function getList()
    {
        $list = parent::getList();
        if ($this->modelClass=='Tour') {
            $mysqlDate = date('Y-m-d', strtotime('-2 days'));
            $list = $list->filter(['Date:GreaterThan' => $mysqlDate]);
        }

        return $list;
    }
    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        //This check is simply to ensure you are on the managed model you want adjust accordingly
        if ($this->modelClass=='TimesForTour' && $gridField=$form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->removeComponentsByType('GridFieldExportButton');
            }
        }

        if ($this->modelClass=='DateInfo' && $gridField=$form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->removeComponentsByType('GridFieldExportButton');
                $gridField->getConfig()->removeComponentsByType('GridFieldPrintButton');
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

        if (is_subclass_of($this->modelClass, 'TourBookingSettings') || $this->modelClass === 'TourBookingSettings') {
            $record = DataObject::get_one('TourBookingSettings');
            if ($record && $record->exists()) {
                return $this->oneItemForm($record);
            }
            if ($gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
                if ($gridField instanceof GridField) {
                    $config = $gridField->getConfig();
                    $config->removeComponentsByType('GridFieldExportButton');
                    $config->removeComponentsByType('GridFieldPrintButton');
                }
            }
        }

        if ($this->modelClass=='ReferralOption' && $gridField=$form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
            //This is just a precaution to ensure we got a GridField from dataFieldByName() which you should have
            if ($gridField instanceof GridField) {
                $gridField->getConfig()->removeComponentsByType('GridFieldExportButton');
                $gridField->getConfig()->removeComponentsByType('GridFieldPrintButton');
                $gridField->getConfig()->addComponent(new GridFieldSortableRows('SortOrder'));
            }
        }

        return $form;
    }


    /**
     *
     * @param DataObject $record
     *
     * @return Form
     */
    public function oneItemForm($record)
    {
        Config::modify()->update('LeftAndMain', 'tree_class', $record->ClassName);
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

