<?php

namespace Sunnysideup\Bookings\Model;

use Dynamic\CountryDropdownField\Fields\CountryDropdownField;
use SilverStripe\Control\Director;

use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Security\Member;
use SunnySideUp\EmailReminder\Model\EmailReminderEmailRecord;
use Sunnysideup\Bookings\Pages\TourBookingPageController;

class Booking extends TourBaseClass
{
    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Tour Booking';

    private static $plural_name = 'Tour Bookings';

    #######################
    ### Model Section
    #######################

    private static $table_name = 'Booking';

    private static $db = [
        'Code' => 'Varchar(9)',
        'Date' => 'Date',
        'TotalNumberOfGuests' => 'Int',
        'InitiatingFirstName' => 'Varchar',
        'InitiatingSurname' => 'Varchar',
        'InitiatingEmail' => 'Varchar',
        'CountryOfOrigin' => 'Varchar(2)',
        'CityTown' => 'Varchar',
        'PrimaryPhone' => 'PhoneField',
        'SecondaryPhone' => 'PhoneField',
        'NumberOfChildren' => 'Int',
        'SpecialAssistanceRequired' => 'Boolean',
        'SpecialAssistanceRequiredInfo' => 'Varchar',
        'HasArrived' => 'Boolean',
        'Cancelled' => 'Boolean',
        'TotalGuestsAdminOverride' => 'Boolean',
        'ReferralText' => 'Varchar',
    ];

    private static $has_one = [
        'BookingMember' => Member::class,
        'Tour' => Tour::class,
    ];

    private static $many_many = [
        'ReferralOptions' => ReferralOption::class,
    ];

    #######################
    ### Further DB Field Details
    #######################

    private static $indexes = [
        'Code' => true,
    ];

    private static $default_sort = [
        'ID' => 'DESC',
    ];

    private static $required_fields = [
        'TourID',
        'PrimaryPhone',
        'TotalNumberOfGuests',
        'InitiatingFirstName',
        'InitiatingEmail',
    ];

    private static $searchable_fields = [
        'Code' => 'PartialMatchFilter',
        'InitiatingFirstName' => 'PartialMatchFilter',
        'InitiatingSurname' => 'PartialMatchFilter',
        'InitiatingEmail' => 'PartialMatchFilter',
        'TotalNumberOfGuests' => 'ExactMatchFilter',
        'PrimaryPhone' => 'PartialMatchFilter',
        'SecondaryPhone' => 'PartialMatchFilter',
        'HasArrived' => 'ExactMatchFilter',
        'Cancelled' => 'ExactMatchFilter',
    ];

    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels = [
        'Code' => 'Booking Reference',
        'InitiatingFirstName' => 'First Name',
        'InitiatingSurname' => 'Surname',
        'InitiatingEmail' => 'Email',
        'TotalNumberOfGuests' => 'Number of People',
        'BookingMember' => 'Contact',
        'HasArrived' => 'Have Arrived',
        'NumberOfChildren' => 'Number of Children attending the tour',
        'PrimaryPhone' => 'Mobile Phone',
        'SecondaryPhone' => 'Secondary Contact Phone',
        'CountryOfOrigin' => 'What country are your from?',
        'CityTown' => 'City or Town',
        'NumberOfAdults' => 'Adults',
        'SpecialAssistanceRequired' => 'Special Assistance',
        'SpecialAssistanceRequiredInfo' => 'Please let us know how we can help?',
    ];

    private static $field_labels_right = [
        'BookingMember' => 'Person making the booking',
        'PrimaryPhone' => 'If you don\'t have a mobile number, please provide a landline number',
        'SecondaryPhone' => 'Enter as +64 5 555 2222',
        'CountryOfOrigin' => 'In what country do most of the people in this group live?',
        'TotalNumberOfGuests' => 'Including children',
    ];

    private static $read_only_fields = [
        'Code',
        'Date',
        'InitiatingSurname',
        'InitiatingFirstName',
        'InitiatingEmail',
    ];

    private static $summary_fields = [
        'Tour.Title' => 'Tour',
        'Created' => 'Created',
        'LastEdited' => 'Edited',
        'Code' => 'Reference',
        'BookingMember.Title' => 'Contact',
        'CountryOfOrigin' => 'Country',
        'TotalNumberOfGuests' => 'Guests',
        'Cancelled.Nice' => 'Cancelled',
    ];

