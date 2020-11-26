<?php



class TourWaitlistForm extends Form
{
    protected $currentTour = null;


    public function __construct($controller, $name, $selectedTour = null, $numberOfGuests = 1)
    {
        if ($selectedTour) {
            $this->currentTour = $selectedTour;
        }

        $waitlisterSingleton = Injector::inst()->get('Waitlister');

        $fields = $waitlisterSingleton->getFrontEndFields();

        $fieldList = FieldList::create();

        $settings = TourBookingSettings::inst();

        if ($settings->WaitlistInfoMessage) {
            $fieldList->push(
                LiteralField::create(
                    'WaitlistNotice',
                    '
                    <p class="message info">
                        '.$settings->WaitlistInfoMessage.'
                    </p>
                    ',
                    $this->currentTour->ID
                )
            );
        }

        $leftColumn = CompositeField::create(
                        ReadonlyField::create('TourTitle', 'Requested Tour', $this->currentTour->Title)
                    )->addExtraClass('left-column');



        foreach ($fields as $field) {
            if ($field->Name == 'TotalNumberOfGuests') {
                $field->setValue($numberOfGuests);
                $field->setReadOnly(true);
            }
            $leftColumn->push($field);
        }

        $fieldList->push(
            $leftColumn
        );

        $tourDateAsTimeStamp = strtotime($this->currentTour->Date);
        $oneWeekEarlier = date('Y-m-d', strtotime("-7 day", $tourDateAsTimeStamp));
        if(strtotime($oneWeekEarlier) <  strtotime("+1 day", strtotime('now'))){
            $oneWeekEarlier = date('Y-m-d', strtotime("+1 day", strtotime('now')));
        }
        $oneWeekLater = date('Y-m-d', strtotime("+7 day", $tourDateAsTimeStamp));
        $tours = [];
        $allToursForFortnight = Tour::get()
                                    ->filter(
                                        [
                                            'Date:GreaterThanOrEqual' => $oneWeekEarlier,
                                            'Date:LessThanOrEqual' => $oneWeekLater
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

        $oldData = Session::get("FormInfo.{$this->FormName()}.data");
        $oldData = $oldData ? $oldData : [];

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
    public function dojoinwaitlist(array $data, Form $form, SS_HTTPRequest $request)
    {

        $this->saveDataToSession();
        $data = Convert::raw2sql($data);

        $waitlister = Waitlister::get()->filter(
                            [
                                'InitiatingEmail' => $data['InitiatingEmail'],
                                'TourID' => $data['TourID']
                            ]
                        )->first();

        if ($waitlister && $waitlister->exists()) {
            //do nothing
        } else {
            $waitlister = Waitlister::create();
        }

        $form->saveInto($waitlister);
        //TotalNumberOfGuests needs to manually set as the field is read only
        $waitlister->TotalNumberOfGuests = intval($data['TotalNumberOfGuests']);

        $validationObject = $waitlister->validate();
        if (! $validationObject->valid()) {
            return $this->controller->redirectBack();
        } else {
            $waitlister->write();
            $settings = TourBookingSettings::inst();
            $confirmationEmail = $settings->WaitlistConfirmationEmail();
            $mailOut = Injector::inst()->get('EmailReminder_DailyMailOut');
            $mailOut->runOne($confirmationEmail, $waitlister);
            $redirect = $this->controller->Link('confirmwaitlist/'.$waitlister->Code);
            //extra tours have been selected
            if (isset($data['ExtraTours'])) {
                foreach ($data['ExtraTours'] as $tourID) {
                    $waitlister = Waitlister::get()->filter(
                                        [
                                            'InitiatingEmail' => $data['InitiatingEmail'],
                                            'TourID' => $tourID
                                        ]
                                    )->first();

                    if ($waitlister && $waitlister->exists()) {
                        //do nothing
                    } else {
                        $waitlister = Waitlister::create();
                    }
                    $form->saveInto($waitlister);
                    $waitlister->TotalNumberOfGuests = intval($data['TotalNumberOfGuests']);
                    $waitlister->TourID = $tourID;
                    $waitlister->write();
                }
            }
            return $this->controller->redirect($redirect);
        }

        return $this->controller->redirectBack();
    }

    /**
     * saves the form into session.
     *
     * @param array $data - data from form.
     */
    public function saveDataToSession()
    {
        $data = $this->getData();
        Session::set("FormInfo.{$this->FormName()}.data", $data);
    }
}
