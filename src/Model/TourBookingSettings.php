<?php

namespace Sunnysideup\Bookings\Model;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use Sunnysideup\Bookings\Cms\TourBookingsAdmin;
use Sunnysideup\Bookings\Pages\TourBookingPageController;
use Sunnysideup\Bookings\Tasks\MonthlyTourReport;
use Sunnysideup\Bookings\Tasks\TourBuilder;
use SunnySideUp\EmailReminder\Model\EmailReminderNotificationSchedule;
use Sunnysideup\GoogleCalendarInterface\GoogleCalendarInterface;
use Sunnysideup\PermissionProvider\Api\PermissionProviderFactory;
use Sunnysideup\SanitiseClassName\Sanitiser;
use SilverStripe\ORM\FieldType\DBField;

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

    private static $plural_name = 'Tour Booking Settings';

    #######################
    ### Model Section
    #######################
    private static $table_name = 'TourBookingSettings';

    private static $db = [
        'MaximumNumberPerGroup' => 'Int',
        'NumberOfDaysToGenerateToursInAdvance' => 'Int',
        'TourFullMessage' => 'Varchar',
        'WaitlistInfoMessage' => 'Varchar(511)',
        'GoogleCalendarVerificationCode' => 'Varchar(255)',
        'ConfirmationPageContent' => 'HTMLText',
    ];

    private static $has_one = [
        'Administrator' => Member::class,
        'BookingConfirmationEmail' => EmailReminderNotificationSchedule::class,
        'UpdateConfirmationEmail' => EmailReminderNotificationSchedule::class,
        'CancellationConfirmationEmail' => EmailReminderNotificationSchedule::class,
        'WaitlistConfirmationEmail' => EmailReminderNotificationSchedule::class,
        'TourSpacesAvailableEmail' => EmailReminderNotificationSchedule::class,
    ];

    #######################
    ### Further DB Field Details
    #######################

    private static $defaults = [
        'MaximumNumberPerGroup' => '10',
        'NumberOfDaysToGenerateToursInAdvance' => 60,
    ];

    private static $default_sort = [
        'ID' => 'ASC',
    ];

    private static $required_fields = [
        'AdministratorID',
    ];

    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels = [
        'Administrator' => 'Tour Booking Manager',
        'TourFullMessage' => 'Tour Full Message',
        'MaximumNumberPerGroup' => 'Max Pax per Group',
        'NumberOfDaysToGenerateToursInAdvance' => 'Tour Auto Generation',
    ];

    private static $field_labels_right = [
        'Administrator' => 'Make sure that the administrator has the right email address.',
        'TourFullMessage' => 'Message displayed to a user when a tour is fully booked',
        'MaximumNumberPerGroup' => 'Maximum number of people per group (set to zero to ignore)',
        'NumberOfDaysToGenerateToursInAdvance' => 'The number of days in advance that tours will be auto generated. The auto generator runs at 2AM everyday, if this is set to 0 then no tours will be auto generated.',
    ];

    private static $summary_fields = [
        'Title' => 'Title',
    ];

    private static $email_fields = [
        'BookingConfirmationEmailID',
        'UpdateConfirmationEmailID',
        'CancellationConfirmationEmailID',
        'WaitlistConfirmationEmailID',
        'TourSpacesAvailableEmailID',
    ];

    private static $message_fields = [
        'TourFullMessage',
        'WaitlistInfoMessage',
        'ConfirmationPageContent',
    ];

    public function i18n_singular_name()
    {
        return _t('TourBookingSettings.SINGULAR_NAME', 'Tour Booking Settings');
    }

    public function i18n_plural_name()
    {
        return _t('TourBookingSettings.PLURAL_NAME', 'Tour Booking Settings');
    }

    public function getTitle()
    {
        return 'Tour Booking Settings';
    }

    public static function inst()
    {
        $obj = DataObject::get_one(TourBookingSettings::class);
        if (! $obj) {
            $obj = TourBookingSettings::create();
            $obj->write();
        }

        return $obj;
    }

    #######################
    ### can Section
    #######################

    public function canCreate($member = null, $context = [])
    {
        return DataObject::get_one(static::class) ? false : true;
    }

    public function canView($member = null, $context = [])
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    public function canEdit($member = null, $context = [])
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    public function canDelete($member = null, $context = [])
    {
        return false;
    }

    #######################
    ### write Section
    #######################

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (class_exists(GoogleCalendarInterface::class) && $this->owner->isChanged('GoogleCalendarVerificationCode')) {
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
        $email = Config::inst()->get(TourBookingSettings::class, 'manager_email');
        if (! $email) {
            $baseURL = Director::absoluteBaseURL();
            $baseURL = str_replace('https://', '', $baseURL);
            $baseURL = str_replace('http://', '', $baseURL);
            $baseURL = trim($baseURL, '/');
            $email = 'tours@' . $baseURL;
        }

        $group = PermissionProviderFactory::inst()
            ->setEmail($email)
            ->setFirstName('Tour')
            ->setSurname('Manager')
            ->setCode(Config::inst()->get(TourBookingSettings::class, 'group_code'))
            ->setPermissionCode('CMS_ACCESS_TOUR_ADMIN')
            ->setRoleTitle('Tour Manager Privileges')
            ->setPermissionArray(['CMS_ACCESS_TourBookingsAdmin'])
            ->CreateGroupAndMember();
        $member = $group->Members()->last();

        if (TourBookingSettings::get()->count() === 0) {
            DB::alteration_message('Creating Tour Bookings Settings', 'created');
            $obj = TourBookingSettings::create();
            if (! $obj->AdministratorID) {
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
        $controller = singleton(TourBookingsAdmin::class);
        return $controller->Link() . Sanitiser::sanitise($this->ClassName) . '/EditForm/field/' . Sanitiser::sanitise($this->ClassName) . '/item/' . $this->ID . '/edit';
    }

    public function CMSAddLink()
    {
        $controller = singleton(TourBookingsAdmin::class);
        return $controller->Link() . Sanitiser::sanitise($this->ClassName) . '/EditForm/field/' . Sanitiser::sanitise($this->ClassName) . '/item/new';
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        //do first??
        $rightFieldDescriptions = $this->Config()->get('field_labels_right');
        foreach ($rightFieldDescriptions as $field => $desc) {
            $formField = $fields->DataFieldByName($field);
            if (! $formField) {
                $formField = $fields->DataFieldByName($field . 'ID');
            }
            if ($formField) {
                $formField->setDescription($desc);
            }
        }
        $group = Group::get()
            ->filter(
                [
                    'code' => [
                        Config::inst()->get(TourBookingSettings::class, 'group_code'),
                        'ADMIN',
                    ],
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

        if (class_exists(GoogleCalendarInterface::class) && ! Director::isDev()) {
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
                            ' . $calendar->getAuthLink() . '
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
            if ($this->{$field}) {
                $emailNotifier = EmailReminderNotificationSchedule::get()->byID($this->{$field});
                if ($emailNotifier) {
                    $cmsLink = $emailNotifier->CMSEditLink();
                    if ($cmsLink) {
                        $formField->setRightTitle(
                            DBField::create_field(
                                'HTMLText',
                                '<a href="' . $cmsLink . '" target="_blank">Edit </a> the content of this email'
                            )
                        );
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

        $bookingSingleton = Injector::inst()->get(Booking::class);

        $this->AddUsefulLinkToFields(
            $fields,
            'Open Tour Booking Page',
            TourBookingPageController::find_link()
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Create Future Tours Now',
            Injector::inst()->get(TourBuilder::class)->Link(),
            'This task runs regularly, but you can run it now by clicking above link.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Monthly Tour Report',
            Injector::inst()->get(MonthlyTourReport::class)->Link(),
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
            '/admin/tour-bookings/Sunnysideup-Bookings-Model-DateInfo/EditForm/field/Sunnysideup-Bookings-Model-DateInfo/item/new',
            'Add new tour date(s) with all the details and then create the tours using the <a href="/dev/tasks/TourBuilder/">create tours button</a>.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Add adhoc tour at irregular time',
            '/admin/tour-bookings/Sunnysideup-Bookings-Model-TimesForTour/EditForm/field/Sunnysideup-Bookings-Model-TimesForTour/item/new',
            'Add the new time first and then add the tour dates.
            After that you will have to create the tours using the <a href="/dev/tasks/TourBuilder/">create tours button</a>.'
        );

        $this->AddUsefulLinkToFields(
            $fields,
            'Find out what tour date rule applies on a certain day',
            '/admin/tour-bookings/Sunnysideup-Bookings-Model-DateInfo',
            'Click on the magnifying glass and search for a particular day.'
        );

        return $fields;
    }
}
