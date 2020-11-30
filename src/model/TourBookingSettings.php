<?php



class TourBookingSettings extends TourBaseClass
{
    private static $group_code = 'tourmanagers';

    private static $manager_email = '';

    private static $tour_location_ip = '';

    private static $monthly_tour_report_email_to = '';


    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Tour Booking Settings';

    public function i18n_singular_name()
    {
        return _t('TourBookingSettings.SINGULAR_NAME', 'Tour Booking Settings');
    }

    private static $plural_name = 'Tour Booking Settings';

    public function i18n_plural_name()
    {
        return _t('TourBookingSettings.PLURAL_NAME', 'Tour Booking Settings');
    }


    #######################
    ### Model Section
    #######################

    private static $db = [
        'MaximumNumberPerGroup' => 'Int',
        'NumberOfDaysToGenerateToursInAdvance' => 'Int',
        'TourFullMessage' => 'Varchar',
        'WaitlistInfoMessage' => 'Varchar(511)',
        'GoogleCalendarVerificationCode' => 'Varchar(255)',
        'ConfirmationPageContent' => 'HTMLText'
    ];

    private static $has_one = [
        'Administrator' => 'Member',
        'BookingConfirmationEmail' => 'EmailReminder_NotificationSchedule',
        'UpdateConfirmationEmail' => 'EmailReminder_NotificationSchedule',
        'CancellationConfirmationEmail' => 'EmailReminder_NotificationSchedule',
        'WaitlistConfirmationEmail' => 'EmailReminder_NotificationSchedule',
        'TourSpacesAvailableEmail' => 'EmailReminder_NotificationSchedule'
    ];


    #######################
    ### Further DB Field Details
    #######################

    private static $defaults = [
        'MaximumNumberPerGroup' => '10',
        'NumberOfDaysToGenerateToursInAdvance' => 60
    ];

    private static $default_sort = [
        'ID' => 'ASC'
    ];

    private static $required_fields = [
        'AdministratorID'
    ];


    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels = [
        'Administrator' => 'Tour Booking Manager',
        'TourFullMessage' => 'Tour Full Message',
        'MaximumNumberPerGroup' => 'Max Pax per Group',
        'NumberOfDaysToGenerateToursInAdvance' => 'Tour Auto Generation'
    ];

    private static $field_labels_right = [
        'Administrator' => 'Make sure that the administrator has the right email address.',
        'TourFullMessage' => 'Message displayed to a user when a tour is fully booked',
        'MaximumNumberPerGroup' => 'Maximum number of people per group (set to zero to ignore)',
        'NumberOfDaysToGenerateToursInAdvance' => 'The number of days in advance that tours will be auto generated. The auto generator runs at 2AM everyday, if this is set to 0 then no tours will be auto generated.'
    ];

    private static $summary_fields = [
        'MaximumNumberPerGroup' => 'Max Pax',
        'Administrator.Title' => 'Administrator'
    ];

    private static $email_fields = [
        'BookingConfirmationEmailID',
        'UpdateConfirmationEmailID',
        'CancellationConfirmationEmailID',
        'WaitlistConfirmationEmailID',
        'TourSpacesAvailableEmailID'
    ];

    private static $message_fields = [
        'TourFullMessage',
        'WaitlistInfoMessage',
        'ConfirmationPageContent'
    ];


    public static function inst()
    {
        $obj = DataObject::get_one('TourBookingSettings');
        if (!$obj) {
            $obj = TourBookingSettings::create();
            $obj->write();
        }

        return $obj;
    }


    #######################
    ### can Section
    #######################

    public function canCreate($member = null)
    {
        return DataObject::get_one($this->class) ? false : true;
    }

    public function canView($member = null)
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    public function canEdit($member = null)
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    public function canDelete($member = null)
    {
        return false;
    }



    #######################
    ### write Section
    #######################

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (class_exists('GoogleCalendarInterface') && $this->owner->isChanged('GoogleCalendarVerificationCode')) {
            $this->createNewGoogleCalendarAccessToken();
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        //...
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        DB::alteration_message('Creating Tour Bookings Manager\'s Group and User', 'created');
        $email = Config::inst()->get('TourBookingSettings', 'manager_email');
        if (! $email) {
            $baseURL = Director::absoluteBaseURL();
            $baseURL = str_replace('https://', '', $baseURL);
            $baseURL = str_replace('http://', '', $baseURL);
            $baseURL = trim($baseURL, '/');
            $email = 'tours@'.$baseURL;
        }
        $group = PermissionProviderFactory::inst()
            ->setEmail($email)
            ->setFirstName('Tour')
            ->setSurname('Manager')
            ->setName('Tour Managers')
            ->setCode(Config::inst()->get('TourBookingSettings', 'group_code'))
            ->setPermissionCode('CMS_ACCESS_TOUR_ADMIN')
            ->setRoleTitle('Tour Manager Privileges')
            ->setPermissionArray(['CMS_ACCESS_TourBookingsAdmin'])
            ->CreateGroupAndMember();
        $member = $group->Members()->last();

        if (TourBookingSettings::get()->count() === 0) {
            DB::alteration_message('Creating Tour Bookings Settings', 'created');
            $obj = TourBookingSettings::create();
            if (!$obj->AdministratorID) {
                if ($member) {
                    $obj->AdministratorID = $member->ID;
                }
            }
            $obj->write();
        }
    }

