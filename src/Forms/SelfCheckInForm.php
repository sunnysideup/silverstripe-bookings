<?php

namespace Sunnysideup\Bookings\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\Tour;

class SelfCheckInForm extends Form
{
    public function __construct($controller, $name)
    {
        $fieldList = FieldList::create();

        $todaysTours = [];
        $tours = Tour::get()->filter(
            [
                'Date:GreaterThanOrEqual' => date('Y-m-d'),
                'Date:LessThanOrEqual' => date('Y-m-d'),
            ]
        );
        if ($tours->count() > 0) {
            foreach ($tours as $tour) {
                $todaysTours[$tour->ID] = $tour->TourTimeAndDate();
            }

            $fieldList->push(
                OptionsetField::create('TodaysTours', 'Which tour are you attending today?', $todaysTours)
            );

            $fieldList->push(
                TextField::create('BookingData', 'Enter either your Surname, Email, Phone Number or Booking Code')
            );

            $actions = FieldList::create(
                FormAction::create(
                    'checkinbooking',
                    'I have arrived'
                )
            );
        } else {
            $fieldList->push(
                LiteralField::create('NoTours', 'Sorry, there are no tours available today.')
            );

            $actions = FieldList::create();
        }

        $validator = new RequiredFields(['TodaysTours']);

        parent::__construct($controller, $name, $fieldList, $actions, $validator);

        return $this;
    }

    /**
     * Form action handler for TourBookingCancellationForm.
     *
     * @param array $data The form request data submitted
     * @param Form  $form The {@link Form} this was submitted on
     */
    public function checkinbooking(array $data, Form $form, HTTPRequest $request)
    {
        $this->saveDataToSession();
        $data = Convert::raw2sql($data);

        if (empty($data['TodaysTours'])) {
            $this->sessionError(
                'Please select the tour you are attending from the list above',
                'bad'
            );

            return $this->controller->redirectBack();
        } elseif (empty($data['BookingData'])) {
            $this->sessionError(
                'You need to enter either your surname, email, phone number or booking code into the field below.',
                'bad'
            );
            return $this->controller->redirectBack();
        }
        $bookingFound = false;
        $fieldsToCheck = [
            'Code' => 'booking code',
            'InitiatingEmail' => 'email',
            'PrimaryPhone' => 'phone number',
            'InitiatingSurname' => 'surname',
        ];

        foreach ($fieldsToCheck as $fieldToCheck => $niceFieldName) {
            $booking = Booking::get()->filter(
                [
                    'TourID' => intval($data['TodaysTours']),
                    $fieldToCheck => $data['BookingData'],
                ]
            );
            if ($booking->exists()) {
                if ($booking->count() > 1) {
                    $booking = $booking->exclude(['HasArrived' => true]);
                    if ($booking->count() > 1) {
                        $this->sessionError(
                            'Sorry there is more than one tour group with a booking for that ' . $niceFieldName,
                            'bad'
                        );
                        return $this->controller->redirectBack();
                    }
                }
                $bookingFound = true;
                $booking = $booking->first();
                if ($booking->HasArrived) {
                    $this->sessionError(
                        'This booking has already been checked in   ',
                        'bad'
                    );
                    return $this->controller->redirectBack();
                }
                $booking->HasArrived = true;
                $booking->write();
                $redirect = $this->controller->Link('confirmselfcheckin/' . $booking->Code);
                return $this->controller->redirect($redirect);
            }
        }

        if (! $bookingFound) {
            //TODO: message should be editable from CMS
            $this->sessionError(
                'Oops, this booking doesn\'t seem to exist in the Peanut Butter World ether. Probably best to ask the PB guru who\'s hanging out behind the desk for some help ',
                'bad'
            );
            return $this->controller->redirectBack();
        }
    }

    /**
     * saves the form into session.
     */
    public function saveDataToSession()
    {
        $data = $this->getData();

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: Session:: (case sensitive)
         * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
         * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        Controller::curr()->getRequest()->getSession()->set("FormInfo.{$this->FormName()}.data", $data);
    }
}
