<?php



class DateInfo extends TourBaseClass
{

    /**
     * [best_match_for_date description]
     * @param  int $dateTS
     * @return DateInfo
     */
    public static function best_match_for_date($dateTS)
    {
        $dateInfos = DateInfo::get()->exclude(['Archived' => 1])->sort(['SortOrder' => 'DESC']);
        foreach ($dateInfos as $dateInfo) {
            //archive ...
            // $dateInfo->write();
            if ($dateInfo->IsDateMatch($dateTS)) {
                return $dateInfo;
            }
        }

        return false;
    }

    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Tour Date - Info and Rules';

    public function i18n_singular_name()
    {
        return _t('DateInfo.SINGULAR_NAME', 'Tour Date - Info and Rules');
    }

    private static $plural_name = 'Tour Dates - Info and Rules';

    public function i18n_plural_name()
    {
        return _t('DateInfo.PLURAL_NAME', 'Tour Dates - Info and Rules');
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
    
    private static $table_name = 'DateInfo';

    private static $db = [
        'Title' => 'Varchar',
        'OneDayOnly' => 'Boolean',
        'FromDate' => 'Date',
        'UntilDate' => 'Date',
        'RepeatEvery' => 'Enum(\'No Repeat,Week,Fortnight,Month,Quarter,Year\', \'No Repeat\')',
        'SortOrder' => 'Int',
        'PublicContent' => 'HTMLText',
        'PrivateContent' => 'HTMLText',
        'Archived' => 'Boolean',
        'NoTourTimes' => 'Boolean'
    ];

    private static $has_many = [
        'Tours' => 'Tour'
    ];

    private static $many_many = [
        'TourTimes' => 'TimesForTour'
    ];

    private static $many_many_extraFields = [];


    #######################
    ### Further DB Field Details
    #######################

    private static $indexes = [
        'SortOrder' => true,
        'OneDayOnly' => true,
        'RepeatEvery' => true,
        'FromDate' => true,
        'Title' => true,
        'UntilDate' => true,
        'Archived' => true
    ];

    private static $default_sort = [
        'Archived' => 'ASC',
        'SortOrder' => 'ASC',
        'ID' => 'ASC'
    ];

    private static $required_fields = [
        'Title'
    ];

    private static $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'RepeatEvery' => 'ExactMatchFilter',
        'Created' => array(
            'field' => 'TextField',
            'filter' => 'TourTimesToApplyForCertainDay_Filter',
            'title' => 'What rules applies on date? (e.g Today, 1 jan 2007, or next Thursday)'
        )
    ];

    #######################
    ### Field Names and Presentation Section
    #######################

    private static $defaults = [
        'SortOrder' => 0,
        'NumberOfSpacesAvailable' => 15
    ];

    private static $field_labels = [
        'SortOrder' => 'Priority',
        'FromDate' => 'Starts',
        'UntilDate' => 'Ends',
        'OneDayOnly' => 'One Day Only',
        'Title' => 'Name'
    ];

    private static $field_labels_right = [
        'SortOrder' => 'When tours are auto-generated a Date Info/Rule with a HIGHER number will overrule a Date Info/Rule with a lower number.</br> Closed days (eg no tours) should always have a higher priority than open days.',
        'Title' => 'Helps to identify this entry',
        'FromDate' => 'First day of period',
        'UntilDate' => 'Last day of period',
        'PrivateContent' => 'More information for staff only',
        'PublicContent' => 'Information for public'
    ];

    private static $summary_fields = [
        'NoTourTimes.Nice' => 'Closed',
        'Title' => 'Title',
        'CalculatedFromDate' => 'From',
        'CalculatedUntilDate' => 'Until',
        'RepeatEvery' => 'Repeats ... ',
        'TourTimesNice' => 'Tour Times',
    ];


    #######################
    ### Casting Section
    #######################


    private static $casting = [
        'NumberOfDays' => 'Int',
        'CalculatedFromDate' => 'Varchar',
        'CalculatedUntilDate' => 'Varchar',
        'TourTimesNice' => 'HTMLText',
    ];

    public function NumberOfDays()
    {
        return $this->getNumberOfDays();
    }
    public function getNumberOfDays()
    {
        if ($this->OneDayOnly) {
            return 1;
        }
        $v = round(((strtotime($this->UntilDate) - strtotime($this->FromDate)) / 86400) + 1);

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
        if($this->NoTourTimes) {
            $html = 'CLOSED';
        } else {
            $html = '- '.implode('<br />- ', $this->TourTimes()->column('Title'));
        }
        return DBField::create_field('HTMLText', $html);
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
                    $postfix = ' starting ' .  date('jS M', strtotime($date));
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
                    $postfix .= ' starting from ' . date('M', strtotime($date));
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
            $value = $prefix . date($format, strtotime($date)) . $postfix;
        } elseif (!$fromDate && !$this->OneDayOnly) {
            $value = $prefix . date($format, strtotime($date)) . $postfix;
        }

        return DBField::create_field('Varchar', $value);
    }

    /**
     * does a particular date match this DateInfo
     * @param  int $dateTS date to match as timestamp
     *
     * @return boolean
     */
    public function IsDateMatch($dateTS)
    {
        $fromDateTS = strtotime($this->FromDate);
        $untilDateTS = strtotime($this->UntilDate);
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
                if ($fromDateTS <= $dateTS && $untilDateTS >= $dateTS) {
                    return true;
                } else {
                    return false;
                }
                break;
        }
        while ($fromDateTS <= $dateTS) {
            if ($fromDateTS <= $dateTS && $untilDateTS >= $dateTS) {
                return true;
            }
            $fromDateTS = strtotime($toAdd, $fromDateTS);
            $untilDateTS = strtotime($toAdd, $untilDateTS);
        }

        return false;
    }

    #######################
    ### can Section
    #######################



    #######################
    ### write Section
    #######################


    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->OneDayOnly || !$this->UntilDate) {
            $this->UntilDate = $this->FromDate;
        }
        if ($this->RepeatEvery == 'No Repeat') {
            if (strtotime('today') > strtotime($this->UntilDate)) {
                $this->Archived = true;
            }
        }
        if ($this->NoTourTimes) {
            $this->TourTimes()->removeAll();
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


        $fields->DataFieldByName('FromDate')->setConfig('showcalendar', 1);
        $fields->DataFieldByName('UntilDate')->setConfig('showcalendar', 1);

        $fields->removeByName('TourTimes');

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

        $fields->dataFieldByName('PublicContent')->setRows('7');
        $fields->dataFieldByName('PrivateContent')->setRows('7');

        $link = Injector::inst()->get('TourBuilder')->Link();

        if($fields->fieldByName('Root.Tours')) {
            $fields->fieldByName('Root.Tours')->setTitle('Created Tours for this Rule');
        }

        return $fields;
    }
}

