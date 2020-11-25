<?php

namespace Sunnysideup\Bookings\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\TourBookingSettings;
use Sunnysideup\Bookings\Model\Waitlister;
use SunnySideUp\EmailReminder\Tasks\EmailReminderDailyMailOut;

class TourWaitlistForm extends Form
{
    protected $currentTour;

    public function __construct($controller, $name, $selectedTour = null, $numberOfGuests = 1)
    {
        $fieldList = FieldList::create();
        $settings = TourBookingSettings::inst();
        if (!$selectedTour) {
            $fieldList->push(
                LiteralField::create(
                    'TourNotFound',
                    '<p class="message bad">The tour could not be found.</p>'
                )
            );
            parent::__construct($controller, $name, $fieldList, FieldList::create(), null);
            return;
        }

        $this->currentTour = $selectedTour;
        $waitlisterSingleton = Injector::inst()->get(Waitlister::class);

        $fields = $waitlisterSingleton->getFrontEndFields();



        if ($settings->WaitlistInfoMessage) {
            $fieldList->push(
                LiteralField::create(
                    'WaitlistNotice',
                    '<p class="message info">' . $settings->WaitlistInfoMessage . '</p>'
                )
            );
        }

        $leftColumn = CompositeField::create(
            ReadonlyField::create('TourTitle', 'Requested Tour', $this->currentTour->Title)
        )->addExtraClass('left-column');

        foreach ($fields as $field) {
            if ('TotalNumberOfGuests' === $field->Name) {
                $field->setValue($numberOfGuests);
                $field->setReadOnly(true);
            }
            $leftColumn->push($field);
        }

        $fieldList->push(
            $leftColumn
        );

        $tourDateAsTimeStamp = strtotime($this->currentTour->Date);
        $oneWeekEarlier = date('Y-m-d', strtotime('-7 day', $tourDateAsTimeStamp));
        if (strtotime($oneWeekEarlier) < strtotime('+1 day', strtotime('now'))) {
            $oneWeekEarlier = date('Y-m-d', strtotime('+1 day', strtotime('now')));
        }
        $oneWeekLater = date('Y-m-d', strtotime('+7 day', $tourDateAsTimeStamp));
        $tours = [];
        $allToursForFortnight = Tour::get()
            ->filter(
                [
                    'Date:GreaterThanOrEqual' => $oneWeekEarlier,
                    'Date:LessThanOrEqual' => $oneWeekLater,
                ]
            )
            ->exclude(['ID' => $this->currentTour->ID]);

        foreach ($allToursForFortnight as $tour) {
            if ($tour->getNumberOfPlacesAvailable()->Value < $numberOfGuests) {
                $tours[$tour->ID] = $tour->Title;
            }
        }

        //$tours = $allToursForFortnight;

        $fieldList->push(
            CheckboxSetField::create('ExtraTours', 'Select any other tours that you would like to join the waitlist for (optional)', $tours)
        );

        $fieldList->push(
            HiddenField::create('TourID', 'TourID', $this->currentTour->ID)
        );

        $actions = FieldList::create(
            FormAction::create(
                'dojoinwaitlist',
                'Join Waitlist'
            )
        );

        $validator = $waitlisterSingleton->getFrontEndValidator();

        parent::__construct($controller, $name, $fieldList, $actions, $validator);
        $oldData = Controller::curr()->getRequest()->getSession()->get("FormInfo.{$this->FormName()}.data");
        $oldData = $oldData ?: [];

        if ($oldData && (is_array($oldData) || is_object($oldData))) {
            $this->loadDataFrom($oldData);
        }
    }

    /**
     * Form action handler for TourBookingForm.
     *
     * @param array $data The form request data submitted
     * @param Form  $form The {@link Form} this was submitted on
     */
    public function dojoinwaitlist(array $data, Form $form, HTTPRequest $request)
    {
        $this->saveDataToSession();
        $data = Convert::raw2sql($data);

        $waitlister = Waitlister::get()->filter(
            [
                'InitiatingEmail' => $data['InitiatingEmail'],
                'TourID' => $data['TourID'],
            ]
        )->first();

        if ($waitlister && $waitlister->exists()) {
            //do nothing
        } else {
            $waitlister = Waitlister::create();
        }

        $form->saveInto($waitlister);
        //TotalNumberOfGuests needs to manually set as the field is read only
        $waitlister->TotalNumberOfGuests = (int) $data['TotalNumberOfGuests'];

        $validationObject = $waitlister->validate();

        if (!$validationObject->isValid()) {
            return $this->controller->redirectBack();
        }
        $waitlister->write();
        $settings = TourBookingSettings::inst();
        $confirmationEmail = $settings->WaitlistConfirmationEmail();
        $mailOut = Injector::inst()->get(EmailReminderDailyMailOut::class);
        $mailOut->runOne($confirmationEmail, $waitlister);

        $redirect = $this->controller->Link('confirmwaitlist/' . $waitlister->Code);
        //extra tours have been selected
        if (isset($data['ExtraTours'])) {
            foreach ($data['ExtraTours'] as $tourID) {
                $waitlister = Waitlister::get()->filter(
                    [
                        'InitiatingEmail' => $data['InitiatingEmail'],
                        'TourID' => $tourID,
                    ]
                )->first();

                if ($waitlister && $waitlister->exists()) {
                    //do nothing
                } else {
                    $waitlister = Waitlister::create();
                }
                $form->saveInto($waitlister);
                $waitlister->TotalNumberOfGuests = (int) $data['TotalNumberOfGuests'];
                $waitlister->TourID = $tourID;
                $waitlister->write();
            }
        }

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
