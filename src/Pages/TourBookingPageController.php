<?php

namespace Sunnysideup\Bookings\Pages;

use PageController;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;
use Sunnysideup\Bookings\Cms\TourBookingsAdmin;
use Sunnysideup\Bookings\Forms\SelfCheckInForm;
use Sunnysideup\Bookings\Forms\TourBookingCancellationForm;
use Sunnysideup\Bookings\Forms\TourBookingForm;
use Sunnysideup\Bookings\Forms\TourWaitlistForm;
use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\DateInfo;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\TourBookingSettings;
use Sunnysideup\Bookings\Model\Waitlister;

class TourBookingPageController extends PageController
{
    protected $isCancellation = false;

    protected $factoryIP = '';

    #######################
    # revivew
    #######################

    protected $listOfToursFromDate = null;

    protected $listOfToursUntilDate = null;

    private static $url_segment = 'tour-bookings';

    private static $allowed_actions = [

        //add
        'BookingForm' => true,
        'signup' => true,
        'availability' => true,
        'confirmsignup' => true,

        //edit
        'BookingCancellationForm' => true,
        'update' => '->canEdit',
        'cancel' => true,

        //waiting list
        'WaitlistForm' => true,
        'waitlist' => true,
        'confirmwaitlist' => true,
        'SingleTourBookingForm' => true,
        'jointour' => true,

        //review / lists
        'calendar' => true,
        'today' => 'CMS_ACCESS_TOUR_ADMIN',
        'tomorrow' => 'CMS_ACCESS_TOUR_ADMIN',
        'nextdays' => 'CMS_ACCESS_TOUR_ADMIN',
        'all' => 'CMS_ACCESS_TOUR_ADMIN',
        'quickview' => 'CMS_ACCESS_TOUR_ADMIN',

        //on the day
        'checkinfortour' => 'CMS_ACCESS_TOUR_ADMIN',
        'confirmonecheckin' => 'CMS_ACCESS_TOUR_ADMIN',

        //on the day
        'SelfCheckInForm' => true,
        'selfcheckin' => true,
        'confirmselfcheckin' => true,
    ];

    #######################
    # add a booking
    #######################

    private $availabilityDateAsTS = null;

    private $bookingCode = '';

    private $totalNumberOfGuests = 0;

    private $currentBooking = null;

    #######################
    # join the waitlist
    #######################

    private $currentWaitlister = null;

    #######################
    # on the day
    #######################

    private $currentTour = null;

    /**
     * called when no other action is called
     * redirects to start sign up process
     */
    public function index($request)
    {
        if ($this->ClassName === TourBookingPage::class) {
            return $this->redirect($this->Link('signup'));
        }
        return ['Content' => DBField::create_field('HTMLText', $this->Content)];
    }

    /**
     * @param string $action
     * @return string
     */
    public static function find_link($action = null)
    {
        $actionToTest = null;
        if ($action) {
            $allowedActions = Config::inst()->get(TourBookingPageController::class, 'allowed_actions');
            $actionToTest = explode('/', $action)[0];
        }
        if ($actionToTest === null || isset($allowedActions[$actionToTest])) {
            $obj = Injector::inst()->get(TourBookingPageController::class);

            return $obj->Link($action);
        }
        user_error('Action ' . $action . ' is not found. Available actions are: ' . implode(', ', array_keys($allowedActions)));
    }

    public function Link($action = null)
    {
        if ($action) {
            $action = '/' . $action . '/';
        }
        return $this->Config()->get('url_segment') . $action;
    }

    public function CalendarLink()
    {
        return $this->Link('calendar');
    }

    public function canEdit($member = null, $context = [])
    {
        return $this->currentBooking && $this->currentBooking->exists() ? true : false;
    }

    public function CurrentUserIsTourManager($member)
    {
        if (Permission::check('CMS_ACCESS_TOUR_ADMIN', 'any', $member)) {
            return true;
        }
        return false;
    }

    public function BookingForm($request = null)
    {
        $this->getBookingFromRequestOrIDParam();
        return TourBookingForm::create($this, 'BookingForm', $this->currentBooking);
    }

    public function signup($request)
    {
        $this->Content = $this->BookingForm();
        if ($this->IsFactory()) {
            return $this->RenderWith(['Page_MainOnly', 'Page']);
        }
        return $this->RenderWith(['Page']);
    }

