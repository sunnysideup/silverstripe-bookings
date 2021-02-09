<?php

namespace Sunnysideup\Bookings\Forms;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use Sunnysideup\Bookings\Model\Booking;
use Sunnysideup\Bookings\Model\TourBookingSettings;
use SunnySideUp\EmailReminder\Tasks\EmailReminderDailyMailOut;

class TourBookingCancellationForm extends Form
{
    public function __construct($controller, $name, $bookingCode)
    {
        $fieldList = FieldList::create();

        $fieldList->push(
            CheckboxField::create('ConfirmCancellation', 'Confirm Cancellation')
        );

        $fieldList->push(
            EmailField::create('ConfirmingEmail', 'Confirm your email')
        );

        $fieldList->push(
            HiddenField::create('BookingCode', 'BookingCode', $bookingCode)
        );

        $actions = FieldList::create(
            FormAction::create(
                'cancelbooking',
                'Cancel Booking'
            )
        );

        $validator = new RequiredFields(['BookingCode', 'ConfirmCancellation', 'ConfirmingEmail']);

        parent::__construct($controller, $name, $fieldList, $actions, $validator);

        return $this;
    }

    /**
     * Form action handler for TourBookingCancellationForm.
     *
     * @param array $data The form request data submitted
     * @param Form  $form The {@link Form} this was submitted on
     */
    public function cancelbooking(array $data, Form $form, HTTPRequest $request)
    {
        $data = Convert::raw2sql($data);
        $booking = Booking::get()->filter(['Code' => $data['BookingCode']])->first();
        if ($booking) {
            if ($data['ConfirmingEmail'] === $booking->InitiatingEmail) {
                //do nothing
            } else {
                $settings = TourBookingSettings::inst();
                $email = $settings->Administrator()->Email;
                $this->sessionError(
                    'ConfirmingEmail',
                    'You need to enter the same email address used to create the original booking, please try again or contact the tour manager for assistance: ' . $email,
                    'bad'
                );
                return $this->controller->redirectBack();
            }
            $booking->Cancelled = true;
            $booking->write();

            $settings = TourBookingSettings::inst();
            $mailOut = Injector::inst()->get(EmailReminderDailyMailOut::class);

            $confirmationEmail = $settings->CancellationConfirmationEmail();
            $mailOut->runOne($confirmationEmail, $booking);

            return $this->controller->redirectBack();
        }
    }
}
