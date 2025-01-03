<?php

namespace Sunnysideup\Bookings\Model;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBField;
use Sunnysideup\Bookings\Search\TourTimesToApplyForCertainDayFilter;
use Sunnysideup\Bookings\Tasks\TourBuilder;

/**
 * Class \Sunnysideup\Bookings\Model\DateInfo
 *
 * @property string $Title
 * @property bool $OneDayOnly
 * @property string $FromDate
 * @property string $UntilDate
 * @property string $RepeatEvery
 * @property int $SortOrder
 * @property string $PublicContent
 * @property string $PrivateContent
 * @property bool $Archived
 * @property bool $NoTourTimes
 * @method DataList|Tour[] Tours()
 * @method ManyManyList|TimesForTour[] TourTimes()
 */
class DateInfo extends TourBaseClass
{
    //######################
    //## Names Section
    //######################

    private static $singular_name = 'Tour Date - Info and Rules';

    private static $plural_name = 'Tour Dates - Info and Rules';

    //######################
    //## Model Section
    //######################

    private static $table_name = 'DateInfo';

    private static $db = [
        'Title' => 'Varchar',
        'OneDayOnly' => 'Boolean',
        'FromDate' => 'Date',
        'UntilDate' => 'Date',
        'RepeatEvery' => "Enum('No Repeat,Week,Fortnight,Month,Quarter,Year', 'No Repeat')",
        'SortOrder' => 'Int',
        'PublicContent' => 'HTMLText',
        'PrivateContent' => 'HTMLText',
        'Archived' => 'Boolean',
        'NoTourTimes' => 'Boolean',
    ];

    private static $has_many = [
        'Tours' => Tour::class,
    ];

    private static $many_many = [
        'TourTimes' => TimesForTour::class,
    ];

    private static $many_many_extraFields = [];

    //######################
    //## Further DB Field Details
    //######################

    private static $indexes = [
        'SortOrder' => true,
        'OneDayOnly' => true,
        'RepeatEvery' => true,
        'FromDate' => true,
        'Title' => true,
        'UntilDate' => true,
        'Archived' => true,
        'NoTourTimes' => true,
    ];

    private static $default_sort = 'NoTourTimes ASC, Archived ASC, SortOrder ASC, ID ASC';

    private static $required_fields = [
        'Title',
    ];

