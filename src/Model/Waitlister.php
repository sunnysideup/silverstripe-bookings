<?php

namespace Sunnysideup\Bookings\Model;






use Sunnysideup\Bookings\Model\Waitlister;
use Sunnysideup\Bookings\Model\Tour;
use SilverStripe\Control\Email\Email;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\RequiredFields;





class Waitlister extends TourBaseClass
{


    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Waitlister';

    public function i18n_singular_name()
    {
        return _t('Waitlister.SINGULAR_NAME', Waitlister::class);
    }

    private static $plural_name = 'Waitlisters';

    public function i18n_plural_name()
    {
        return _t('Waitlister.PLURAL_NAME', 'Waitlisters');
    }


    #######################
    ### Model Section
    #######################


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * OLD: private static $db (case sensitive)
  * NEW: 
    private static $table_name = '[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]';

    private static $db (COMPLEX)
  * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    
    private static $table_name = 'Waitlister';

    private static $db = [
        'Code' => 'Varchar(9)',
        'TotalNumberOfGuests' => 'Int',
        'InitiatingFirstName' => 'Varchar',
        'InitiatingSurname' => 'Varchar',
        'InitiatingEmail' => 'Varchar',
        'PrimaryPhone' => 'PhoneField',
        'TourDate' => 'Date'
    ];

    private static $has_one = [
        'Tour' => Tour::class
    ];

    private static $field_labels = [
        'Code' => 'Waitlist Reference',
        'InitiatingFirstName' => 'First Name',
        'InitiatingSurname' => 'Surname',
        'InitiatingEmail' => Email::class,
        'TotalNumberOfGuests' => 'Number of people in your group.',
        'PrimaryPhone' => 'Mobile Phone'
    ];

    private static $field_labels_right = [
        'PrimaryPhone' => 'If you don\'t have a mobile number, please provide a landline number',
        'TotalNumberOfGuests' => 'Including children'
    ];

    private static $summary_fields = [
        'Code' => 'Code',
        'TotalNumberOfGuests' => 'Number of Spaces',
        'InitiatingFirstName' => 'First Name',
        'PrimaryPhone' => 'Phone',
        'InitiatingEmail' => 'Email'
    ];

    private static $required_fields = [
        'InitiatingFirstName',
        'InitiatingSurname',
        'InitiatingEmail',
        'TotalNumberOfGuests',
        'PrimaryPhone'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->replaceField(
            'TourDate',
            ReadonlyField::create(
                'TourDate',
                'TourDate'
            )
        );

        return $fields;
    }

    public function getFrontEndFields($params = null)
    {
        $fields = parent::getFrontEndFields($params);

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $labels = Config::inst()->get($this->ClassName, 'field_labels');

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $fieldLabelsRight = Config::inst()->get($this->ClassName, 'field_labels_right');
        $fields->removeByName('Code');
        $fields->removeByName('TourDate');

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

        return $fields;
    }

    /**
     * Validation for the front end
     * @return RequiredFields
     */
    public function getFrontEndValidator()
    {

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $fields = Config::inst()->get($this->ClassName, 'required_fields');

        return RequiredFields::create($fields);
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (! $this->Code) {
            $this->Code = substr(hash('md5', uniqid()), 0, 9);
        }
        if (! $this->TourDate) {
            $this->TourDate = $this->Tour()->Date;
        }
    }

    public function FutureWaitlistings()
    {
        return Waitlister::get()
                ->filter(
                    [
                        'InitiatingEmail' => $this->InitiatingEmail,
                        'TourDate:GreaterThanOrEqual' => date('Y-m-d')
                    ]
                )
                ->sort('TourDate', 'ASC');
    }
}

