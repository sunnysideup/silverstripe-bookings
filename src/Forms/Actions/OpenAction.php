<?php

namespace Sunnysideup\Bookings\Forms\Actions;

use Colymba\BulkManager\BulkAction\Handler;
use Colymba\BulkTools\HTTPBulkToolsResponse;
use Exception;
use SilverStripe\Control\HTTPRequest;

/**
 * Bulk action handler for recursive archiving records.
 *
 * @author colymba
 */
class OpenAction extends Handler
{
    /**
     * Front-end label for this handler's action.
     *
     * @var string
     */
    protected $label = 'Open';

    /**
     * Front-end icon path for this handler's action.
     *
     * @var string
     */
    protected $icon = '';

    /**
     * Extra classes to add to the bulk action button for this handler
     * Can also be used to set the button font-icon e.g. font-icon-trash.
     *
     * @var string
     */
    protected $buttonClasses = 'font-icon-eye';

    /**
     * Whether this handler should be called via an XHR from the front-end.
     *
     * @var bool
     */
    protected $xhr = true;

    /**
     * Set to true is this handler will destroy any data.
     * A warning and confirmation will be shown on the front-end.
     *
     * @var bool
     */
    protected $destructive = true;

    /**
     * URL segment used to call this handler
     * If none given, @BulkManager will fallback to the Unqualified class name.
     *
     * @var string
     */
    private static $url_segment = 'open';

    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = ['open'];

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = [
        '' => 'open',
    ];

    /**
     * Return i18n localized front-end label.
     *
     * @return array
     */
    public function getI18nLabel()
    {
        return 'Open';
    }

    /**
     * Archive the selected records passed from the archive bulk action.
     *
     * @return HTTPBulkToolsResponse
     */
    public function open(HTTPRequest $request)
    {
        $records = $this->getRecords();
        $response = new HTTPBulkToolsResponse(true, $this->gridField);

        try {
            foreach ($records as $record) {
                $record->IsClosed = false;
                $outcome = (bool) $record->write();
                if ($outcome) {
                    $response->addSuccessRecord($record);
                } else {
                    $response->addFailedRecord($record, 'Could not open Tour: ' . $record->getTitle());
                }
            }

            $doneCount = count($response->getSuccessRecords());
            $failCount = count($response->getFailedRecords());
            $message = sprintf(
                'Opened %1$d of %2$d records.',
                $doneCount,
                $doneCount + $failCount
            );
            $response->setMessage($message);
        } catch (Exception $exception) {
            $response->setStatusCode(500);
            $response->setMessage($exception->getMessage());
        }

        return $response;
    }
}
