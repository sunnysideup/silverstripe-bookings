<?php

namespace Sunnysideup\Bookings\Model;

use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBTime;

/**
 * Class \Sunnysideup\Bookings\Model\TimesForTour
 *
 * @property string $Title
 * @property string $StartTime
 * @property int $Duration
 * @property int $NumberOfSpacesAvailable
 * @property string $PublicContent
 * @property string $PrivateContent
 * @method ManyManyList|DateInfo[] DateInfos()
 */
class TimesForTour extends TourBaseClass
{
    //######################
    //## Names Section
    //######################

    private static $singular_name = 'Tour Time';

    private static $plural_name = 'Tour Times';

    //######################
    //## Model Section
    //######################

    private static $table_name = 'TimesForTour';

    private static $db = [
        'Title' => 'Varchar',
        'StartTime' => 'Time',
        'Duration' => 'Int',
        'NumberOfSpacesAvailable' => 'Int',
        'PublicContent' => 'HTMLText',
        'PrivateContent' => 'HTMLText',
    ];

    private static $belongs_many_many = [
        'DateInfos' => DateInfo::class,
    ];

    //######################
    //## Further DB Field Details
    //######################

    private static $indexes = [
        'Title' => true,
    ];

    private static $defaults = [
        'Duration' => 60,
        'NumberOfSpacesAvailable' => 15,
    ];

    private static $default_sort = 'StartTime ASC, ID ASC';

    private static $required_fields = [
        'Title',
        'NumberOfSpacesAvailable',
        'StartTime',
        'Duration',
    ];

    //######################
    //## Field Names and Presentation Section
    //######################

    private static $field_labels = [
        'Title' => 'Name',
        'NumberOfSpacesAvailable' => 'Spaces available',
        'PrivateContent' => 'More information for staff only',
        'PublicContent' => 'Information for public',
    ];

    private static $field_labels_right = [
        'StartTime' => 'Please enter in 24-hour clock',
        'Duration' => 'In Minutes - e.g. 90',
        'Title' => 'E.g. early morning tour, lunch tour, etc...',
        'NumberOfSpacesAvailable' => 'Number of people who can join this tour time',
    ];

    private static $summary_fields = [
        'Title' => 'Name',
        'StartTime.Nice' => 'Starts',
        'Duration' => 'Minutes',
        'NumberOfSpacesAvailable' => 'Spaces Available',
    ];

    //######################
    //## Casting Section
    //######################

    private static $casting = [
        'EndTimeObj' => 'Time',
    ];

    public function i18n_singular_name()
    {
        return _t('TimesForTour.SINGULAR_NAME', 'Tour Time');
    }

    public function i18n_plural_name()
    {
        return _t('TimesForTour.PLURAL_NAME', 'Tour Times');
    }

    public function EndTimeObj()
    {
        return $this->getEndTime();
    }

    public function getEndTimeObj()
    {
        $fakeDate = date('Y-m-d') . ' ' . $this->StartTime;
        $fakeDateTS = strtotime((string) $fakeDate);
        $fakeDateTS = strtotime('+' . $this->Duration . ' minute', $fakeDateTS);

        $v = date('H:i:s', $fakeDateTS);

        return DBTime::create_field('Time', $v);
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        //...
    }

    //######################
    //## Import / Export Section
    //######################

    //######################
    //## CMS Edit Section
    //######################

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->dataFieldByName('PublicContent');
        $fields->dataFieldByName('PrivateContent');
        if (null !== $fields->fieldByName('Root.DateInfos')) {
            $fields->fieldByName('Root.DateInfos')->setTitle('Info and Rules');
        }

        return $fields;
    }

    //######################
    //## can Section
    //######################

    //######################
    //## write Section
    //######################

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        //...
    }

    protected function onAfterWrite()
    {
        parent::onAfterWrite();
        //...
    }
}
