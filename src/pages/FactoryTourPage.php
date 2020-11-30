<?php

/**
 *
 */

class FactoryTourPage extends TourBookingPage
{
    /**
     * in the sitetree, what children are allowed?
     * e.g. array('Page', 'MyPage') or none
     *
     *
     * @var string | array
     */
    private static $allowed_children = 'none';


    /**
     * can this page be create at the top of the sitetree?
     * e.g. HomePage would be YES and Product would be NO
     *
     *
     * @var boolean
     */
    private static $can_be_root = true;


    /**
     * this is a key one for defining the database fields
     * e.g.
     * MyTextField => 'Varchar(100)'
     * MyTextField => 'Varchar(100)'
     * MyTextField => 'Varchar(100)'
     * @var array
     */

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * OLD: private static $db (case sensitive)
  * NEW: 
    private static $table_name = '[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]';

    private static $db (COMPLEX)
  * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    
    private static $table_name = 'FactoryTourPage';

    private static $db = array(
        'IntroLeft' => 'HTMLText',
        'IntroRight' => 'HTMLText',
        'TACertLocation' => 'enum("left, right")',
        'MaxBookingsMessage' => 'HTMLText',
        'GoogleMapsEmbedLink' => 'Varchar(255)',
        'TourBookingLink' => 'Varchar(100)',
        'TourTimes' => 'Varchar(100)',
        'TourPhone' => 'PhoneField(30)',
        'TourDuration' => 'Varchar(100)',
        'TourCost' => 'Varchar(100)',
        'TourStreetAddress' => 'Varchar(100)',
        'VimeoLink' => 'Varchar(100)',
        'MapDownloadHeader' => 'Varchar(100)',
        'VideoHeader' => 'Varchar(100)',
        'TourDetailsHeader' => 'Varchar(100)',
        'BookNowLinkCaption' => 'Varchar(100)',
        'TourCity' => 'Varchar(100)',
        'TourCountry' => 'Varchar(100)',
        'TourLocation' => 'Varchar(100)',
        'ShowSpecialInfo' => 'Boolean',
        'ShowAsPopUp' => 'Boolean',
        'ShowTourLogo' => 'Boolean',
        'SpecialNoticeHeading' => 'Varchar(100)',
        'SpecialNoticeSubHeading' => 'Varchar(100)',
        'SpecialInformation' => 'HTMLText',
    );

    /**
     * creates links to other objects
     * create a has_many or has_one on the other side
     *
     * @var array
     */
    private static $has_one = array(
        'TourTimesBannerMobile' => 'Image',
        'TourTimesBannerDesktop' => 'Image',
        'BubblesBannerMobile' => 'Image',
        'BubblesBannerDesktop' => 'Image',
        'BookYourTourImage' => 'Image',
        'InsideTheFactory' => 'Image',
        'MapDownloadImage' => 'Image',
        'MapPDF' => 'File',
        'NavigationImage' => 'Image',
        'GeoTargetedHomePageImage' => 'Image'
    );

    //private static $default_records = null;

    /**
     * should always be set to true
     * but is true by default
     * @var bool
     */
    private static $can_create = true;

    /**
     * always set to something like
     * mysite/images/treeicons/MyClassName
     * @var string
     */
    private static $icon = 'mysite/images/treeicons/FactoryTourPage';

    /**
     * short description of page type
     * @var string
     */
    private static $description = 'Factory Tour Page';


    /**
     * e.g. Page / home Page / Product Page / My Page
     * @var string
     */
    //private static $singular_name;
    private static $singular_name = 'Factory Tour Page';
    public function i18n_singular_name()
    {
        return self::$singular_name;
    }



    /**
     * e.g. Pages / home Pages / Product Pages / My Pages
     * @var string
     */
    //private static $plural_name;
    private static $plural_name = 'Factory Tour Pages';
    public function i18n_plural_name()
    {
        return self::$plural_name;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->insertBefore(
            'Metadata',
            HTMLEditorField::create(
                'IntroLeft',
                'Intro - Left Column'
            )->setRows(9)
        );

        $fields->insertBefore(
            'Metadata',
            HTMLEditorField::create(
                'IntroRight',
                'Intro - Right Column'
            )->setRows(9)
        );

        $fields->insertBefore(
            'Metadata',
            HTMLEditorField::create(
                'MaxBookingsMessage',
                'Max Bookings Message'
            )->setRows(5)
        );

        $fields->addFieldsToTab(
            'Root.SpecialInfo',
            [
                CheckboxField::create('ShowSpecialInfo', 'Show'),
                CheckboxField::create('ShowAsPopUp', 'Show as PopUp'),
                CheckboxField::create('ShowTourLogo', 'Show Tour Logo (may obsure text)'),
                TextField::create('SpecialNoticeHeading', 'Heading'),
                TextField::create('SpecialNoticeSubHeading', 'Subheading'),
                HTMLEditorField::create('SpecialInformation', 'Details'),
            ]
        );
        $fields->addFieldsToTab(
            'Root.FutherInfo',
            [
                OptionsetField::create(
                    'TACertLocation',
                    'Trip Advisor Certificate Location',
                    [
                        'left' => 'To the left of the Content below',
                        'right' => 'To the right of the Content below',
                    ]
                    ),
                    $fields->dataFieldByName('Content')
            ]
        );

        $fields->addFieldsToTab(
            'Root.PageImages',
            array(
                UploadField::create('BookYourTourImage', 'Image for \'Book Your Tour\' link'),
                PerfectCMSImagesUploadField::create(
                    'TourTimesBannerDesktop',
                    'Tour Times Banner Desktop'
                ),
                PerfectCMSImagesUploadField::create(
                    'TourTimesBannerMobile',
                    'Tour Times Banner Mobile'
                ),
                PerfectCMSImagesUploadField::create(
                    'BubblesBannerDesktop',
                    'Bubbles Banner Desktop'
                ),
                PerfectCMSImagesUploadField::create(
                    'BubblesBannerMobile',
                    'Bubbles Banner Mobile'
                ),
            )
        );
        $fields->addFieldsToTab(
            'Root.TourDetails',
            array(
                TextField::create('TourBookingLink', 'Link to tour booking site'),
                HeaderField::create('SchemaMarkup', 'Schema.org Markup Fields'),
                TextField::create('TourPhone', 'Tour Phone Number')
                    ->setRightTitle('Please enter as +64 3 555 44433'),
                TextField::create('TourStreetAddress', 'Street Address'),
                TextField::create('TourCity', 'City'),
                TextField::create('TourCountry', 'Country')

            )
        );

        $fields->removeByName("Ratings");

        return $fields;
    }
}

