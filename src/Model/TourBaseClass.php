<?php

namespace Sunnysideup\Bookings\Model;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use Sunnysideup\Bookings\Cms\TourBookingsAdmin;
use Sunnysideup\Bookings\Cms\TourBookingsConfig;
use Sunnysideup\Bookings\Pages\TourBookingPage;
use Sunnysideup\SanitiseClassName\Sanitiser;
use Sunnysideup\YesNoAnyFilter\FixBooleanSearch;

/**
 * Class \Sunnysideup\Bookings\Model\TourBaseClass
 *
 */
class TourBaseClass extends DataObject
{
    use FixBooleanSearch;

    private static $table_name = 'TourBaseClass';

    //######################
    //## can Section
    //######################

    public function CurrentUserIsTourManager($member)
    {
        return (bool) Permission::check('CMS_ACCESS_TOUR_ADMIN', 'any', $member);
    }

    public function canCreate($member = null, $context = [])
    {
        if ($this->CurrentMemberIsOwner()) {
            return true;
        }

        return $this->CurrentUserIsTourManager($member);
    }

    public function canView($member = null, $context = [])
    {
        if ($this->CurrentMemberIsOwner()) {
            return true;
        }

        return $this->CurrentUserIsTourManager($member);
    }

    public function canEdit($member = null, $context = [])
    {
        if ($this->CurrentMemberIsOwner()) {
            return true;
        }

        return $this->CurrentUserIsTourManager($member);
    }

    public function canDelete($member = null, $context = [])
    {
        if ($this->CurrentMemberIsOwner()) {
            return true;
        }

        return $this->CurrentUserIsTourManager($member);
    }

    //######################
    //## write Section
    //######################

    public function validate()
    {
        $result = parent::validate();
        $fieldLabels = $this->FieldLabels();
        $indexes = $this->Config()->get('indexes');
        $requiredFields = $this->Config()->get('required_fields');
        if (is_array($requiredFields)) {
            foreach ($this->Config()->get('required_fields') as $field) {
                $value = $this->{$field};
                if (! $value) {
                    $fieldWithoutID = $field;
                    if ('ID' === substr((string) $fieldWithoutID, -2)) {
                        $fieldWithoutID = substr((string) $fieldWithoutID, 0, -2);
                    }
                    $myName = isset($fieldLabels[$fieldWithoutID]) ? $fieldLabels[$fieldWithoutID] : $fieldWithoutID;
                    $result->addError(
                        _t(
                            'Booking.' . $field . '_REQUIRED',
                            $myName . ' is required'
                        ),
                        'REQUIRED_Booking_' . $field
                    );
                }
                if (isset($indexes[$field], $indexes[$field]['type']) && 'unique' === $indexes[$field]['type']) {
                    $id = (empty($this->ID) ? 0 : $this->ID);
                    $count = self::get()
                        ->filter([$field => $value, 'ClassName' => $this->ClassName])
                        ->exclude(['ID' => $id])
                        ->count();
                    if ($count > 0) {
                        $myName = $fieldLabels['$field'];
                        $result->addError(
                            _t(
                                $this->ClassName . '.' . $field . '_UNIQUE',
                                $myName . ' needs to be unique'
                            ),
                            'UNIQUE_' . $this->ClassName . '_' . $field
                        );
                    }
                }
            }
        }

        return $result;
    }

    //######################
    //## Import / Export Section
    //######################

    //######################
    //## CMS Edit Section
    //######################

    public function CMSEditLink()
    {
        $controller = $this->getModelAdminController();

        return $controller->Link() . '/' . Sanitiser::sanitise($this->ClassName) . '/EditForm/field/' . Sanitiser::sanitise($this->ClassName) . '/item/' . $this->ID . '/edit';
    }

    public function CMSAddLink()
    {
        $controller = $this->getModelAdminController();

        return $controller->Link() . Sanitiser::sanitise($this->ClassName) . '/EditForm/field/' . Sanitiser::sanitise($this->ClassName) . '/item/new';
    }

    public function CMSListLink()
    {
        $controller = $this->getModelAdminController();

        return $controller->getLinkForModelClass($this->ClassName);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $readonlyfields = Config::inst()->get($this->ClassName, 'readonly_fields');
        if (is_array($readonlyfields)) {
            foreach ($readonlyfields as $replaceField) {
                $fields->replaceField(
                    $replaceField,
                    $fields->dataFieldByName($replaceField)->performReadonlyTransformation()
                );
            }
        }

        $castedValues = Config::inst()->get($this->ClassName, 'casting');
        $fieldLabels = $this->Config()->get('field_labels_right');
        if (is_array($castedValues)) {
            foreach (array_keys($castedValues) as $fieldName) {
                $avoid = [
                    'ID',
                    'LastEdited',
                    'Created',
                    'CSSClasses',
                    'ClassName',
                    'Title',
                ];
                if (! in_array($fieldName, $avoid, true)) {
                    $method = 'get' . $fieldName;
                    $fieldNameNice = $fieldName;
                    if (isset($fieldLabels[$fieldName])) {
                        $fieldNameNice = $fieldLabels[$fieldName];
                    }
                    $value = $this->{$method}();
                    if (is_object($value) && $value->hasMethod('Nice')) {
                        $value = $value->Nice();
                    }
                    $fields->addFieldToTab(
                        'Root.CalculatedValues',
                        ReadonlyField::create(
                            $fieldName,
                            $fieldNameNice,
                            $value
                        )
                    );
                }
            }
        }

        $rightFieldDescriptions = $this->Config()->get('field_labels_right');
        if ($rightFieldDescriptions) {
            foreach ($rightFieldDescriptions as $field => $desc) {
                $formField = $fields->DataFieldByName($field);
                if (null === $formField) {
                    $formField = $fields->DataFieldByName($field . 'ID');
                }
                if (null !== $formField) {
                    $formField->setDescription($desc);
                }
            }
        }

        return $fields;
    }

    public function LinkToTourPage()
    {
        return TourBookingPage::find_link();
    }

    protected function isOperationalClass(): bool
    {
        //we exclude tours here, because we only show the future tours in the operational side ...
        return
            $this instanceof Tour
            ||
            $this instanceof Booking
            ||
            $this instanceof Waitlister;
    }

    protected function getModelAdminController()
    {
        if ($this->isOperationalClass()) {
            return Injector::inst()->get(TourBookingsAdmin::class);
        }

        return Injector::inst()->get(TourBookingsConfig::class);
    }

    protected function CurrentMemberIsOwner()
    {
        return false;
    }

    protected function addUsefulLinkToFields(FieldList $fields, string $title, string $link, ?string $explanation = '')
    {
        $name = preg_replace('#[^A-Za-z0-9 ]#', '', $title);
        $fields->addFieldsToTab(
            'Root.UsefulLinks',
            [
                LiteralField::create(
                    $name . '_UseFulLink',
                    '<h2>› <a href="' . $link . '">' . $title . '</a></h2><p>' . $explanation . '</p>'
                ),
            ]
        );
    }
}
