<?php



class Tour extends TourBaseClass
{


    /**
     * @param  int $dateTS
     * @param  int $numberOfPeople
     *
     * @return ArrayList
     */
    public static function future_tours()
    {
        $mysqlDate = date('Y-m-d');

        return Tour::get()->filter(['Date:GreaterThan' => $mysqlDate]);
    }

    #######################
    ### Names Section
    #######################

    private static $singular_name = 'Tour';

    public function i18n_singular_name()
    {
        return _t('Tour.SINGULAR_NAME', 'Tour');
    }

    private static $plural_name = 'Tours';

    public function i18n_plural_name()
    {
        return _t('Tour.PLURAL_NAME', 'Tours');
    }


    #######################
    ### Model Section
    #######################

    private static $db = [
        'IsClosed' => 'Boolean',
        'Date' => 'Date',
        'StartTime' => 'Time',
        'Duration' => 'Int',
        'TotalSpacesAtStart' => 'Int',
        'PublicContent' => 'HTMLText',
        'PrivateContent' => 'HTMLText',
        'PublicContentForTour' => 'HTMLText',
        'PrivateContentForTour' => 'HTMLText',
        'GoogleEventID' => 'Varchar(255)',
    ];

    private static $has_one = [
        'TourTime' => 'TimesForTour',
        'DateInfo' => 'DateInfo'
    ];

    private static $has_many = [
        'Bookings' => 'Booking',
        'Waitlisters' => 'Waitlister'
    ];


    #######################
    ### Further DB Field Details
    #######################

    private static $indexes = [
        'IsClosed' => true,
        'Date' => true
    ];

    private static $default_sort = [
        'Date' => 'ASC',
        'StartTime' => 'ASC',
        'ID' => 'ASC'
    ];

    private static $required_fields = [
        'Date',
        'TourTimeID'
    ];

    private static $searchable_fields = [
        'TotalSpacesAtStart' => 'ExactMatchFilter',
        'Duration' => 'ExactMatchFilter',
        'IsClosed' => 'ExactMatchFilter',
        'Created' => [
            'field' => 'TextField',
            'filter' => 'TourDate_Filter',
            'title' => 'Tour Date (e.g Today, 1 jan 2020, or next Thursday)'
        ],
        'StartTime' => 'ExactMatchFilter',
    ];



    #######################
    ### Field Names and Presentation Section
    #######################

    private static $field_labels = [
        'IsClosed' => 'Closed',
        'Date' => 'Date',
        'StartTime' => 'Start Time',
        'Duration' => 'Minutes',
        'TotalSpacesAtStart' => 'Total Spaces',
        'PublicContent' => 'Public Info',
        'PrivateContent' => 'Staff Only Info',
        'PublicContentForTour' => 'Public Info',
        'PrivateContentForTour' => 'Staff Only Info',
        'DateInfo' => 'Based On - RULE',
        'TourTime' => 'Type of Tour',
        'Tours' => 'Resulting Tours'
    ];

    private static $field_labels_right = [
        'IsClosed' => 'Manually close this tour',
        'Date' => 'Enter as 30-11-2011',
        'StartTime' => 'Enter as 13:13',
        'Duration' => 'Duration of the tour',
        'TotalSpacesAtStart' => 'Total Spaces available for this tour before bookings',
        'PublicContent' => 'Information from Date Info and Tour Time',
        'PrivateContent' => 'Information from Date Info and Tour Time',
        'PublicContentForTour' => 'Tour specific info',
        'PrivateContentForTour' => 'Tour specific info'
    ];

    private static $summary_fields = [
        'IsClosed.Nice' => 'Closed',
        'Date.Nice' => 'Date',
        'StartTime.Nice' => 'Start Time',
        'Duration' => 'Minutes',
        'TotalSpacesAtStart' => 'Total Spots Available',
        'NumberOfPlacesAvailable' => 'Spots Left',
        'NumberOfChildren' => 'Kids Attending',
        'NumberOfGroups' => 'Groups',
        'IsFull.Nice' => 'Full'
    ];

