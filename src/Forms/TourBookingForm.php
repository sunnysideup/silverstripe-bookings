<?php

namespace Sunnysideup\Bookings\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\ReferralOption;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\TourBookingSettings;

class TourBookingForm extends Form
{
    protected $currentBooking;

    protected $currentTour;

    private static $show_city_field_for_countries = [
        'NZ',
    ];

    // important note: $existingBooking and $singleTour should not both exist at the same time
    public function __construct($controller, $name, $existingBooking = null, $singleTour = null)
    {
        if ($existingBooking) {
            $this->currentBooking = $existingBooking;
            $bookingSingleton = $this->currentBooking;
        } else {
            $bookingSingleton = Injector::inst()->get(Booking::class);
        }

        if ($singleTour) {
            $this->currentTour = $singleTour;
        }

        $fields = $bookingSingleton->getFrontEndFields();

        $fieldList = FieldList::create();

        $column1 = CompositeField::create()->addExtraClass('always-show left-column');
        $column2 = CompositeField::create()->addExtraClass('right-column');

        if ($this->currentBooking) {
            $LeftColHeader = HeaderField::create('UpdateBookingHeader', 'Update your booking.', 5);
        } else {
            $LeftColHeader = HeaderField::create('LeftColHeader', _t('TourBookingForm.SELECT_DATE_AND_NUMBER_OF_GUESTS', 'Select your date and number of guests.'), 5);
        }

        $column1->push(
            $LeftColHeader
        );

        $column1->push(
            $guestsField = NumericField::create('TotalNumberOfGuests', 'Tell us how many people you\'d like to bring?')
                ->addExtraClass('always-show')
                ->setScale(0)
        );

        if (null === $this->currentTour) {
            $column1->push(
                $dateField = TextField::create('BookingDate', 'Select Your Date')
            );

            if ($existingBooking) {
                $column1->push(
                    HiddenField::create('CurrentBookingDate', 'Current Booking Date', $existingBooking->Date)
                );
            }
        }

        $column2->push(
            HeaderField::create('RightColHeader', 'Your personal details.', 5)
        );

        foreach ($fields as $field) {
            $column2->push($field);
        }

        //only for new bookings
        if ($existingBooking) {
            $fields->removeByName('CityTown');
        } else {
            $fields->dataFieldByName('CountryOfOrigin')->setValue('nz');
            //referral options
            $referralOptions = ReferralOption::get()->filter(['Archived' => false]);
            if (0 !== $referralOptions->count()) {
                $referralOptionsField = CheckboxSetField::create(
                    'ReferralOptions',
                    'How did you hear about our tours?',
                    $referralOptions->sort('SortOrder', 'ASC')->map('ID', 'Title')
                );

                $column2->push(
                    $referralOptionsField
                );

                $hasOther = ReferralOption::get()->filter(['IsOther' => true])->first();
                if (null !== $hasOther) {
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

        $oldData = Controller::curr()->getRequest()->getSession()->get("FormInfo.{$this->FormName()}.data");

        $oldData = $oldData ?: $this->currentBooking;

        if ($oldData && (is_array($oldData) || is_object($oldData))) {
            $this->loadDataFrom($oldData);
        }

        return $this;
    }

    /**
     * Form action handler for TourBookingForm.
     *
     * @param array $data The form request data submitted
     * @param Form  $form The {@link Form} this was submitted on
     */
    public function dobooking(array $data, Form $form, HTTPRequest $request)
    {
        $newBooking = true;
        $this->saveDataToSession();
        $data = Convert::raw2sql($data);

        if (isset($data['TourID']) && $data['TourID']) {
            $selectedTour = Tour::get_by_id($data['TourID']);

            if ($selectedTour && !$selectedTour->getAllowBooking()) {
                $this->sessionError(
                    'Sorry, the tour is no-longer accepting booking at the selected time. Please book a tour at a different time.',
                    'bad'
                );

                return $this->controller->redirectBack();
            }
        }

        if ($this->currentTour) {
            $spacesLeft = $this->currentTour->getNumberOfPlacesAvailable()->value;
            if ((int) $data['TotalNumberOfGuests'] > $spacesLeft) {
                $message = 'Sorry, there are no spaces left.';
                if ($spacesLeft > 1) {
                    $message = 'Sorry there are only ' . $spacesLeft . ' spaces';
                } elseif ($spacesLeft === 1) {
                }
                $this->sessionError(
                    $message . ' left. Please reduce the number of people for your booking or book a tour at a different time.',
                    'bad'
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
                $this->sessionError(
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
        if (! $validationObject->isValid()) {
            foreach ($validationObject->getMessages() as $message) {
                $this->sessionError(
                    $message['message'] . ' ',
                );
            }

            return $this->controller->redirectBack();
        }
        if (isset($data['ReferralOptions'])) {
            foreach ($data['ReferralOptions'] as $referralOptionID) {
                $referralOptionID = (int) $referralOptionID;
                $referralOption = ReferralOption::get()->byID($referralOptionID);
                if (null !== $referralOption) {
                    $this->currentBooking->ReferralOptions()->add($referralOption);
                }
            }
        }
        if (isset($data['ReferralText'])) {
            $this->currentBooking->ReferralText = $data['ReferralText'];
        }
        $this->currentBooking->write();
        //$this->currentBooking->Tour()->write();
        $code = substr((string) $this->currentBooking->Code, 0, 9);
        $settings = TourBookingSettings::inst();

        if ($newBooking) {
            $confirmationEmail = $settings->BookingConfirmationEmail();
            $confirmationEmail->sendOne($this->currentBooking);
        } else {
            $confirmationEmail = $settings->UpdateConfirmationEmail();
            $confirmationEmail->sendOne($this->currentBooking);
        }

        $redirect = $this->currentBooking->ConfirmLink();

        return $this->controller->redirect($redirect);
    }

    /**
     * saves the form into session.
     */
    public function saveDataToSession()
    {
        $data = $this->getData();

        Controller::curr()->getRequest()->getSession()->set("FormInfo.{$this->FormName()}.data", $data);
    }
}