    private static $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'RepeatEvery' => 'ExactMatchFilter',
        'Created' => [
            'field' => TextField::class,
            'filter' => TourTimesToApplyForCertainDayFilter::class,
            'title' => 'What rules applies on date? (e.g Today, 1 jan 2007, or next Thursday)',
        ],
    ];

    //######################
    //## Field Names and Presentation Section
    //######################

    private static $defaults = [
        'SortOrder' => 0,
        'NumberOfSpacesAvailable' => 15,
    ];

    private static $field_labels = [
        'SortOrder' => 'Priority',
        'FromDate' => 'Starts',
        'UntilDate' => 'Ends',
        'OneDayOnly' => 'One Day Only',
        'Title' => 'Name',
    ];

    private static $field_labels_right = [
        'SortOrder' => '
            When tours are auto-generated a Date Info/Rule with a HIGHER number will overrule a Date Info/Rule with a lower number.</br>
            Closed days (eg no tours) should always have a higher priority than open days.',
        'Title' => 'Helps to identify this entry',
        'FromDate' => 'First day of period we are defining (e.g. first day of Xmas Holiday)',
        'UntilDate' => 'Last day of period we are defining (e.g. last day of Xmas Holiday)',
        'PrivateContent' => 'More information for staff only',
        'PublicContent' => 'Information for public',
        'RepeatEvery' => 'Rules run forever, if they repeat.',
    ];

    private static $summary_fields = [
        'NoTourTimes.NiceAndColourfullInvertedColours' => 'Closed',
        'Title' => 'Title',
        'CalculatedFromDateFormated' => 'From',
        'CalculatedUntilDateFormated' => 'Until',
        'RepeatEvery' => 'Repeats ... ',
        'TourTimesNice' => 'Tour Times',
    ];

    //######################
    //## Casting Section
    //######################

    private static $casting = [
        'NumberOfDays' => 'Int',
        'CalculatedFromDate' => 'Varchar',
        'CalculatedUntilDate' => 'Varchar',
        'TourTimesNice' => 'HTMLText',
    ];

    public function getCalculatedFromDateFormated()
    {
        return $this->getCalculatedNiceDateFormated($this->FromDate);
    }

    public function getCalculatedUntilDateFormated()
    {
        return $this->getCalculatedNiceDateFormated($this->UntilDate, false);
    }

    /**
     * [best_match_for_date description].
     *
     * @param int $dateTS
     *
     * @return DateInfo
     */
    public static function best_match_for_date($dateTS)
    {
        $dateInfos = DateInfo::get()->exclude(['Archived' => 1])->sort('SortOrder', 'DESC');
        foreach ($dateInfos as $dateInfo) {
            //archive ...
            // $dateInfo->write();
            if ($dateInfo->IsDateMatch($dateTS)) {
                return $dateInfo;
            }
        }

        return false;
    }

    public function i18n_singular_name()
    {
        return _t('DateInfo.SINGULAR_NAME', 'Tour Date - Info and Rules');
    }

    public function i18n_plural_name()
    {
        return _t('DateInfo.PLURAL_NAME', 'Tour Dates - Info and Rules');
    }

    public function NumberOfDays()
    {
        return $this->getNumberOfDays();
    }

    public function getNumberOfDays()
    {
        if ($this->OneDayOnly) {
            return 1;
        }
        $v = round(((strtotime((string) $this->UntilDate) - strtotime((string) $this->FromDate)) / 86400) + 1);

        return DBField::create_field('Int', $v);
    }

    public function CalculatedFromDate()
    {
        return $this->getCalculatedFromDate();
    }

    public function getCalculatedFromDate()
    {
        return $this->getCalculatedNiceDate($this->FromDate);
    }

    public function CalculatedUntilDate()
    {
        return $this->getCalculatedUntilDate();
    }

    public function getCalculatedUntilDate()
    {
        return $this->getCalculatedNiceDate($this->UntilDate, false);
    }

    public function TourTimesNice()
    {
        return $this->getTourTimesNice();
    }

    public function getTourTimesNice()
    {
        $html = $this->NoTourTimes ? 'CLOSED' : '- ' . implode('<br />- ', $this->TourTimes()->column('Title'));

        return DBField::create_field('HTMLText', $html);
    }

    /**
     * does a particular date match this DateInfo.
     *
     * @param int $dateTS date to match as timestamp
     *
     * @return bool
     */
    public function IsDateMatch($dateTS)
    {
        $fromDateTS = strtotime((string) $this->FromDate);
        $untilDateTS = strtotime((string) $this->UntilDate);
        switch ($this->RepeatEvery) {
            case 'Week':
                $toAdd = '+1 week';

                break;
            case 'Fortnight':
                $toAdd = '+2 weeks';

                break;
            case 'Month':
                $toAdd = '+1 month';

                break;
            case 'Quarter':
                $toAdd = '+3 month';

                break;
            case 'Year':
                $toAdd = '+1 year';

                break;
            default:
                return $fromDateTS <= $dateTS && $untilDateTS >= $dateTS;
        }
        while ($fromDateTS <= $dateTS) {
            if ($fromDateTS <= $dateTS && $untilDateTS >= $dateTS) {
                return true;
            }
            $fromDateTS = strtotime((string) $toAdd, $fromDateTS);
            $untilDateTS = strtotime((string) $toAdd, $untilDateTS);
        }

        return false;
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $list = DateInfo::get();
        foreach ($list as $item) {
            $item->write();
        }
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

        $fields->removeByName('TourTimes');
        $fields->removeByName('SortOrder');

        $fields->addFieldToTab(
            'Root.TourTimes',
            CheckboxField::create(
                'NoTourTimes',
                'No Tours'
            )->setDescription('Check this box, if there are no tours for these dates')
        );

        if (! $this->NoTourTimes) {
            $fields->addFieldToTab(
                'Root.TourTimes',
                CheckboxSetField::create(
                    'TourTimes',
                    'Tour Times',
                    TimesForTour::get()
                )
            );
        }

        $fields->dataFieldByName('PublicContent');
        $fields->dataFieldByName('PrivateContent');

        // $link = Injector::inst()->get(TourBuilder::class)->Link();

        if (null !== $fields->fieldByName('Root.Tours')) {
            $fields->fieldByName('Root.Tours')->setTitle('Created Tours for this Rule');
        }

        return $fields;
    }

    protected function getCalculatedNiceDateFormated($date, $fromDate = true)
    {
        $prefix = '';
        $format = 'D jS M Y';
        $postfix = '';
        $value = '';
        switch ($this->RepeatEvery) {
            case 'Week':
                $format = 'D';

                break;
            case 'Fortnight':
                $prefix = 'Every second ';
                $format = 'l';
                if ($fromDate) {
                    $postfix = ' starting ' . date('D jS M', strtotime((string) $date));
                }

                break;
            case 'Month':
                $format = 'D jS';
                $postfix = ' of every month';

                break;
            case 'Quarter':
                $format = 'D jS';
                $postfix = ' of the month';
                if ($fromDate) {
                    $postfix .= ' starting from ' . date('M', strtotime((string) $date));
                }

                break;
            case 'Year':
                $format = 'D jS M';

                break;
            default:
                $format = 'D jS M Y';

                break;
        }

        if ($fromDate) {
            $value = $prefix . date($format, strtotime((string) $date)) . $postfix;
        } elseif (! $fromDate && ! $this->OneDayOnly) {
            $value = $prefix . date($format, strtotime((string) $date)) . $postfix;
        }

        return DBField::create_field('Varchar', $value);
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
        if ($this->SortOrder < 10000 && $this->NoTourTimes) {
            $this->SortOrder += 1000;
        } elseif ($this->SortOrder > 10000 && ! $this->NoTourTimes) {
            $this->SortOrder -= 1000;
        }
        if ($this->OneDayOnly || ! $this->UntilDate) {
            $this->UntilDate = $this->FromDate;
        }
        if ('No Repeat' === $this->RepeatEvery) {
            if (strtotime('today') > strtotime((string) $this->UntilDate)) {
                $this->Archived = true;
            }
        }
        if ($this->NoTourTimes) {
            $this->TourTimes()->removeAll();
        }
    }

    protected function onAfterWrite()
    {
        parent::onAfterWrite();
        //...
    }

    protected function getCalculatedNiceDate($date, $fromDate = true)
    {
        $prefix = '';
        $format = 'jS M Y';
        $postfix = '';
        $value = '';
        switch ($this->RepeatEvery) {
            case 'Week':
                $format = 'D';

                break;
            case 'Fortnight':
                $prefix = 'Every second ';
                $format = 'l';
                if ($fromDate) {
                    $postfix = ' starting ' . date('jS M', strtotime((string) $date));
                }

                break;
            case 'Month':
                $format = 'jS';
                $postfix = ' of every month';

                break;
            case 'Quarter':
                $format = 'jS';
                $postfix = ' of the month';
                if ($fromDate) {
                    $postfix .= ' starting from ' . date('M', strtotime((string) $date));
                }

                break;
            case 'Year':
                $format = 'jS M';

                break;
            default:
                $format = 'jS M Y';

                break;
        }

        if ($fromDate) {
            $value = $prefix . date($format, strtotime((string) $date)) . $postfix;
        } elseif (! $fromDate && ! $this->OneDayOnly) {
            $value = $prefix . date($format, strtotime((string) $date)) . $postfix;
        }

        return DBField::create_field('Varchar', $value);
    }
}
