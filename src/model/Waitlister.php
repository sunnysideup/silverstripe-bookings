<?php



class Waitlister extends TourBaseClass
{


    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Waitlister';

    public function i18n_singular_name()
    {
        return _t('Waitlister.SINGULAR_NAME', 'Waitlister');
    }

    private static $plural_name = 'Waitlisters';

    public function i18n_plural_name()
    {
        return _t('Waitlister.PLURAL_NAME', 'Waitlisters');
    }


    #######################
    ### Model Section
    #######################

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
        'Tour' => 'Tour'
    ];

    private static $field_labels = [
        'Code' => 'Waitlist Reference',
        'InitiatingFirstName' => 'First Name',
        'InitiatingSurname' => 'Surname',
        'InitiatingEmail' => 'Email',
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
        $labels = Config::inst()->get($this->ClassName, 'field_labels');
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
