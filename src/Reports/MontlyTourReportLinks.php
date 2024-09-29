<?php

namespace Sunnysideup\Bookings\Reports;

use SilverStripe\Control\Director;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DB;
use SilverStripe\Reports\Report;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\Queries\SQLSelect;
use Sunnysideup\Bookings\Tasks\MonthlyTourReport;
use Sunnysideup\Bookings\Tasks\YearlyTourReport as YearlyTourReportTask;

class MontlyTourReportLinks extends Report
{
    /**
     * {@inheritDoc}
     */
    public function title()
    {
        return _t(__CLASS__ . '.Title', 'Montly Tour Reports');
    }

    /**
     * {@inheritDoc}
     */
    public function sourceRecords($params = [], $sort = null, $limit = null): ArrayList
    {
        $yearmonths = [];

        $sql = 'SELECT DATE_FORMAT("Date",\'%Y-%m\') as YearMonth from Booking GROUP BY DATE_FORMAT("Date",\'%Y-%m\');';
        $rows = DB::query($sql);

        foreach ($rows as $row) {
            $yearmonths[] = $row['YearMonth'];
        }
        rsort($yearmonths);

        $output = ArrayList::create();

        if (!empty($yearmonths)) {
            foreach ($yearmonths as $yearmonth) {
                $output->push(ArrayData::create([
                    'Year' => $yearmonth,
                    'SendMail' => rand(1, 999),
                ]));
            }
        }

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function columns()
    {
        $baseURL =
            MonthlyTourReport::singleton()->Link() .
            '?action=%s&yearmonth=%s';

        return [
            'Year' => [
                'title' => _t(__CLASS__ . '.Year', 'Year'),
                'formatting' => function ($value, $item) use ($baseURL) {
                    return sprintf(
                        '<a class="grid-field__link" href="%s" target="_blank">View %s report</a>',
                        sprintf($baseURL, 'view', $item->Year),
                        $item->Year
                    );
                },
            ],
            'SendMail' => [
                'title' => _t(__CLASS__ . '.SendMail', 'Send mail'),
                'formatting' => function ($value, $item) use ($baseURL) {
                    return sprintf(
                        '<a class="grid-field__link" href="%s" target="_blank">%s</a>',
                        sprintf($baseURL, 'email', $item->Year),
                        'Send'
                    );
                },
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getReportField()
    {
        $field = parent::getReportField();

        $field->getConfig()->removeComponentsByType([
            GridFieldExportButton::class,
            GridFieldPrintButton::class,
            GridFieldButtonRow::class,
        ]);

        return $field;
    }
}
