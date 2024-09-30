<?php

namespace Sunnysideup\Bookings\Model;

use SilverStripe\Forms\CheckboxField;

/**
 * Class \Sunnysideup\Bookings\Model\ReferralOption
 *
 * @property string $Title
 * @property int $SortOrder
 * @property bool $IsOther
 * @property bool $Archived
 * @method ManyManyList|Booking[] Bookings()
 */
class ReferralOption extends TourBaseClass
{
    //######################
    //## Names Section
    //######################

    private static $singular_name = 'Referral Option';

    private static $plural_name = 'Referral Options';

    //######################
    //## Model Section
    //######################

    private static $table_name = 'ReferralOption';

    private static $db = [
        'Title' => 'Varchar',
        'SortOrder' => 'Int',
        'IsOther' => 'Boolean',
        'Archived' => 'Boolean',
    ];

    private static $belongs_many_many = [
        'Bookings' => Booking::class,
    ];

    //######################
    //## Further DB Field Details
    //######################

    private static $default_sort = 'Title ASC';

    private static $required_fields = [
        'Title',
    ];

    private static $summary_fields = [
        'Title' => 'Option',
        'Bookings.Count' => 'Number of Bookings',
        'Archived.NiceAndColourfullInvertedColours' => 'Archived',
    ];

    public function i18n_singular_name()
    {
        return _t(self::class . '.SINGULAR_NAME', 'Referral Option');
    }

    public function i18n_plural_name()
    {
        return _t(self::class . '.PLURAL_NAME', 'Referral Options');
    }

    //######################
    //## CMS Edit Section
    //######################

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('SortOrder');

        $hasOther = ReferralOption::get()->filter(['IsOther' => true])->first();
        if ($hasOther && $this->ID !== $hasOther->ID) {
            $fields->removeByName('IsOther');
        } else {
            $fields->replaceField(
                'IsOther',
                CheckboxField::create(
                    'IsOther',
                    'More Info Option'
                )->setDescription('When this option is selected on the booking form a text field will display allowing the user to provide more info.  Only one referral option can have provide this functionality.')
            );
        }

        $fields->dataFieldByName('Archived')->setDescription('If archived, this option will no longer display on the booking form. <strong>This record should never be deleted as it contains a record of all the data that has been collected for this option</strong>.');

        return $fields;
    }

    public function canDelete($member = null, $context = [])
    {
        if ($this->Bookings()->count() > 0) {
            return false;
        }

        return $this->CurrentUserIsTourManager($member);
    }
}