    private static $casting = [
        'Title' => 'Varchar',
        'NumberOfAdults' => 'Int',
        'BookingReference' => 'Varchar',
    ];

    public function i18n_singular_name()
    {
        return _t('Booking.SINGULAR_NAME', 'Tour Booking');
    }

    public function i18n_plural_name()
    {
        return _t('Booking.PLURAL_NAME', 'Tour Bookings');
    }

    public function getTitle()
    {
        $v =
            'Booking by ' . $this->BookingMember()->getTitle() .
            ' for ' . $this->getNumberOfAdults()->Nice() . ' adults,' .
            ' and ' . $this->NumberOfChildren . ' children, ' .
            ' on ' . $this->Tour()->Date .
            ' at ' . $this->Tour()->Time;

        return DBField::create_field('Varchar', $v);
    }

    public function NumberOfAdults()
    {
        return $this->getNumberOfAdults();
    }

    public function getNumberOfAdults()
    {
        $v = $this->TotalNumberOfGuests - $this->NumberOfChildren;

        return DBField::create_field('Int', $v);
    }

    public function BookingReference()
    {
        return $this->getBookingReference();
    }

    public function getBookingReference()
    {
        $v = strtoupper(substr($this->Code, 0, 5));

        return DBField::create_field('Varchar', $v);
    }

    #######################
    ### can Section
    #######################

    public function canEdit($member = null, $context = [])
    {
        if ($this->HasArrived) {
            return false;
        }

        return parent::canEdit($member);
    }

    public function canDelete($member = null, $context = [])
    {
        if ($this->HasArrived) {
            return false;
        }

        return parent::canEdit($member);
    }

    #######################
    ### write Section
    #######################

    public function validate()
    {
        $result = parent::validate();
        //check for other bookings with same email ....
        if ($this->TourID) {
            $errorCount = Booking::get()
                ->filter(['InitiatingEmail' => $this->InitiatingEmail,  'TourID' => $this->TourID])
                ->exclude(['ID' => intval($this->ID)])
                ->count();
            if ($errorCount) {
                $result->addError(
                    'Another booking for this tour with the same email already exists. You can only make one booking per tour per email number.',
                    'UNIQUE_' . $this->ClassName . '_InitiatingEmail'
                );
            }

            if ($this->PrimaryPhone) {
                $errorCount = Booking::get()
                    ->filter(['PrimaryPhone' => $this->PrimaryPhone,  'TourID' => $this->TourID])
                    ->exclude(['ID' => intval($this->ID)])
                    ->count();
                if ($errorCount) {
                    $result->addError(
                        'Another booking for this tour with the same mobile phone already exists. You can only make one booking per tour per mobile phone number.',
                        'UNIQUE_' . $this->ClassName . 'PrimaryPhone'
                    );
                }
            }
            $tour = Tour::get()->byID($this->TourID);
            if ($tour) {
                $availableRaw = $tour->getNumberOfPlacesAvailable()->RAW();
                if ($this->exists()) {
                    //we have to get the booking from the DB again because that value for $this->TotalNumberOfGuests has already changed
                    $beforeUpdate = Booking::get()->byID($this->ID);
                    $placesAvailable = $availableRaw + $beforeUpdate->TotalNumberOfGuests;
                    //one extra check to make sure placesAvailable is never greater the how many places available for the tour
                    if ($placesAvailable > $tour->TotalSpacesAtStart) {
                        $placesAvailable = $tour->TotalSpacesAtStart;
                    }
                } else {
                    $placesAvailable = $availableRaw;
                }
                //admins can override the following validation
                $adminOverrideNotSet = $this->TotalGuestsAdminOverride ? false : true;
                if ($this->TotalNumberOfGuests > $placesAvailable && $adminOverrideNotSet) {
                    $result->addError(
                        'Sorry, there are not enough places available for your booking. Your booking is for ' . $this->TotalNumberOfGuests . ' and the places still available is: ' . $placesAvailable,
                        'UNIQUE_' . $this->ClassName . '_NumberOfPlacesAvailable'
                    );
                }
            }
        }
        if (intval($this->TotalNumberOfGuests) < 1) {
            $result->addError(
                'You need to have at least one person attending to make a booking.',
                'UNIQUE_' . $this->ClassName . '_TotalNumberOfGuests'
            );
        }
        if (intval($this->TotalNumberOfGuests) < (intval($this->NumberOfChildren) + 1)) {
            $result->addError(
                'You need to have at least one adult attending. It appears you only have children listed for this booking.',
                'UNIQUE_' . $this->ClassName . '_NumberOfChildren'
            );
        }

        return $result;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (! $this->Code) {
            $this->Code = hash('md5', uniqid());
        }
        $this->Date = $this->Tour()->Date;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        //create member ...
        if (! $this->BookingMemberID && $this->InitiatingEmail) {
            $member = Member::get()->filter(['Email' => $this->InitiatingEmail])->last();
            if (! $member) {
                $member = Member::create(
                    [
                        'Email' => $this->InitiatingEmail,
                        'FirstName' => $this->InitiatingFirstName,
                        'Surname' => $this->InitiatingSurname,
                    ]
                );
                $member->write();
            }
            $this->BookingMemberID = $member->ID;
            if ($this->BookingMemberID) {
                $this->write();
            }
        }

        //always update the tour after a booking has been updated/added
        //this ensures that data for the tour is always up to date and that it will be synched with the google calendar
        $this->Tour()->write();
    }