    public function availability($request)
    {
        $dateAsString = $request->getVar('date');
        $this->totalNumberOfGuests = intval($request->getVar('guests'));
        // hack!
        // $dateAsString = str_replace(' (New Zealand Standard Time)', '', $dateAsString);
        $dateAsString = preg_replace("/\([^)]+\)/", '', $dateAsString);
        $this->availabilityDateAsTS = strtotime($dateAsString);

        $this->bookingCode = Convert::raw2sql($request->getVar('bookingcode'));
        if ($this->bookingCode) {
            $this->currentBooking = Booking::get()->filter(['Code' => $this->bookingCode])->first();
        }

        return $this->RenderWith('Sunnysideup/Bookings/Includes/TourBookingsAvailableForOneDay');
    }

    public function confirmsignup($request)
    {
        if (! $this->currentBooking) {
            return $this->httpError(404, 'Not Found');
        }

        $this->Content = $this->RenderWith('Sunnysideup/Bookings/Includes/BookingConfirmationContent');

        if ($this->IsFactory()) {
            return $this->RenderWith(['Page_MainOnly', 'Page']);
        }
        return $this->RenderWith(['Page']);
    }

    public function DateInformation()
    {
        return DateInfo::best_match_for_date($this->availabilityDateAsTS);
    }

    public function MyDate()
    {
        return DBField::create_field('Date', date('Y-m-d', $this->availabilityDateAsTS));
    }

    public function ListOfToursForOneDay()
    {
        return $this->findTours($this->availabilityDateAsTS, $this->totalNumberOfGuests);
    }

    public function CurrentBooking()
    {
        return $this->currentBooking;
    }

    public function TotalNumberOfGuests()
    {
        return $this->totalNumberOfGuests;
    }

    #######################
    # edit or cancel booking
    #######################

    public function BookingCancellationForm()
    {
        $bookingCode = ! empty($this->currentBooking) ? $this->currentBooking->Code : 0;
        return TourBookingCancellationForm::create($this, 'BookingCancellationForm', $bookingCode);
    }

    public function IsCancelled()
    {
        if (! empty($this->currentBooking)) {
            return $this->currentBooking->Cancelled;
        }
        return false;
    }

    public function update($request)
    {
        if (! $this->currentBooking) {
            return $this->httpError(404, 'Not Found');
        }

        if ($this->IsCancelled()) {
            $this->Title = 'Cancellation Confirmation';
            $this->Content = $this->RenderWith('Sunnysideup/Bookings/Includes/BookingCancellationContent');
        } else {
            $this->Title = 'Update your booking';
            $this->Content = $this->RenderWith('Sunnysideup/Bookings/Includes/UpdateBookingContent');
        }

        if ($this->IsFactory()) {
            return $this->RenderWith(['Page_MainOnly', 'Page']);
        }
        return $this->RenderWith(['Page']);
    }

    public function cancel($request)
    {
        if (! $this->currentBooking) {
            return $this->httpError(404, 'Not Found');
        }

        $this->isCancellation = true;
        $this->Title = 'Cancel your booking';
        if ($this->IsCancelled()) {
            $this->Title = 'Cancellation Confirmation';
        }
        $this->Content = $this->RenderWith('Sunnysideup/Bookings/Includes/BookingCancellationContent');

        if ($this->IsFactory()) {
            return $this->RenderWith(['Page_MainOnly', 'Page']);
        }

        return $this->RenderWith(['Page']);
    }

    public function CurrentWaitlister()
    {
        return $this->currentWaitlister;
    }

    public function WaitlistForm($request = null)
    {
        $this->getTourFromRequestOrIDParam();
        $this->getNumberOfGuestsFromRequestOrIDParam();
        return TourWaitlistForm::create($this, 'WaitlistForm', $this->currentTour, $this->totalNumberOfGuests);
    }

    public function SingleTourBookingForm($request = null)
    {
        $this->getTourFromRequestOrIDParam();
        return TourBookingForm::create($this, 'SingleTourBookingForm', null, $this->currentTour);
    }

