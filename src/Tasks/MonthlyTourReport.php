<?php

namespace Sunnysideup\Bookings\Tasks;

use DateTime;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\TourBookingSettings;
use Sunnysideup\Geoip\Geoip;

class MonthlyTourReport extends BuildTask
{
    /**
     * @var string
     */
    private static $segment = 'monthlytourreport';

    protected $title = 'Monthly Tour Report';

    protected $description = '
        Sends and email at the beginning of each month containing details of
        Tour data for the previous month.
        You can specify alternative dates using - ?yearmonth=2011-11';

    protected $startDate = '';
    protected $endDate = '';

    /**
     * @var null|HTTPRequest
     */
    protected $request = null;

    /**
     * @return string e.g. /dev/tasks/MyTask-In-Full
     */
    public function Link(): string
    {
        $link = $this->Config()->get('segment');
        if (!$link) {
            $link = str_replace('\\', '-', static::class);
        }

        return Director::absoluteUrl('dev/tasks/') . $link;
    }


    /**
     * {@inheritDoc}
     */
    public function run($request)
    {

        $this->setStartAndEndDate($request);
        $type = '';
        if ($request) {
            $type = $request->getVar('action');
        }
        if (!$type) {
            if (Director::is_cli()) {
                $type = 'email';
            } else {
                $type = 'view';
            }
        }
        switch ($type) {
            case 'view':
                $this->doView();
                break;

            case 'email':
                $this->sendMail();
                break;

            default:
                echo 'Please specify an action.';
        }
    }

    public function setStartAndEndDate(?HTTPRequest $request = null)
    {
        $monthStart = null;
        $monthEnd = null;
        if ($request) {
            $this->request = $request;
            $yearMonth = $this->request->getVar('yearmonth') ?: date('Y-m');
            if ($yearMonth) {
                $monthTs = strtotime((string) $yearMonth . '-01');
                $monthStart = new DateTime(date('Y-m-d', $monthTs));
                $monthEnd = new DateTime('last day of ' . date('F', $monthTs) . ' ' . date('Y', $monthTs));
            }
        }
        if (!$monthStart) {
            $monthStart = new DateTime('first day of last month');
        }
        if (!$monthEnd) {
            $monthEnd = new DateTime('last day of last month');
        }
        $this->startDate = $monthStart->format('Y-m-d');
        $this->endDate = $monthEnd->format('Y-m-d');
    }

    /**
     * Displays report
     */
    public function doView()
    {
        echo $this->generateReport();
    }

    /**
     * Sends report
     */
    public function sendMail()
    {
        $settings = TourBookingSettings::inst();
        $fromEmail = $settings->Config()->manager_email;
        $yearReportToEmail   = $settings->Config()->yearly_tour_report_email_to;
        $monthReportToEmail = $settings->Config()->monthly_tour_report_email_to;

        if (!$fromEmail) {
            $baseURL = Director::absoluteBaseURL();
            $baseURL = str_replace('https://', '', $baseURL);
            $baseURL = str_replace('http://', '', $baseURL);
            $baseURL = str_replace('www.', '', $baseURL);
            $baseURL = trim((string) $baseURL, '/');
            $fromEmail = 'tours@' . $baseURL;
        }

        $myToEmails = array_filter(array_unique(
            [
                $fromEmail,
                $monthReportToEmail,
                $yearReportToEmail,
                Config::inst()->get(Email::class, 'admin_email')
            ]
        ));
        foreach ($myToEmails as $myToEmail) {
            $email = Email::create();
            $email->setFrom($fromEmail);
            $email->setTo($myToEmail);
            $email->setSubject('Yearly Tour Report for ' . $this->startDate . ' until ' . $this->endDate);
            $email->setBody($this->generateReport());
            $email->send();
            /*if ($result || Director::isDev()) {
                echo 'Email was succesfully sent to ' . $myToEmail;
            } else {
                echo '
                    There was a problem sending the email,
                    please contact your developer for assistance...';
            }*/
        }
    }