    public function onAfterDelete()
    {
        parent::onAfterDelete();
        //always update the tour after a booking has been deleted
        //this ensures that data for the tour is always up to date and that it will be synched with the google calendar
        $this->Tour()->write();
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        //...
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

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('TotalGuestsAdminOverride');
        $fields->insertBefore(
            CheckboxField::create(
                'TotalGuestsAdminOverride',
                'Total Guests Override (Admin Only)'
            )->setDescription('If this is checked, it will allow you create a booking with more guests than spaces available on the tour. Useful when you need to add bookings for large groups.'),
            'InitiatingFirstName'
        );

        if ($this->TourID) {
            $tour = $this->Tour();
            $fields->replaceField(
                'TourID',
                LiteralField::create(
                    'TourDetails',
                    '<div class="field readonly">
                        <label class="left">Tour</label>
                        <div class="middleColumn">
                            <a href="' . $tour->CMSEditLink() . '">' . $tour->getTitle() . '</a>
                        </div>
                    </div>'
                )
            );
        }

        $fields->replaceField(
            'Country Of Origin',
            CountryDropdownField::create(
                'CountryOfOrigin',
                'CountryOfOrigin'
            )
        );

        $fields->replaceField(
            'Code',
            ReadonlyField::create(
                'Code',
                'Code'
            )
        );

        if ($this->BookingMemberID) {
            $fields->replaceField(
                'BookingMemberID',
                ReadonlyField::create(
                    'BookingMemberInfo',
                    'Person making the booking',
                    $this->BookingMember()->getTitle() . ' - ' . $this->BookingMember()->Email
                )
            );

            $readonlyfields = Config::inst()->get(Booking::class, 'read_only_fields');
            foreach ($readonlyfields as $replaceField) {
                $fields->replaceField(
                    $replaceField,
                    $fields->dataFieldByName($replaceField)->performReadonlyTransformation()->setTitle('Original        ' . str_replace('Initiating', '', $replaceField))
                );
            }
        } else {
            $fields->removeByName('BookingMemberID');
            $fields->removeByName('Date');
            $fields->removeByName('TourID');
            $today = date('Y-m-d');
            $tours = Tour::get()->filter(
                ['Date:GreaterThanOrEqual' => $today]
            )->map()->toArray();
            $fields->insertBefore(
                DropdownField::create('TourID', 'Tour', $tours),
                'TotalNumberOfGuests'
            );
        }

        $fields->removeByName('ReferralText');
        $fields->removeByName('ReferralOptions');

        $fields->addFieldsToTab(
            'Root.ReferralInfo',
            [

                HeaderField::create(
                    'ReferralInfoHeading',
                    'How did the booking contact hear about this tour?',
                    2
                ),
                GridField::create(
                    'ReferralOptions',
                    'Options Selected',
                    $this->ReferralOptions(),
                    GridFieldConfig_RecordViewer::create()
                ),
                ReadonlyField::create(
                    'ReferralText',
                    'More Details'
                )->setRightTitle('There will only be data here if the user provides more details when selecting the "other" option.'),
            ]
        );

        $emailRecords = EmailReminderEmailRecord::get()->filter(['ExternalRecordID' => $this->ID]);

        $fields->addFieldsToTab(
            'Root.Messages',
            [
                ReadonlyField::create(
                    'Created',
                    'Booking Made'
                ),
                GridField::create(
                    'Email',
                    'Emails Sent',
                    $emailRecords,
                    GridFieldConfig_RecordViewer::create()
                ),
            ]
        );

        $this->AddUsefulLinkToFields($fields, 'Add New Booking', $this->AddLink());
        if ($this->Code) {
            $this->AddUsefulLinkToFields($fields, 'Confirm Booking', $this->ConfirmLink());
            $this->AddUsefulLinkToFields($fields, 'Edit Booking', $this->ConfirmLink());
            $this->AddUsefulLinkToFields($fields, 'Cancel Booking', $this->CancelLink());
        }
        return $fields;
    }

