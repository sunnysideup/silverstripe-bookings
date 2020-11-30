<?php

namespace Sunnysideup\Bookings\Forms;

use Form;
use Injector;
use FieldList;
use CompositeField;
use HeaderField;
use NumericField;
use TextField;
use ReferralOption;
use CheckboxSetField;
use HiddenField;
use EmailField;
use FormAction;
use Controller;
use SS_HTTPRequest;
use Convert;
use TourBookingSettings;
use Booking;




class TourBookingForm extends Form
{
    protected $currentBooking = null;

    protected $currentTour = null;

    private static $show_city_field_for_countries = [
        'NZ'
    ];

    /* important note: $existingBooking and $singleTour should not both exist at the same time */
    public function __construct($controller, $name, $existingBooking = null, $singleTour = null)
    {
        if ($existingBooking) {
            $this->currentBooking = $existingBooking;
            $bookingSingleton = $this->currentBooking;
        } else {
            $bookingSingleton = Injector::inst()->get('Booking');
        }

        if ($singleTour) {
            $this->currentTour = $singleTour;
        }

        $fields = $bookingSingleton->getFrontEndFields();

        $fieldList = FieldList::create();

        $column1 = CompositeField::create()->addExtraClass('always-show left-column');
        $column2 = CompositeField::create()->addExtraClass('right-column');

        if ($this->currentBooking) {
            $LeftColHeader = HeaderField::create('UpdateBookingHeader', 'Update your booking.', 1);
        } else {
            $LeftColHeader = HeaderField::create('LeftColHeader', 'Pic\'s Peanut Butter Factory Tour');
        }

        $column1->push(
            $LeftColHeader
        );

        $column1->push(

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: NumericField::create (case sensitive)
  * NEW: NumericField::create (COMPLEX)
  * EXP: check the number of decimals required and add as ->setScale(2)
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            NumericField::create('TotalNumberOfGuests', 'Number of people in this booking.')->addExtraClass('always-show')
        );

        if (is_null($this->currentTour)) {
            $column1->push(
                TextField::create('BookingDate', 'Select Your Date')
            );
        }

        $column2->push(
            HeaderField::create('RightColHeader', 'Your personal details.')
        );

        foreach ($fields as $field) {
            $column2->push($field);
        }

        //only for new bookings
        if ($existingBooking) {
            $fields->removeByName('CityTown');
        } else {
            //referral options
            $referralOptions = ReferralOption::get()->filter(['Archived' => false]);
            if ($referralOptions->count()) {
                $referralOptionsField = CheckboxSetField::create(
                    'ReferralOptions',
                    'How did you hear about our tours?',
                    $referralOptions->sort('SortOrder', 'ASC')->map('ID', 'Title')
                );


                $column2->push(
                    $referralOptionsField
                );

                $hasOther = ReferralOption::get()->filter(['IsOther' => true])->first();
                if ($hasOther) {
                    $referralOptionsField->setAttribute('data-other', $hasOther->ID);

                    $column2->push(
                        TextField::create(
                            'ReferralText',
                            'Let us know more'
                        )
                    );
                }
            }
        }

        if ($this->currentBooking) {
            $fieldList->push(
                HiddenField::create('BookingCode', '', $this->currentBooking->Code)
            );
            $column2->removeByName('InitiatingEmail');
            $column2->push(
                EmailField::create(
                    'ConfirmingEmail',
                    'Confirm your Email'
                )
            );
        }

        if ($this->currentTour) {
            $column2->replaceField(
                'TourID',
                HiddenField::create(
                    'TourID',
                    'TourID',
                    $this->currentTour->ID
                )
            );
        }

        $fieldList->push($column1);
        $fieldList->push($column2);

        $actions = FieldList::create(
            FormAction::create(
                'dobooking',
                'Finalise Booking'
            )
        );

        $validator = $bookingSingleton->getFrontEndValidator();

        parent::__construct($controller, $name, $fieldList, $actions, $validator);


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: Session:: (case sensitive)
  * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
  * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly. 
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $oldData = Controller::curr()->getRequest()->getSession()->get("FormInfo.{$this->FormName()}.data");
        $oldData = $oldData ? $oldData : $this->currentBooking;

        if ($oldData && (is_array($oldData) || is_object($oldData))) {
            $this->loadDataFrom($oldData);
        }

        return $this;
    }

    /**
     * Form action handler for TourBookingForm.
     *
     *
     * @param array $data The form request data submitted
     * @param Form  $form The {@link Form} this was submitted on
     */
    public function dobooking(array $data, Form $form, SS_HTTPRequest $request)
    {
        $newBooking = true;
        $this->saveDataToSession();
        $data = Convert::raw2sql($data);
        if ($this->currentTour) {
            $spacesLeft = $this->currentTour->getNumberOfPlacesAvailable()->value;
            if (intval($data['TotalNumberOfGuests']) > $spacesLeft) {
                $message = 'Sorry there is only ' . $spacesLeft . 'space';
                if ($spacesLeft > 1) {
                    $message = 'Sorry there are only ' . $spacesLeft . ' spaces';
                }
                $this->addErrorMessage(
                    'BookingForm',
                    $message . ' left. Please reduce the number of people for your booking or book a tour at a different time.',
                    'bad',
                    false
                );
                return $this->controller->redirectBack();
            }
        }
        if ($this->currentBooking) {
            $newBooking = false;
            if ($data['ConfirmingEmail'] === $this->currentBooking->InitiatingEmail) {
                //do nothing
            } else {
                $settings = TourBookingSettings::inst();
                $email = $settings->Administrator()->Email;
                $this->addErrorMessage(
                    'BookingForm',
                    'You need to enter the same email address used to create the original booking, please try again or contact the tour manager for assistance: ' . $email,
                    'bad'
                );
                return $this->controller->redirectBack();
            }
        } else {
            $this->currentBooking = Booking::create();
        }
        $form->saveInto($this->currentBooking);
        $validationObject = $this->currentBooking->validate();
        if (!$validationObject->valid()) {
            foreach ($validationObject->messageList() as $message) {
                $this->addErrorMessage(
                    'BookingForm',
                    $message . ' ',
                    'bad'
                );
            }

            return $this->controller->redirectBack();
        } else {

            if (isset($data['ReferralOptions'])) {
                foreach ($data['ReferralOptions'] as $referralOptionID) {
                    $referralOptionID = intval($referralOptionID);
                    $referralOption = ReferralOption::get()->byID($referralOptionID);
                    if ($referralOption) {
                        $this->currentBooking->ReferralOptions()->add($referralOption);
                    }
                }
            }
            if (isset($data['ReferralText'])) {
                $this->currentBooking->ReferralText = $data['ReferralText'];
            }
            $this->currentBooking->write();
            //$this->currentBooking->Tour()->write();
            $code = substr($this->currentBooking->Code, 0, 9);
            $settings = TourBookingSettings::inst();
            $mailOut = Injector::inst()->get('EmailReminder_DailyMailOut');

            if ($newBooking) {
                $confirmationEmail = $settings->BookingConfirmationEmail();
                $mailOut->runOne($confirmationEmail, $this->currentBooking);
            } else {
                $confirmationEmail = $settings->UpdateConfirmationEmail();
                $mailOut->runOne($confirmationEmail, $this->currentBooking, false, true);
            }


            $redirect = $this->controller->Link('confirmsignup/' . $code);

            return $this->controller->redirect($redirect);
        }
    }

    /**
     * saves the form into session.
     *
     * @param array $data - data from form.
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

