<?php

namespace Sunnysideup\Bookings\Tasks;

use DateTime;



use Geoip;
use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\ArrayList;
use Sunnysideup\Bookings\Model\Tour;
use Sunnysideup\Bookings\Model\TourBookingSettings;

/**
 * @package cms
 * @subpackage assets
 */
class MonthlyTourReport extends BuildTask
{
    protected $title = 'Monthly Tour Report';

    protected $description = 'Sends and email at the beginning of each month containing details of Tour data for the previous month';

    public function Link()
    {
        return '/dev/tasks/' . static::class;
    }

    public function run($request)
    {
        ini_set('max_execution_time', 300);
        $settings = TourBookingSettings::inst();
        $toEmail = $settings->Config()->monthly_tour_report_email_to;
        $nationalities = [];
        $citiesAndTowns = [];
        $totalNumberOfPlacesBooked = 0;
        $totalNumberOfGroups = 0;
        $refferalData = [];
        $monthStart = new DateTime('first day of last month');
        $monthEnd = new DateTime('last day of last month');
        $tours = Tour::get()
            ->filter(
                [

                    /**
                     * ### @@@@ START REPLACEMENT @@@@ ###
                     * WHY: automated upgrade
                     * OLD: >format('Y-m-d') (case sensitive)
                     * NEW: ->format('Y-MM-d') (COMPLEX)
                     * EXP: check usage of new date/time system https://www.php.net/manual/en/datetime.format.php vs http://userguide.icu-project.org/formatparse/datetime
                     * ### @@@@ STOP REPLACEMENT @@@@ ###
                     */

                    /**
                     * ### @@@@ START REPLACEMENT @@@@ ###
                     * WHY: automated upgrade
                     * OLD: ->format( (case sensitive)
                     * NEW: ->format( (COMPLEX)
                     * EXP: If this is a PHP Date format call then this needs to be changed to new Date formatting system. (see http://userguide.icu-project.org/formatparse/datetime)
                     * ### @@@@ STOP REPLACEMENT @@@@ ###
                     */
                    'Date:GreaterThanOrEqual' => $monthStart-- > format('Y-MM-d'),

                    /**
                     * ### @@@@ START REPLACEMENT @@@@ ###
                     * WHY: automated upgrade
                     * OLD: >format('Y-m-d') (case sensitive)
                     * NEW: ->format('Y-MM-d') (COMPLEX)
                     * EXP: check usage of new date/time system https://www.php.net/manual/en/datetime.format.php vs http://userguide.icu-project.org/formatparse/datetime
                     * ### @@@@ STOP REPLACEMENT @@@@ ###
                     */

                    /**
                     * ### @@@@ START REPLACEMENT @@@@ ###
                     * WHY: automated upgrade
                     * OLD: ->format( (case sensitive)
                     * NEW: ->format( (COMPLEX)
                     * EXP: If this is a PHP Date format call then this needs to be changed to new Date formatting system. (see http://userguide.icu-project.org/formatparse/datetime)
                     * ### @@@@ STOP REPLACEMENT @@@@ ###
                     */
                    'Date:LessThanOrEqual' => $monthEnd-- > format('Y-MM-d'),
                ]
            );
        foreach ($tours as $tour) {
            $totalNumberOfPlacesBooked += $tour->NumberOfPlacesBooked()->value;
            $totalNumberOfGroups += $tour->NumberOfGroups()->value;
            foreach ($tour->Bookings() as $booking) {
                $countryCode = strtoupper($booking->CountryOfOrigin);
                if (isset($nationalities[$countryCode])) {
                    ++$nationalities[$countryCode];
                } else {
                    //first occurance
                    $nationalities[$countryCode] = 1;
                }
                $cityTown = ucwords(strtolower(trim($booking->CityTown)));
                if ($cityTown) {
                    if (isset($citiesAndTowns[$cityTown])) {
                        ++$citiesAndTowns[$cityTown];
                    } else {
                        //first occurance
                        $citiesAndTowns[$cityTown] = 1;
                    }
                }

                foreach ($booking->ReferralOptions() as $refferalOption) {
                    if (isset($refferalData[$refferalOption->SortOrder])) {
                        ++$refferalData[$refferalOption->SortOrder]['Count'];
                    } else {
                        //first occurance
                        $refferalData[$refferalOption->SortOrder]['Count'] = 1;
                        $refferalData[$refferalOption->SortOrder]['Title'] = $refferalOption->Title;
                    }

                    if ($refferalOption->IsOther) {
                        $refferalData[$refferalOption->SortOrder]['OtherInfo'][$booking->ID] = $booking->ReferralText;
                    }
                }
            }
        }

        $emailData = [
            'TotalBookings' => $totalNumberOfPlacesBooked,
            'TotalGroups' => $totalNumberOfGroups,
            'Nationalities' => $this->convertNationalityDataToArrayList($nationalities),
            'CitiesAndTowns' => $this->convertCityTownDataToArrayList($citiesAndTowns),
            'RefferalData' => $this->convertRefferalDataToArrayList($refferalData),
        ];

        $email = Email::create();
        $email->setTemplate(MonthlyTourReport::class);
        $email->populateTemplate($emailData);
        $email->setTo($toEmail);

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: ->format( (case sensitive)
         * NEW: ->format( (COMPLEX)
         * EXP: If this is a PHP Date format call then this needs to be changed to new Date formatting system. (see http://userguide.icu-project.org/formatparse/datetime)
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        $email->setSubject('Monthly Tour Report for the month of ' . $monthStart->format('F Y'));

        $result = $email->send();
        if ($result) {
            echo 'Email was succesfully sent to ' . $toEmail;
        } else {
            echo 'There was a problem sending the email, please contact your developer for assistance';
        }
    }

    public function convertNationalityDataToArrayList($array)
    {
        $list = ArrayList::create();
        arsort($array);
        foreach ($array as $countryCode => $count) {
            $countryName = Geoip::countryCode2name(strtoupper($countryCode));
            if (! $countryName) {
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

    public function convertCityTownDataToArrayList($array)
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

    public function convertRefferalDataToArrayList($options)
    {
        $list = ArrayList::create();
        asort($options);
        foreach ($options as $optionID => $optionDetails) {
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
}