    public function getFrontEndFields($params = null)
    {
        $fields = parent::getFrontEndFields($params);
        $labels = Config::inst()->get(Booking::class, 'field_labels');
        $fieldLabelsRight = Config::inst()->get(Booking::class, 'field_labels_right');
        $fields->removeByName('Code');
        $fields->removeByName('Date');
        $fields->removeByName('HasArrived');
        $fields->removeByName('Cancelled');
        $fields->removeByName('BookingMemberID');
        $fields->removeByName('TotalNumberOfGuests');
        $fields->removeByName('SecondaryPhone');
        $fields->removeByName('TotalGuestsAdminOverride');
        $fields->removeByName('ReferralText');

        $fields->replaceField(
            'PrimaryPhone',
            TextField::create(
                'PrimaryPhone',
                $labels['PrimaryPhone']
            )->setRightTitle($fieldLabelsRight['PrimaryPhone'])
        );

        $fields->replaceField(
            'InitiatingEmail',
            EmailField::create(
                'InitiatingEmail',
                $labels['InitiatingEmail']
            )
        );

        $fields->replaceField(
            'NumberOfChildren',
            NumericField::create(
                'NumberOfChildren',
                $labels['NumberOfChildren']
            )->setScale()
        );

        $fields->replaceField(
            'CountryOfOrigin',
            CountryDropdownField::create(
                'CountryOfOrigin',
                $labels['CountryOfOrigin']
            )
        );

        $fields->replaceField(
            'CityTown',
            TextField::create(
                'CityTown',
                $labels['CityTown']
            )
        );

        $fields->replaceField(
            'TourID',
            HiddenField::create(
                'TourID',
                'TourID'
            )
        );
        return $fields;
    }

    /**
     * Validation for the front end
     * @return RequiredFields
     */
    public function getFrontEndValidator()
    {

        $fields = Config::inst()->get(Booking::class, 'required_fields');

        return RequiredFields::create($fields);
    }

    /**
     * This function is used to exclude cancelled bookings from reminder and follow up emails
     *
     * @param  EmailReminderNotificationSchedule  $reminder
     * @param DataList $records
     *
     * @return boolean
     */
    public function EmailReminderExclude($reminder, $records)
    {
        return $this->Cancelled;
    }

    #######################
    ### Links
    #######################

    public function AddLink($absolute = false)
    {
        $v = TourBookingPageController::find_link('signup');
        if ($absolute) {
            $v = Director::absoluteURL($v);
        }
        return $v;
    }

    public function ConfirmLink($absolute = false)
    {
        if ($this->Code) {
            $v = TourBookingPageController::find_link('confirmsignup') . substr($this->Code, 0, 9) . '/';
            if ($absolute) {
                $v = Director::absoluteURL($v);
            }
            return $v;
        }

        return 'error';
    }

    public function EditLink($absolute = false)
    {
        if ($this->Code) {
            $v = TourBookingPageController::find_link('update') . substr($this->Code, 0, 9) . '/';
            if ($absolute) {
                $v = Director::absoluteURL($v);
            }
            return $v;
        }

        return 'error';
    }

    public function CancelLink($absolute = false)
    {
        if ($this->Code) {
            $v = TourBookingPageController::find_link('cancel') . substr($this->Code, 0, 9) . '/';
            if ($absolute) {
                $v = Director::absoluteURL($v);
            }
            return $v;
        }
        return 'error';
    }

    protected function CurrentMemberIsOwner()
    {
        if (Member::currentUserID() === $this->BookingMemberID) {
            return true;
        }

        return false;
    }
}