    private static $readonly_fields = [
        'Date',
        'StartTime',
        'Duration',
        'TotalSpacesAtStart',
        'TourTimeID',
        'PublicContent',
        'PrivateContent'
    ];

    #######################
    ### Casting Section
    #######################

    private static $casting = [
        'Title' => 'Varchar',
        'EndTime' => 'Time',
        'NumberOfPlacesBooked' => 'Int',
        'NumberOfPlacesAvailable' => 'Int',
        'IsFull' => 'Boolean',
        'NumberOfAdults' => 'Int',
        'NumberOfChildren' => 'Int',
        'NumberOfGroups' => 'Int',
        'CalculatedPublicContent' => 'HTMLText',
        'CalculatedPrivateContent' => 'HTMLText'
    ];

    public function Title()
    {
        return $this->getTitle();
    }

    public function TourTimeAndDate()
    {
        return $this->getTourTimeAndDate();
    }

    public function FullCalendarTitle()
    {
        return $this->collateTitleData('Long');
    }

    public function AbrvCalendarTitle()
    {
        return $this->collateTitleData('Short');
    }

    protected function collateTitleData($type)
    {
        $a = [];
        if ($this->NumberOfPlacesBooked()->RAW() == 0) {
            $a[] = [
                'Short' => '0/'.$this->TotalSpacesAtStart,
                'Long' => 'No bookings ('.$this->TotalSpacesAtStart.' spots left)',
            ];
        } else {
            if ($this->getIsFull()->RAW()) {
                $a[] = [
                    'Short' => 'Full',
                    'Long' => 'Full: ' . $this->NumberOfPlacesBooked()
                ];
            } else {
                $a[] = [
                    'Short' => 'SL: ' . $this->NumberOfPlacesAvailable() . '/'. $this->TotalSpacesAtStart,
                    'Long' => 'Spots Left: ' . $this->NumberOfPlacesAvailable() . '/'. $this->TotalSpacesAtStart
                ];
            }
            $a[] = [
                'Short' => 'G: ' . $this->NumberOfGroups(),
                'Long' => 'Groups: ' . $this->NumberOfGroups()
            ];
            $a[] = [
                'Short' => 'A: ' . $this->NumberOfAdults(),
                'Long' => 'Adults: ' . $this->NumberOfAdults()
            ];
            $a[] = [
                'Short' => 'C: ' . $this->NumberOfChildren(),
                'Long' => 'Children: ' . $this->NumberOfChildren()
            ];
        }
        $newData = [];
        foreach ($a as $entry) {
            $newData[] = $entry[$type];
        }
        return implode(
            ', ',
            $newData
        );
    }

    public function getTitle()
    {
        $v = 'Tour on ' . date('D, jS M Y', strtotime($this->Date)) . ' at '. $this->StartTimeObj()->Nice() .' until '. $this->getEndTime()->Nice();

        return DBField::create_field('Varchar', $v);
    }

    public function getTourTimeAndDate()
    {
        $v = date('l, jS F Y', strtotime($this->Date)) .' at '.$this->StartTimeObj->Nice();

        return DBField::create_field('Varchar', $v);
    }

    public function CalculatedPublicContent()
    {
        return $this->getCalculatedPublicContent();
    }

    public function getCalculatedPublicContent()
    {
        $v = $this->PublicContent.$this->PublicContentForTour;
        if($v){
            $v .= '<br>';
        }
        if ($this->IsFull()->value) {
            $settings = TourBookingSettings::inst();
            $v .= $settings->TourFullMessage;
        } else {
            $singularPlural = $this->NumberOfPlacesAvailable()->value > 1 ? ' spaces' : ' space';
            $v .= $this->NumberOfPlacesAvailable()->value . $singularPlural .' left';
        }
        return DBField::create_field('HTMLText', $v);
    }

    public function CalculatedPrivateContent()
    {
        return $this->getCalculatedPrivateContent();
    }

    public function getCalculatedPrivateContent()
    {
        $v = $this->PrivateContent.$this->PrivateContentForTour;

        return DBField::create_field('HTMLText', $v);
    }

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

    public function StartTimeObj()
    {
        return $this->getStartTimeObj();
    }