    public function createNewGoogleCalendarAccessToken()
    {
        if ($this->GoogleCalendarVerificationCode) {
            $calendar = new GoogleCalendarInterface();
            $calendar->config($this->GoogleCalendarVerificationCode);
        }
    }


    #######################
    ### Import / Export Section
    #######################

    public function getExportFields()
    {
        //..
        return parent::getExportFields();
    }



    #######################
    ### CMS Edit Section
    #######################


    public function CMSEditLink()
    {
        $controller = singleton("TourBookingsAdmin");

        return $controller->Link().$this->ClassName."/EditForm/field/".$this->ClassName."/item/".$this->ID."/edit";
    }

    public function CMSAddLink()
    {
        $controller = singleton("TourBookingsAdmin");

        return $controller->Link().$this->ClassName."/EditForm/field/".$this->ClassName."/item/new";
    }


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        //do first??
        $rightFieldDescriptions = $this->Config()->get('field_labels_right');
        foreach ($rightFieldDescriptions as $field => $desc) {
            $formField = $fields->DataFieldByName($field);
            if (! $formField) {
                $formField = $fields->DataFieldByName($field.'ID');
            }
            if ($formField) {
                $formField->setDescription($desc);
            }
        }
        $group = Group::get()
            ->filter(
                [
                    'code' =>[
                        Config::inst()->get('TourBookingSettings', 'group_code'),
                        'ADMIN'
                    ]
                ]
            )->first();
        if ($group) {
            $members = $group->Members();
            $fields->replaceField(
                'AdministratorID',
                DropdownField::create(
                    'AdministratorID',
                    'Administrator',
                    $members->map()->toArray()
                )
            );
        }

        $fields->replaceField(
            'WaitlistInfoMessage',
            TextareaField::create(
                'WaitlistInfoMessage',
                'Waitlist Info Message'
            )->setRightTitle(
                'Message displayed at the top of the "Join Waitlist" form to provide info about the waitlisting process'
            )
        );

        if (class_exists('GoogleCalendarInterface') && !Director::isDev()) {
            $calendar = new GoogleCalendarInterface();
            $calendarVerificationField = $fields->dataFieldByName('GoogleCalendarVerificationCode');
            if (empty($calendar->config())) {
                $calendarVerificationField->setRightTitle('Enter a Verfication Code to intergrate with a Google Calenadar');
            } else {
                $calendarVerificationField->setRightTitle('Enter a new Verfication Code to change the Google Account associated with the tour bookings system');
            }

            $fields->insertAfter(
                LiteralField::create(
                    'GoogleCalendarVerificationURL',
                    '<div id="GoogleCalendarVerificationURL_Holder" class="field text">
                        <label class="left" for="GoogleCalendarVerificationURL"></label>
                        <div class="middleColumn">
                            '.$calendar->getAuthLink().'
                        </div>
                    </div>'
                ),
                'GoogleCalendarVerificationCode'
            );
        } else {
            $fields->removeByName('GoogleCalendarVerificationCode');
        }

        $emailFields = $this->Config()->get('email_fields');
        foreach ($emailFields as $field) {
            $formField = $fields->DataFieldByName($field);
            $fields->addFieldsToTab(
                'Root.Emails',
                $formField
            );
            if ($this->$field) {
                $emailNotifier = EmailReminder_NotificationSchedule::get()->byID($this->$field);
                if ($emailNotifier) {
                    $cmsLink = $emailNotifier->CMSEditLink();
                    if ($cmsLink) {
                        $formField->setRightTitle('<a href="'.$cmsLink.'" target="_blank">Edit</a> the content of this email');
                    }
                }
            }
        }

        $messageFields = $this->Config()->get('message_fields');
        foreach ($messageFields as $field) {
            $formField = $fields->DataFieldByName($field);
            if ($formField instanceof HTMLEditorField) {
                $formField->setRows(5);
            }
            $fields->addFieldsToTab(
                'Root.Messages',
                $formField
            );
        }

        $bookingSingleton = Injector::inst()->get('Booking');

        $this->AddUsefulLinkToFields(
            $fields,
            'Open Tour Booking Page',
            TourBookingPage_Controller::find_link()
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Create Future Tours Now',
            Injector::inst()->get('TourBuilder')->Link(),
            'This task runs regularly, but you can run it now by clicking above link.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Monthly Tour Report',
            Injector::inst()->get('MonthlyTourReport')->Link(),
            'This task runs once a month, but you can get the report sent now by clicking above link.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Add New Booking',
            $bookingSingleton->AddLink()
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Add adhoc tour at regular time',
            '/admin/tour-bookings/DateInfo/EditForm/field/DateInfo/item/new',
            'Add new tour date(s) with all the details and then create the tours using the <a href="/dev/tasks/TourBuilder/">create tours button</a>.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Add adhoc tour at irregular time',
            '/admin/tour-bookings/TimesForTour/EditForm/field/TimesForTour/item/new',
            'Add the new time first and then add the tour dates.
            After that you will have to create the tours using the <a href="/dev/tasks/TourBuilder/">create tours button</a>.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Find out what tour date rule applies on a certain day',
            '/admin/tour-bookings/DateInfo',
            'Click on the magnifying glass and search for a particular day.'
        );

        return $fields;
    }
}