    /**
     * Generates report
     */
    public function generateReport()
    {

        $nationalities = [];
        $citiesAndTowns = [];
        $totalNumberOfPlacesBooked = 0;
        $totalNumberOfGroups = 0;
        $referralData = [];

        $tours = Tour::get()
            ->filter(
                [
                    'Date:GreaterThanOrEqual' => $this->startDate,
                    'Date:LessThanOrEqual' => $this->endDate,
                ]
            );

        if (!$tours->exists()) {
            echo 'There is no tour data available for the period from
                ' . $this->startDate . ' until ' . $this->endDate . '.';
            exit;
        }

        foreach ($tours as $tour) {
            $totalNumberOfPlacesBooked += $tour->NumberOfPlacesBooked()->value;
            $totalNumberOfGroups += $tour->NumberOfGroups()->value;
            foreach ($tour->Bookings() as $booking) {
                $countryCode = strtoupper((string) $booking->CountryOfOrigin);
                if (isset($nationalities[$countryCode])) {
                    ++$nationalities[$countryCode];
                } else {
                    //first occurance
                    $nationalities[$countryCode] = 1;
                }

                $cityTown = ucwords(strtolower(trim((string) $booking->CityTown)));
                if ('' !== $cityTown) {
                    if (isset($citiesAndTowns[$cityTown])) {
                        ++$citiesAndTowns[$cityTown];
                    } else {
                        //first occurance
                        $citiesAndTowns[$cityTown] = 1;
                    }
                }

                foreach ($booking->ReferralOptions() as $referralOption) {
                    if (isset($referralData[$referralOption->SortOrder])) {
                        ++$referralData[$referralOption->SortOrder]['Count'];
                    } else {
                        //first occurance
                        $referralData[$referralOption->SortOrder]['Count'] = 1;
                        $referralData[$referralOption->SortOrder]['Title'] = $referralOption->Title;
                    }

                    if ($referralOption->IsOther) {
                        $referralData[$referralOption->SortOrder]['OtherInfo'][$booking->ID] = $booking->ReferralText;
                    }
                }
            }
        }

        $data = [
            'From' => $this->startDate,
            'Until' => $this->endDate,
            'Link' => $this->Link(),
            'TotalBookings' => $totalNumberOfPlacesBooked,
            'TotalGroups' => $totalNumberOfGroups,
            'Nationalities' => $this->convertNationalityDataToArrayList($nationalities),
            'CitiesAndTowns' => $this->convertCityTownDataToArrayList($citiesAndTowns),
            'ReferralData' => $this->convertReferralDataToArrayList($referralData),
        ];

        $template = $this->config()->get('report_template') ?: 'Sunnysideup/Bookings/Email/MonthlyTourReport';

        return ArrayData::create($data)->renderWith($template);
    }

    protected function convertNationalityDataToArrayList(array $array): ArrayList
    {
        $list = ArrayList::create();
        arsort($array);
        foreach ($array as $countryCode => $count) {
            $countryName = Geoip::countryCode2name(strtoupper((string) $countryCode));
            if (!$countryName) {
                $countryName = $countryCode;
            }
            $data = [
                'Country' => $countryName,
                'Count' => $count,
            ];
            $list->push($data);
        }

        return $list;
    }

    protected function convertCityTownDataToArrayList(array $array): ArrayList
    {
        $list = ArrayList::create();
        arsort($array);
        foreach ($array as $cityTown => $count) {
            $data = [
                'CityTown' => $cityTown,
                'Count' => $count,
            ];
            $list->push($data);
        }

        return $list;
    }