    public function getStartTimeObj()
    {
        $fakeDate = date('Y-m-d') . ' ' . $this->StartTime;
        $fakeDateTS = strtotime($fakeDate);
        $v = date('H:i:s', $fakeDateTS);

        return DBField::create_field('Time', $v);
    }

    public function NumberOfPlacesBooked()
    {
        return $this->getNumberOfPlacesBooked();
    }
    public function getNumberOfPlacesBooked()
    {
        $v = 0;
        foreach ($this->ValidBookings() as $booking) {
            $v += $booking->TotalNumberOfGuests;
        }

        return DBField::create_field('Int', $v);
    }

    public function NumberOfPlacesAvailable()
    {
        return $this->getNumberOfPlacesAvailable();
    }
    public function getNumberOfPlacesAvailable()
    {
        if ($this->IsClosed) {
            $v = 0;
        } else {
            $v = $this->TotalSpacesAtStart - $this->getNumberOfPlacesBooked()->RAW();
        }

        return DBField::create_field('Int', $v);
    }

    public function IsFull()
    {
        return $this->getIsFull();
    }
    public function getIsFull()
    {
        if ($this->IsClosed) {
            $v = true;
        } else {
            $v = ($this->getNumberOfPlacesAvailable()->RAW() < 1 ? true : false);
        }

        return DBField::create_field('Boolean', $v);
    }

    public function NumberOfAdults()
    {
        return $this->getNumberOfAdults();
    }
    public function getNumberOfAdults()
    {
        $v = 0;
        foreach ($this->ValidBookings() as $booking) {
            $v += intval($booking->getNumberOfAdults()->Raw());
        }

        return DBField::create_field('Boolean', $v);
    }

    public function NumberOfChildren()
    {
        return $this->getNumberOfChildren();
    }
    public function getNumberOfChildren()
    {
        $v = 0;
        foreach ($this->ValidBookings() as $booking) {
            $v += intval($booking->NumberOfChildren);
        }

        return DBField::create_field('Int', $v);
    }

    public function NumberOfGroups()
    {
        return $this->getNumberOfGroups();
    }
    public function getNumberOfGroups()
    {
        $v = $this->ValidBookings()->count();

        return DBField::create_field('Int', $v);
    }



    #######################
    ### can Section
    #######################



    public function canCreate($member = null)
    {
        return $this->CurrentUserIsTourManager($member);
    }


    public function canDelete($member = null)
    {
        if ($this->getNumberOfGroups()->RAW() > 0) {
            return false;
        }
        return $this->CurrentUserIsTourManager($member);
    }


    #######################
    ### write Section
    #######################


