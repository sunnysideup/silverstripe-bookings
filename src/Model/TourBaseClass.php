<?php

namespace Sunnysideup\Bookings\Model;

use DataObject;
use Permission;
use Config;
use ReadonlyField;
use LiteralField;
use TourBookingPage_Controller;




class TourBaseClass extends DataObject
{


    #######################
    ### can Section
    #######################

    public function CurrentUserIsTourManager($member)
    {
        if (Permission::check('CMS_ACCESS_TOUR_ADMIN', 'any', $member)) {
            return true;
        }
        return false;
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

    protected function CurrentMemberIsOwner()
    {
        return false;
    }

    #######################
    ### write Section
    #######################




    public function validate()
    {
        $result = parent::validate();
        $fieldLabels = $this->FieldLabels();
        $indexes = $this->Config()->get('indexes');
        $requiredFields = $this->Config()->get('required_fields');
        if (is_array($requiredFields)) {
            foreach ($this->Config()->get('required_fields') as $field) {
                $value = $this->$field;
                if (! $value) {
                    $fieldWithoutID = $field;
                    if (substr($fieldWithoutID, -2) === 'ID') {
                        $fieldWithoutID = substr($fieldWithoutID, 0, -2);
                    }
                    $myName = isset($fieldLabels[$fieldWithoutID]) ? $fieldLabels[$fieldWithoutID] : $fieldWithoutID;
                    $result->addError(
                        _t(
                            'Booking.'.$field.'_REQUIRED',
                            $myName.' is required'
                        ),
                        'REQUIRED_Booking_'.$field
                    );
                }
                if (isset($indexes[$field]) && isset($indexes[$field]['type']) && $indexes[$field]['type'] === 'unique') {
                    $id = (empty($this->ID) ? 0 : $this->ID);
                    $count = self::get()

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                        ->filter(array($field => $value, 'ClassName' => $this->ClassName))
                        ->exclude(array('ID' => $id))
                        ->count();
                    if ($count > 0) {
                        $myName = $fieldLabels['$field'];
                        $result->addError(
                            _t(

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                                $this->ClassName.'.'.$field.'_UNIQUE',
                                $myName.' needs to be unique'
                            ),

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                            'UNIQUE_'.$this->ClassName.'_'.$field
                        );
                    }
                }
            }
        }

        return $result;
    }


    #######################
    ### Import / Export Section
    #######################



    #######################
    ### CMS Edit Section
    #######################

    public function CMSEditLink()
    {
        $controller = singleton("TourBookingsAdmin");


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return $controller->Link().$this->ClassName."/EditForm/field/".$this->ClassName."/item/".$this->ID."/edit";
    }

    public function CMSAddLink()
    {
        $controller = singleton("TourBookingsAdmin");


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return $controller->Link().$this->ClassName."/EditForm/field/".$this->ClassName."/item/new";
    }

    public function CMSListLink()
    {
        $controller = singleton("TourBookingsAdmin");


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        return $controller->Link().$this->ClassName;
    }


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();


/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $readonlyfields = Config::inst()->get($this->ClassName, 'readonly_fields');
        if (is_array($readonlyfields)) {
            foreach ($readonlyfields as $replaceField) {
                $fields->replaceField(
                    $replaceField,
                    $fields->dataFieldByName($replaceField)->performReadonlyTransformation()
                );
            }
        }



/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: $this->ClassName (case sensitive)
  * NEW: $this->ClassName (COMPLEX)
  * EXP: Check if the class name can still be used as such
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
        $castedValues = Config::inst()->get($this->ClassName, 'casting');
        $fieldLabels = $this->Config()->get('field_labels_right');
        if (is_array($castedValues)) {
            foreach ($castedValues as $fieldName => $fieldType) {
                $avoid = [
                    'ID',
                    'LastEdited',
                    'Created',
                    'CSSClasses',
                    'ClassName',
                    'Title'
                ];
                if (! in_array($fieldName, $avoid)) {
                    $method = 'get'.$fieldName;
                    $fieldNameNice = $fieldName;
                    if (isset($fieldLabels[$fieldName])) {
                        $fieldNameNice = $fieldLabels[$fieldName];
                    }
                    $value = $this->$method();
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
        if($rightFieldDescriptions){
            foreach ($rightFieldDescriptions as $field => $desc) {
                $formField = $fields->DataFieldByName($field);
                if (! $formField) {
                    $formField = $fields->DataFieldByName($field.'ID');
                }
                if ($formField) {
                    $formField->setDescription($desc);
                }
            }
        }

        return $fields;
    }


    protected function AddUsefulLinkToFields($fields, $title, $link, $explanation = '')
    {
        $name = preg_replace("/[^A-Za-z0-9 ]/", '', $title);
        $fields->addFieldsToTab(
            'Root.UsefulLinks',
            [
                LiteralField::create($name.'_UseFulLink', '<h2>› <a href="'.$link.'">'.$title.'</a></h2><p>'.$explanation.'</p>'),
            ]
        );
    }

    public function LinkToTourPage()
    {
        return TourBookingPage_Controller::find_link();
    }
}

