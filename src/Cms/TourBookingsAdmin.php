<?php

namespace Sunnysideup\Bookings\Cms;

use Colymba\BulkManager\BulkAction\EditHandler;
use Colymba\BulkManager\BulkManager;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldPaginator;
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

        return $form;
    }
}
