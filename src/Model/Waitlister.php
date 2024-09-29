<?php

namespace Sunnysideup\Bookings\Model;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use Sunnysideup\Bookings\Forms\Fields\TourDateFilterField;
use Sunnysideup\Bookings\Search\TourDateFilter;

/**
 * Class \Sunnysideup\Bookings\Model\Waitlister
 *
 * @property string $Code
 * @property int $TotalNumberOfGuests
 * @property string $InitiatingFirstName
 * @property string $InitiatingSurname
 * @property string $InitiatingEmail
 * @property string $PrimaryPhone
 * @property string $TourDate
 * @property int $TourID
 * @method Tour Tour()
 */
class Waitlister extends TourBaseClass
{
    //######################
    //## Names Section
    //######################

    private static $singular_name = 'Waitlister';

    private static $plural_name = 'Waitlisters';

    //######################
    //## Model Section
    //######################

    private static $table_name = 'Waitlister';

    private static $db = [
        'Code' => 'Varchar(9)',
        'TotalNumberOfGuests' => 'Int',
        'InitiatingFirstName' => 'Varchar',
        'InitiatingSurname' => 'Varchar',
        'InitiatingEmail' => 'Varchar',
        'PrimaryPhone' => 'PhoneField',
        'TourDate' => 'Date',
    ];

    private static $has_one = [
        'Tour' => Tour::class,
    ];

    private static $field_labels = [
        'Code' => 'Waitlist Reference',
        'InitiatingFirstName' => 'First Name',
        'InitiatingSurname' => 'Surname',
        'InitiatingEmail' => 'Email',
        'TotalNumberOfGuests' => 'Number of people in your group.',
        'PrimaryPhone' => 'Mobile Phone',
    ];

    private static $field_labels_right = [
        'PrimaryPhone' => "If you don't have a mobile number, please provide a landline number",
        'TotalNumberOfGuests' => 'Including children',
    ];

    private static $summary_fields = [
        'Tour.Date.Nice' => 'Date',
        'Tour.StartTime.Nice' => 'Time',
        'Code' => 'Code',
        'TotalNumberOfGuests' => 'Number of Spaces',
        'InitiatingFirstName' => 'First Name',
        'PrimaryPhone' => 'Phone',
        'InitiatingEmail' => 'Email',
    ];

    private static $searchable_fields = [
        'Tour.Date' => [
            'field' => TourDateFilterField::class,
            'filter' => TourDateFilter::class,
            'title' => 'Tour Date',
        ],
        'Code' => 'PartialMatchFilter',
        'InitiatingFirstName' => 'PartialMatchFilter',
        'InitiatingSurname' => 'PartialMatchFilter',
        'InitiatingEmail' => 'PartialMatchFilter',
        'PrimaryPhone' => 'PartialMatchFilter',
        'TotalNumberOfGuests' => 'ExactMatchFilter',
    ];

    private static $required_fields = [
        'InitiatingFirstName',
        'InitiatingSurname',
        'InitiatingEmail',
        'TotalNumberOfGuests',
        'PrimaryPhone',
    ];


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: default_sort = [
  * NEW: default_sort = [ ...  (COMPLEX)
  * EXP: A string is preferred over an array
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    private static $default_sort = [
        'Created' => 'DESC',
    ];

    public function i18n_singular_name()
    {
        return _t('Waitlister.SINGULAR_NAME', 'Waitlister');
    }

    public function i18n_plural_name()
    {
        return _t('Waitlister.PLURAL_NAME', 'Waitlisters');
    }

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

        $labels = Config::inst()->get(Waitlister::class, 'field_labels');

        $fieldLabelsRight = Config::inst()->get(Waitlister::class, 'field_labels_right');
        $fields->removeByName('Code');
        $fields->removeByName('TourDate');

        $fields->replaceField(
            'PrimaryPhone',
            TextField::create(
                'PrimaryPhone',
                $labels['PrimaryPhone']
            )->setDescription($fieldLabelsRight['PrimaryPhone'])
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
     * Validation for the front end.
     *
     * @return RequiredFields
     */
    public function getFrontEndValidator()
    {
        $fields = Config::inst()->get(Waitlister::class, 'required_fields');

        return RequiredFields::create($fields);
    }

    public function FutureWaitlistings()
    {
        return Waitlister::get()
            ->filter(
                [
                    'InitiatingEmail' => $this->InitiatingEmail,
                    'TourDate:GreaterThanOrEqual' => date('Y-m-d'),
                ]
            )

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: ->sort(
  * NEW: ->sort( ...  (COMPLEX)
  * EXP: This method no longer accepts raw sql, only known field names.  If you have raw SQL then use ->orderBy
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            ->sort('TourDate', 'ASC')
        ;
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (! $this->Code) {
            $this->Code = substr(hash('md5', uniqid()), 0, 9);
        }
        if (! $this->TourDate) {
            $this->TourDate = $this->Tour()->Date;
        }
    }
}
