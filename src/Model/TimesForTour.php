<?php



class TimesForTour extends TourBaseClass
{


    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Tour Time';

    public function i18n_singular_name()
    {
        return _t('TimesForTour.SINGULAR_NAME', 'Tour Time');
    }

    private static $plural_name = 'Tour Times';

    public function i18n_plural_name()
    {
        return _t('TimesForTour.PLURAL_NAME', 'Tour Times');
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
    
    private static $table_name = 'TimesForTour';

    private static $db = [
        'Title' => 'Varchar',
        'StartTime' => 'Time',
        'Duration' => 'Int',
        'NumberOfSpacesAvailable' => 'Int',
        'PublicContent' => 'HTMLText',
        'PrivateContent' => 'HTMLText'
    ];

    private static $belongs_many_many = [
        'DateInfos' => 'DateInfo'
    ];


    #######################
    ### Further DB Field Details
    #######################

    private static $indexes = [
        'Title' => true
    ];

    private static $defaults = [
        'Duration' => 60,
        'NumberOfSpacesAvailable' => 15
    ];

    private static $default_sort = [
        'StartTime' => 'ASC',
        'ID' => 'ASC'
    ];

    private static $required_fields = [
        'Title',
        'NumberOfSpacesAvailable',
        'StartTime',
        'Duration'
    ];


    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels = [
        'Title' => 'Name',
        'NumberOfSpacesAvailable' => 'Spaces available',
        'PrivateContent' => 'More information for staff only',
        'PublicContent' => 'Information for public'
    ];

    private static $field_labels_right = [
        'StartTime' => 'Please enter in 24-hour clock',
        'Duration' => 'In Minutes - e.g. 90',
        'Title' => 'E.g. early morning tour, lunch tour, etc...',
        'NumberOfSpacesAvailable' => 'Number of people who can join this tour time'
    ];

    private static $summary_fields = [
        'Title' => 'Name',
        'StartTime.Nice' => 'Starts',
        'Duration' => 'Minutes',
        'NumberOfSpacesAvailable' => 'Spaces Available'
    ];


    #######################
    ### Casting Section
    #######################

    private static $casting = [
        'EndTime' => 'Time'
    ];


    public function EndTime()
    {
        return $this->getEndTime();
    }

    public function getEndTime()
    {
        $fakeDate = date('Y-m-d') . ' ' . $this->StartTime;
        $fakeDateTS = strtotime($fakeDate);
        $fakeDateTS = strtotime("+".$this->Duration." minute", $fakeDateTS);
        $v = date('H:i:s', $fakeDateTS);

        return DBField::create_field('Time', $v);
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
        //...
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

        $fields->dataFieldByName('PublicContent')->setRows('7');
        $fields->dataFieldByName('PrivateContent')->setRows('7');
        if($fields->fieldByName('Root.DateInfos')) {
            $fields->fieldByName('Root.DateInfos')->setTitle('Info and Rules');
        }

        return $fields;
    }
}