    private $calendarDebug = false;

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->TotalSpacesAtStart && $this->TourTimeID) {

            if($this->TourTime()->exists()) {
                $this->StartTime = $this->TourTime()->StartTime;
                $this->Duration = $this->TourTime()->Duration;
                $this->TotalSpacesAtStart = $this->TourTime()->NumberOfSpacesAvailable;
                $this->PublicContent = implode('<br />', [$this->TourTime()->PublicContent]);
                $this->PrivateContent = implode('<br />', [$this->TourTime()->PrivateContent]);
            }
        }

        if ($this->Date && $this->isChanged("Date")) {
            foreach ($this->Bookings() as $booking) {
                $booking->Date = $this->Date;
                $booking->write();
            }
        }




        if (class_exists('GoogleCalendarInterface') && (Director::isLive() || $this->calendarDebug)) {
            $settings = TourBookingSettings::inst();
            $calendar = new GoogleCalendarInterface();
            if (! empty($calendar->config())) {
                $timeZone = Config::inst()->get('GoogleCalendarInterface', 'time_zone');

                $decription ='';

                if ($this->NumberOfGroups()->Value) {
                    $decription .= $this->NumberOfPlacesBooked()->Value . ' people attending' . '; ';
                    $decription .= $this->NumberOfGroups()->Value . ' groups; ';
                    $decription .= $this->NumberOfAdults()->Value . ' adults; ';
                    $decription .= $this->NumberOfChildren()->Value . ' children; ';
                }

                if ($decription) {
                    $decription = 'Tour: ' . $decription;
                } else {
                    $decription = 'Tour: No Current Bookings';
                }


                $eventAttributes = [
                    'summary' => json_encode($decription),
                    'description' => $this->Title()->Value,
                    'start' =>  [
                                    'dateTime' => $this->Date.'T'.$this->StartTime,
                                    'timeZone' => $timeZone
                                ],
                    'end' =>    [
                                    'dateTime' => $this->Date.'T'.$this->EndTime(),
                                    'timeZone' => $timeZone
                                ],
                ];

                if ($this->GoogleEventID && $calendar->getCalendarEvent($this->GoogleEventID)) {
                    $googleEvent = $calendar->updateCalendarEvent($eventAttributes, $this->GoogleEventID);
                } else {
                    $googleEvent = $calendar->addCalendarEvent($eventAttributes);
                }
                if (isset($googleEvent->id)) {
                    $this->GoogleEventID = $googleEvent->id;
                }
            }
        }


        $tourDate = new DateTime($this->Date);
        $now = new DateTime(date('Y-m-d'));
        //we only want to do this if the the tour is not in the past
        if ($this->Waitlisters()->count() && $tourDate->getTimestamp() >= $now->getTimestamp()) {
            $settings = TourBookingSettings::inst();
            $spacesAvailableEmail = $settings->TourSpacesAvailableEmail();
            $mailOut = Injector::inst()->get('EmailReminder_DailyMailOut');

            $placesAvailable = $this->NumberOfPlacesAvailable()->value;
            $waitlisters = $this->Waitlisters()->filter(['TotalNumberOfGuests:LessThanOrEqual' => $placesAvailable]);

            foreach ($waitlisters as $waitlister) {
                //send an email to the waitlister containing link to booking form
                $mailOut->runOne($spacesAvailableEmail, $waitlister);
            }
        }
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        if (class_exists('GoogleCalendarInterface')) {
            $settings = TourBookingSettings::inst();
            $calendar = new GoogleCalendarInterface();
            if (! empty($calendar->config()) && $this->GoogleEventID) {
                $googleEvent = $calendar->deleteCalendarEvent($this->GoogleEventID);
            }
        }
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

        if(!$this->ID){
            $dbFields = Config::inst()->get('Tour', 'db');;
            foreach($dbFields as $dbFieldName => $dbFieldType){
                $fields->removeByName($dbFieldName);
            }
            $fields->removeByName('Date');
            $fields->removeByName('TourTimeID');
            $fields->removeByName('DateInfoID');

            $times = array('' => '-- please select one --') + TimesForTour::get()->map()->toArray();

            $fields->addFieldsToTab(
                'Root.Main',
                [
                    $dateField = DateField::create('Date', 'Date'),
                    $tourTimeField = DropdownField::create('TourTimeID','Type of Tour', $times),
                ]
            );
            $dateField->setConfig('showcalendar', true);
            $timesForTour = Injector::inst()->get('TimesForTour');
            $tourTimeField->setRightTitle('<a href="'.$timesForTour->CMSAddLink(). '" target="_blank">Create a new tour time</a>.');
        }
        else {
            if ($fields->dataFieldByName('PublicContentForTour') instanceof HTMLEditorField) {
                $fields->dataFieldByName('PublicContentForTour')->setRows('7');
            }
            if ($fields->dataFieldByName('PrivateContentForTour') instanceof HTMLEditorField) {
                $fields->dataFieldByName('PrivateContentForTour')->setRows('7');
            }
            $fields->replaceField(
                'GoogleEventID',
                $fields->dataFieldByName('GoogleEventID')->performReadonlyTransformation()
            );
        }

        return $fields;
    }

    public function ValidBookings()
    {
        return $this->Bookings()->exclude(['Cancelled' => 1]);
    }

    public function JoinLink($absolute = false)
    {
        $v = TourBookingPage_Controller::find_link('jointour') . $this->ID . '/';
        if ($absolute) {
            $v = Director::absoluteURL($v);
        }
        return $v;
    }
}