    protected function convertReferralDataToArrayList(array $options): ArrayList
    {
        $list = ArrayList::create();
        asort($options);
        foreach ($options as $optionDetails) {
            $otherResponses = ArrayList::create();
            $data = [
                'Title' => $optionDetails['Title'],
                'Count' => $optionDetails['Count'],
            ];
            if (isset($optionDetails['OtherInfo'])) {
                foreach ($optionDetails['OtherInfo'] as $otherResponse) {
                    $responseData = [
                        'Reponse' => $otherResponse,
                    ];
                    $otherResponses->push($responseData);
                }
            }
            $data['OtherResponses'] = $otherResponses;
            $list->push($data);
        }

        return $list;
    }

    // public function oldRun($request)
    // {
    //     ini_set('max_execution_time', 300);
    //     $nationalities = [];
    //     $citiesAndTowns = [];
    //     $totalNumberOfPlacesBooked = 0;
    //     $totalNumberOfGroups = 0;
    //     $referralData = [];
    //     $monthStart = new DateTime('first day of last month');
    //     $monthEnd = new DateTime('last day of last month');

    //     $tours = Tour::get()
    //         ->filter(
    //             [
    //                 'Date:GreaterThanOrEqual' => $monthStart->format('Y-m-d'),
    //                 'Date:LessThanOrEqual' => $monthEnd->format('Y-m-d'),
    //             ]
    //         );
    //     foreach ($tours as $tour) {
    //         $totalNumberOfPlacesBooked += $tour->NumberOfPlacesBooked()->value;
    //         $totalNumberOfGroups += $tour->NumberOfGroups()->value;
    //         foreach ($tour->Bookings() as $booking) {
    //             $countryCode = strtoupper($booking->CountryOfOrigin);
    //             if (isset($nationalities[$countryCode])) {
    //                 ++$nationalities[$countryCode];
    //             } else {
    //                 //first occurance
    //                 $nationalities[$countryCode] = 1;
    //             }
    //             $cityTown = ucwords(strtolower(trim($booking->CityTown)));
    //             if ('' !== $cityTown) {
    //                 if (isset($citiesAndTowns[$cityTown])) {
    //                     ++$citiesAndTowns[$cityTown];
    //                 } else {
    //                     //first occurance
    //                     $citiesAndTowns[$cityTown] = 1;
    //                 }
    //             }

    //             foreach ($booking->ReferralOptions() as $referralOption) {
    //                 if (isset($referralData[$referralOption->SortOrder])) {
    //                     ++$referralData[$referralOption->SortOrder]['Count'];
    //                 } else {
    //                     //first occurance
    //                     $referralData[$referralOption->SortOrder]['Count'] = 1;
    //                     $referralData[$referralOption->SortOrder]['Title'] = $referralOption->Title;
    //                 }

    //                 if ($referralOption->IsOther) {
    //                     $referralData[$referralOption->SortOrder]['OtherInfo'][$booking->ID] = $booking->ReferralText;
    //                 }
    //             }
    //         }
    //     }

    //     $emailData = [
    //         'TotalBookings' => $totalNumberOfPlacesBooked,
    //         'TotalGroups' => $totalNumberOfGroups,
    //         'Nationalities' => $this->convertNationalityDataToArrayList($nationalities),
    //         'CitiesAndTowns' => $this->convertCityTownDataToArrayList($citiesAndTowns),
    //         'ReferralData' => $this->convertReferralDataToArrayList($referralData),
    //     ];

    //     if ($toEmail) {
    //         $email = Email::create();
    //         $email->setHTMLTemplate('Sunnysideup/Bookings/Email/MonthlyTourReport');
    //         $email->setData($emailData);
    //         $email->setTo($toEmail);
    //         $email->setSubject('Monthly Tour Report for the month of ' . $monthStart->format('MY'));

    //         $result = $email->send();
    //         if ($result || Director::isDev()) {
    //             echo 'Email was succesfully sent to ' . $toEmail;
    //         } else {
    //             echo 'There was a problem sending the email, please contact your developer for assistance';
    //         }
    //     }
    // }

}