    public function waitlist($request)
    {
        $this->getNumberOfGuestsFromRequestOrIDParam();
        $this->Title = 'Join the Waitlist';
        $this->Content = $this->WaitlistForm();

        if ($this->IsFactory()) {
            return $this->RenderWith(['Page_MainOnly', 'Page']);
        }
        return $this->RenderWith(['Page']);
    }

    public function confirmwaitlist($request)
    {
        $code = Convert::raw2sql($this->request->param('ID'));

        $this->currentWaitlister = Waitlister::get()->filter(['Code' => $code])->last();

        if (! $code || ! $this->currentWaitlister) {
            return $this->httpError(404, 'Not Found');
        }

        $this->Title = 'Confirmation';
        $this->Content = $this->RenderWith('Sunnysideup/Bookings/Includes/WaitlistConfirmationContent');

        if ($this->IsFactory()) {
            return $this->RenderWith(['Page_MainOnly', 'Page']);
        }

        return $this->RenderWith(['Page']);
    }

    public function jointour($request)
    {
        $this->getTourFromRequestOrIDParam();
        $spacesLeft = $this->currentTour->getNumberOfPlacesAvailable()->value;
        $this->Title = $this->currentTour->getTitle();

        if ($spacesLeft > 0) {
            $this->Content = $this->SingleTourBookingForm();
        } else {
            $this->Content = $this->RenderWith('Sunnysideup/Bookings/Includes/TourFullMessage');
        }

        if ($this->IsFactory()) {
            return $this->RenderWith(['Page_MainOnly', 'Page']);
        }

        return $this->RenderWith(['Page']);
    }

    public function TourFullMessage()
    {
        $settings = TourBookingSettings::inst();
        return $settings->TourFullMessage;
    }

    public function ConfirmationPageContent()
    {
        $settings = TourBookingSettings::inst();
        return $settings->ConfirmationPageContent;
    }

    public function calendar($request)
    {
        $member = Security::getCurrentUser();
        if (! $member) {
            return Security::permissionFailure($this);
        } elseif (Permission::checkMember($member, 'CMS_ACCESS_TOUR_ADMIN')) {

            $this->Content = $this->RenderWith('Sunnysideup/Bookings/Includes/CalendarView');

            return $this->RenderWith(['Page']);
        }
        echo 'Sorry you don\'t have the required permissions to access this area. <a href="/Security/logout">LOG OUT</a>';
    }

    public function all($request)
    {
        $this->listOfToursFromDate = Date('Y-m-d', strtotime('today'));
        $this->listOfToursUntilDate = Date('Y-m-d', strtotime('+1 years'));
        return json_encode(array_merge($this->ClosedDatesAsArray(), $this->TourDateAsArray()));
    }

    public function today($request)
    {
        $this->listOfToursFromDate = Date('Y-m-d');
        $this->listOfToursUntilDate = Date('Y-m-d');
    }

    public function tomorrow($request)
    {
        $this->listOfToursFromDate = Date('Y-m-d', strtotime('tomorrow'));
        $this->listOfToursUntilDate = Date('Y-m-d', strtotime('tomorrow'));
    }

    public function nextdays($request)
    {
        $numberOfDays = intval($request->param('ID'));
        if (! $numberOfDays) {
            $numberOfDays = 7;
        }
        $this->listOfToursFromDate = Date('Y-m-d', strtotime('today'));
        $this->listOfToursUntilDate = Date('Y-m-d', strtotime('+ ' . $numberOfDays . ' days'));
    }

    public function ListOfTours()
    {
        return Tour::get()->filter(
            [
                'Date:GreaterThanOrEqual' => $this->listOfToursFromDate,
                'Date:LessThanOrEqual' => $this->listOfToursUntilDate,
            ]
        );
    }

    public function TourDateAsArray()
    {
        $tours = $this->ListOfTours();
        $tourData = [];
        foreach ($tours as $tour) {
            $array = [];
            $array['title'] = $tour->FullCalendarTitle();
            $array['abrv-title'] = $tour->AbrvCalendarTitle();
            $array['url'] = $this->Link('checkinfortour') . $tour->ID . '/';
            $array['start'] = $tour->Date . 'T' . $tour->StartTime;
            $array['end'] = $tour->Date . 'T' . $tour->EndTime()->Value;
            $array['backgroundColor'] = '#16a335';
            if ($tour->IsFull()->value) {
                $array['backgroundColor'] = '#e83333';
            }
            $tourData[] = $array;
        }
        return $tourData;
    }

    public function ClosedDatesAsArray()
    {
        $closedData = [];
        for ($i = 1; $i <= 365; $i++) {
            $dateTS = strtotime('today +' . $i . ' day');
            $dateInfo = DateInfo::best_match_for_date($dateTS);
            if ($dateInfo->NoTourTimes) {
                $mysqlDate = date('Y-m-d', $dateTS);
                $title = $dateInfo->PublicContent ? $dateInfo->dbObject('PublicContent')->Summary(10) : 'Closed';
                $array = [];
                $array['title'] = $title;
                $array['abrv-title'] = $title;
                $array['start'] = $mysqlDate . 'T00:00:00';
                $array['end'] = $mysqlDate . 'T23:59:00';
                $array['backgroundColor'] = '#007bff';
                $closedData[] = $array;
            }
        }
        return $closedData;
    }

    public function quickview($request)
    {
        $this->getTourFromRequestOrIDParam();
        if (! $this->currentTour) {
            return $this->httpError(404, 'Not Found');
        }

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: ->RenderWith( (ignore case)
         * NEW: ->RenderWith( (COMPLEX)
         * EXP: Check that the template location is still valid!
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        return $this->RenderWith([TourBookingPageController::class, 'QuickView']);
    }

    public function checkinfortour($request)
    {
        $this->getTourFromRequestOrIDParam();
        if (! $this->currentTour) {
            return $this->httpError(404, 'Not Found');
        }

        $this->Content = $this->RenderWith('Sunnysideup/Bookings/Includes/TourCheckinContent');

        return $this->RenderWith(['Page']);
    }

    public function confirmonecheckin($request)
    {
        $booking = Booking::get()->byID(intval($request->getVar('id')));
        $booking->HasArrived = Convert::raw2sql($request->getVar('arrived'));
        return $booking->write();
    }

    public function SelfCheckInForm($request = null)
    {
        return SelfCheckInForm::create($this, SelfCheckInForm::class);
    }

    public function selfcheckin()
    {
        if ($this->IsFactory()) {
            $this->Content = '
                <h1>Self Check-In</h1>
                <p class="message good">Complete the form below to check in for your tour.</p>';
            $this->Form = $this->SelfCheckInForm();
        } else {
            $this->Content = '
                <h1>Self Check-In</h1>
                <p class="message good">
                    Please make your way to Pics to complete your check in.<br>
                    If you are already there then connect to Pic\'s Really Good Free WiFi
            ';
        }

        //this page will always render without a header/footer - regardless of whether or not it is being accessed from the factory
        return $this->RenderWith(['Page_MainOnly', 'SelfCheckInPage']);
    }

    public function confirmselfcheckin($request)
    {
        if (! $this->currentBooking) {
            return $this->httpError(404, 'Not Found');
        }

        $this->Content = $this->RenderWith('Sunnysideup/Bookings/Includes/SelfCheckInConfirmationContent');

        //this page will also always render without a header/footer - regardless of whether or not it is being accessed from the factory
        return $this->RenderWith(['Page_MainOnly', 'Page']);
    }

    public function CurrentTour()
    {
        return $this->currentTour;
    }

    public function TourLinks($className = '')
    {
        $modelAdmin = Injector::inst()->get(TourBookingsAdmin::class);
        $models = $modelAdmin->getManagedModels();
        $al = ArrayList::create();
        foreach ($models as $key => $model) {
            if ($className && $className === $key) {
                return Injector::inst()->get($key)->CMSListLink();
            }
            $al->push(Injector::inst()->get($key));
        }
        return $al;
    }

    public function TourBookingsAdminLink()
    {
        $member = Security::getCurrentUser();
        if ($member && $this->CurrentUserIsTourManager($member)) {
            return $this->Link('calendar');
        }
        return false;
    }

    public function IsCancellation()
    {
        return $this->isCancellation;
    }

    public function IsFactory()
    {
        $hideHeader = (bool) $this->request->getVar('hideheader');
        //if hideheader get var has explicitly been set to false then pretend this is not the factory, even it if is
        if ($this->factoryIP === $_SERVER['REMOTE_ADDR'] || $hideHeader) {
            return true;
        }
        return false;
    }

    protected function init()
    {
        parent::init();
        $this->factoryIP = Config::inst()->get(TourBookingSettings::class, 'tour_location_ip');
        $countries = json_encode(Config::inst()->get(TourBookingForm::class, 'show_city_field_for_countries'));
        $settings = TourBookingSettings::inst();
        Requirements::customScript(
            '
                if(typeof TourBookingsInPageData === "undefined") {
                    var TourBookingsInPageData = {};
                }
                TourBookingsInPageData.url = "' . $this->Link() . '";
                TourBookingsInPageData.maxPerGroup = "' . $settings->MaximumNumberPerGroup . '";
                TourBookingsInPageData.emailContact = "' . $settings->Administrator()->Email . '";
                TourBookingsInPageData.showCityTownForCountries = ' . $countries . ';
            ',
            'TourBookingsInPageData'
        );
        $this->getBookingFromRequestOrIDParam();
    }

    #######################
    # protected functions
    #######################

    protected function getBookingFromRequestOrIDParam()
    {
        $this->currentBooking = null;
        $code = '';
        if ($code = $this->request->postVar('BookingCode')) {
            $code = Convert::raw2sql($code);
        } else {
            $code = Convert::raw2sql($this->request->param('ID'));
        }
        if ($code) {
            $count = Booking::get()->filter(['Code' => $code])->count();
            if ($count > 1) {
                user_error('There are duplicate bookings with the same Boooking Code');
            }
            $this->currentBooking = Booking::get()->filter(['Code' => $code])->last();
        }

        return $this->currentBooking;
    }

    protected function getTourFromRequestOrIDParam()
    {
        $this->currentTour = null;
        if ($id = $this->request->postVar('TourID')) {
            $id = intval($id);
        } else {
            $id = intval($this->request->param('ID'));
        }
        $this->currentTour = Tour::get()->byID($id);
        return $this->currentTour;
    }

    protected function getNumberOfGuestsFromRequestOrIDParam()
    {
        $this->totalNumberOfGuests = null;
        if ($guests = $this->request->param('OtherID')) {
            $this->totalNumberOfGuests = intval($guests);
        } elseif ($guests = $this->request->postVar('TotalNumberOfGuests')) {
            $this->totalNumberOfGuests = intval($guests);
        }
        return $this->totalNumberOfGuests;
    }

    /**
     * returns an ArrayData with
     *   PreviousDay: list of tours
     *   RequestedDay: list of tours
     *   NextDay: list of tours
     *
     * @param  int $dateTS
     * @param  int $numberOfPlacesRequested
     *
     * @return ArrayData
     */
    protected function findTours($dateTS, $numberOfPlacesRequested = 0)
    {
        $finalArrayList = ArrayList::create();
        $dateMysql = date('Y-m-d', $dateTS);
        $tours = Tour::get()->filter(['Date' => $dateMysql])->sort(['StartTime' => 'ASC', 'ID' => 'ASC']);
        $myTourID = 0;
        if ($this->currentBooking && $this->currentBooking->exists()) {
            $myTourID = $this->currentBooking->TourID;
        }
        foreach ($tours as $tour) {
            $calculatedNumberOfPlacesRequested = $numberOfPlacesRequested;
            if ($tour->ID === $myTourID) {
                $calculatedNumberOfPlacesRequested = $numberOfPlacesRequested - $this->currentBooking->TotalNumberOfGuests;
            }
            if ($tour->getNumberOfPlacesAvailable()->Value === 0 && $calculatedNumberOfPlacesRequested > 0) {
                $availability = 'Full';
                $isAvailable = false;
            } elseif ($tour->getNumberOfPlacesAvailable()->Value >= $calculatedNumberOfPlacesRequested) {
                $availability = 'Available';
                $isAvailable = true;
            } else {
                $availability = 'Unavailable';
                $isAvailable = false;
            }
            if (! isset($finalArray[$tour->ID])) {
                $finalArray[$tour->ID] = ArrayList::create();
            }
            $tour->Availability = $availability;
            $tour->IsAvailable = $isAvailable;
            $finalArrayList->push($tour);
        }
        return $finalArrayList;
    }
}
