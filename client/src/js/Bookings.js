var Bookings = {
  url: '',

  maxPerGroup: '',

  emailContact: '',

  currentSearchDate: '',

  totalNumberOfGuests: '',

  bookNowButton: '.book-now',

  bookingFormID: '#TourBookingForm_BookingForm',

  totalAttendeesInput: '#TourBookingForm_BookingForm_TotalNumberOfGuests',

  bookingDateInputHolder: '#TourBookingForm_BookingForm_BookingDate_Holder',

  countryDropDown: '#TourBookingForm_BookingForm_CountryOfOrigin',

  cityTownInput: '#TourBookingForm_BookingForm_CityTown',

  cityTownInputHolder: '#TourBookingForm_BookingForm_CityTown_Holder',

  childrenAttendingInput: '#TourBookingForm_BookingForm_NumberOfChildren',

  specialAssistanceCheckbox:
    '#TourBookingForm_BookingForm_SpecialAssistanceRequired',

  specialAssistanceInfoInputHolder:
    '#TourBookingForm_BookingForm_SpecialAssistanceRequiredInfo_Holder',

  specialAssistanceInfoInput:
    '#TourBookingForm_BookingForm_SpecialAssistanceRequiredInfo',

  referralOptionsList: '#TourBookingForm_BookingForm_ReferralOptions',

  referralOptionsCheckboxes:
    '#TourBookingForm_BookingForm_ReferralOptions input',

  referralOtherTextInputHolder:
    '#TourBookingForm_BookingForm_ReferralText_Holder',

  tourIDInput: '#TourBookingForm_BookingForm_TourID',

  singleTourBookingFormID: '#TourBookingForm_SingleTourBookingForm',

  waitlistFormID: '#TourWaitlistForm_WaitlistForm',

  extraToursField: '#ExtraTours',

  tourInfoID: '#date-and-tour-information',

  formSubmitInput: '',

  daysInFutureAllowed: 90,

  bookingCodeSelector: '#TourBookingForm_BookingForm_BookingCode',

  bookingCode: '',

  init: function () {
    if (
      jQuery(Bookings.bookingFormID).length ||
      jQuery(Bookings.singleTourBookingFormID).length ||
      jQuery(Bookings.waitlistFormID).length
    ) {
      if (typeof TourBookingsInPageData === 'object') {
        for (var key in TourBookingsInPageData) {
          if (TourBookingsInPageData.hasOwnProperty(key)) {
            Bookings[key] = TourBookingsInPageData[key]
          }
        }
      }
      if (jQuery(Bookings.bookingFormID).length) {
        Bookings.attendeeAndDateListener()
        Bookings.bookNowListener()
        Bookings.childrenInputListener()
        Bookings.countryDropDownListener()
        Bookings.specialAssistanceCheckboxListener()
        Bookings.referralOptionsCheckboxSetListener()
        Bookings.numericFieldListener()
        Bookings.hideOrShowFields()
        if (parseInt(jQuery(Bookings.totalAttendeesInput).val()) > 0) {
          jQuery(Bookings.totalAttendeesInput).trigger('paste')
        }
      }
      if (jQuery(Bookings.singleTourBookingFormID).length) {
        Bookings.totalAttendeesInput =
          '#TourBookingForm_SingleTourBookingForm_TotalNumberOfGuests'
        Bookings.formSubmitInput =
          '#TourBookingForm_SingleTourBookingForm_action_dojoinwaitlist'
        Bookings.specialAssistanceCheckbox =
          '#TourBookingForm_SingleTourBookingForm_SpecialAssistanceRequired'
        Bookings.specialAssistanceInfoInputHolder =
          '#TourBookingForm_SingleTourBookingForm_SpecialAssistanceRequiredInfo_Holder'
        Bookings.specialAssistanceInfoInput =
          '#TourBookingForm_SingleTourBookingForm_SpecialAssistanceRequiredInfo'
        Bookings.referralOtherTextInputHolder =
          '#TourBookingForm_SingleTourBookingForm_ReferralText_Holder'
        Bookings.countryDropDown =
          '#TourBookingForm_SingleTourBookingForm_CountryOfOrigin'
        Bookings.cityTownInput =
          '#TourBookingForm_SingleTourBookingForm_CityTown'
        Bookings.cityTownInputHolder =
          '#TourBookingForm_SingleTourBookingForm_CityTown_Holder'
        Bookings.childrenAttendingInput =
          '#TourBookingForm_SingleTourBookingForm_NumberOfChildren'
        jQuery(Bookings.specialAssistanceInfoInputHolder).slideUp()
        jQuery(Bookings.referralOtherTextInputHolder).slideUp()
        Bookings.simpleAttendeeListener()
        Bookings.childrenInputListener()
        Bookings.countryDropDownListener()
        Bookings.showHideCityTownHolder()
        Bookings.specialAssistanceCheckboxListener()
        Bookings.referralOptionsCheckboxSetListener()
        Bookings.numericFieldListener()
      }
      if (jQuery(Bookings.waitlistFormID).length) {
        Bookings.totalAttendeesInput =
          '#TourWaitlistForm_WaitlistForm_TotalNumberOfGuests'
        Bookings.formSubmitInput =
          '#TourWaitlistForm_WaitlistForm_action_dojoinwaitlist'
        Bookings.simpleAttendeeListener()
        Bookings.extraToursListener()
      }
      this.hideSubmitButtonAfterItIsClicked()
      this.bookingCode = jQuery(this.bookingCodeSelector).val()
    }
  },

  /**
   * BOOKING FORM LISTENERS
   */
  attendeeAndDateListener: function () {
    jQuery(Bookings.totalAttendeesInput).on('input paste', function (e) {
      Bookings.totalNumberOfGuests = parseInt(jQuery(this).val())
      var dateInputHolder = jQuery(Bookings.bookingDateInputHolder)
      if (!isNaN(Bookings.totalNumberOfGuests)) {
        Bookings.removeDynamicallyAddedFields()
        Bookings.hideOrShowFields()
        if (
          Bookings.maxPerGroup &&
          Bookings.totalNumberOfGuests > Bookings.maxPerGroup
        ) {
          Bookings.displayMaxPerGroupMessage(dateInputHolder)
        } else if (Bookings.totalNumberOfGuests > 0) {
          Bookings.showDateField()
          dateInputHolder
            .datepicker({
              startDate: '0',
              endDate: '+' + Bookings.daysInFutureAllowed + 'd',
            })
            .on('changeDate', function (e) {
              e.date = Bookings.checkIfUserNeedsToManuallyEnterDate(e.date)
              if (e.date !== Bookings.currentSearchDate) {
                Bookings.currentSearchDate = e.date
                Bookings.retrieveAvailability(
                  Bookings.currentSearchDate,
                  Bookings.totalNumberOfGuests
                )
              }
            })
        }
      } else {
        dateInputHolder.slideUp()
      }
    })
  },

  bookNowListener: function () {
    jQuery(Bookings.bookingFormID).on(
      'click',
      Bookings.bookNowButton,
      function (e) {
        e.preventDefault()
        jQuery(Bookings.bookNowButton).each(function (i, el) {
          jQuery(el).closest('li').removeClass('selected')
        })
        jQuery(this).closest('li').addClass('selected')
        var tourID = jQuery(this).data('id')
        jQuery(Bookings.tourIDInput).val(tourID)
        Bookings.hideOrShowFields(false)
        jQuery(Bookings.childrenAttendingInput).change()
        return false
      }
    )
  },

  childrenInputListener: function () {
    jQuery(Bookings.childrenAttendingInput).on(
      'input paste change',
      function (e) {
        if (jQuery('#max-children-msg').length) {
          jQuery('#max-children-msg').remove()
        }
        var el = jQuery(this)
        var numberOfChildren = el.val()
        var maxChildrenAllowed = Bookings.totalNumberOfGuests - 1
        if (maxChildrenAllowed < 0) {
          maxChildrenAllowed = 0
        }
        //there needs to be at least one adult
        if (numberOfChildren > maxChildrenAllowed) {
          var msg =
            '<span class="message validation" id="max-children-msg">The maximum number of children allowed for this booking is ' +
            maxChildrenAllowed +
            ', there needs to be at least one adult.</span>'
          el.val(maxChildrenAllowed)
          jQuery(msg).insertAfter(el.parent())
        }
      }
    )
  },

  countryDropDownListener: function () {
    jQuery(Bookings.countryDropDown).on('change', function (e) {
      Bookings.showHideCityTownHolder()
    })
  },

  showHideCityTownHolder() {
    var selectedCountry = jQuery(Bookings.countryDropDown).val()
    if (Bookings.showCityTownForCountries.includes(selectedCountry)) {
      jQuery(Bookings.cityTownInputHolder).slideDown()
      jQuery(Bookings.cityTownInput).prop('required', true)
    } else {
      jQuery(Bookings.cityTownInputHolder).slideUp()
      jQuery(Bookings.cityTownInput).prop('required', false)
    }
  },

  specialAssistanceCheckboxListener: function () {
    jQuery(Bookings.specialAssistanceCheckbox).on('change', function (e) {
      var el = jQuery(this)
      if (el.is(':checked')) {
        jQuery(Bookings.specialAssistanceInfoInputHolder).slideDown()
        jQuery(Bookings.specialAssistanceInfoInput).prop('required', true)
      } else {
        jQuery(Bookings.specialAssistanceInfoInputHolder).slideUp()
        jQuery(Bookings.specialAssistanceInfoInput).prop('required', false)
      }
    })
  },

  referralOptionsCheckboxSetListener: function () {
    jQuery(Bookings.referralOptionsCheckboxes).on('change', function (e) {
      Bookings.hideOrShowReferralTextInput()
    })
  },

  hideOrShowReferralTextInput: function () {
    let otherOption = jQuery(Bookings.referralOptionsList).data('other')
    if (otherOption == null) {
      //do nothing - there is no other option available
    } else {
      let otherIsSelected = false
      jQuery(Bookings.referralOptionsCheckboxes).each(function (i, el) {
        let value = jQuery(el).val()
        if (value == otherOption) {
          otherIsSelected = jQuery(el).is(':checked')
        }
      })

      if (otherIsSelected) {
        jQuery(Bookings.referralOtherTextInputHolder).slideDown()
      } else {
        jQuery(Bookings.referralOtherTextInputHolder).slideUp()
      }
    }
  },

  numericFieldListener: function () {
    jQuery(document).on('input', 'input.numeric', function (e) {
      var el = jQuery(this)
      var value = el.val()
      var start = this.selectionStart,
        end = this.selectionEnd
      var newValue = Bookings.numericFormatter(value)
      el.val(newValue)
      this.setSelectionRange(start, end)
    })
  },

  /**
   * WAITING LIST and SINGLE TOUR BOOKING FORM LISTENERS
   */

  simpleAttendeeListener: function () {
    jQuery(Bookings.totalAttendeesInput).on('input paste', function (e) {
      jQuery(Bookings.childrenAttendingInput).val(0)
      Bookings.totalNumberOfGuests = parseInt(jQuery(this).val())
      var submit = jQuery(Bookings.formSubmitInput)
      Bookings.removeDynamicallyAddedFields()
      if (!isNaN(Bookings.totalNumberOfGuests)) {
        if (
          Bookings.maxPerGroup &&
          Bookings.totalNumberOfGuests > Bookings.maxPerGroup
        ) {
          Bookings.displayMaxPerGroupMessage(Bookings.totalAttendeesInput)
          submit.prop('disabled', true)
          submit.slideUp()
        } else {
          submit.prop('disabled', false)
          submit.slideDown()
        }
      }
    })
  },

  extraToursListener: function () {
    jQuery(Bookings.extraToursField + ' label.left').on('click', function () {
      jQuery(this).toggleClass('show-extras')
      jQuery(Bookings.extraToursField + ' .middleColumn').slideToggle()
    })
  },

  /**
   * AJAX CALL
   */
  retrieveAvailability: function (date, totalNumberOfGuests) {
    var availabilityURL = '/' + Bookings.url + '/availability'
    jQuery.ajax({
      beforeSend: function () {},
      complete: function () {},
      dataType: 'html',
      data: {
        date: date.toDateString(),
        guests: totalNumberOfGuests,
        bookingcode: Bookings.bookingCode,
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(errorThrown)
      },
      success: function (data, textStatus, jqXHR) {
        Bookings.removeDynamicallyAddedFields()
        //if the calendar is clicked after a booking has already been selected
        if (jQuery(Bookings.tourIDInput).val()) {
          jQuery(Bookings.tourIDInput).val(0)
          Bookings.hideOrShowFields()
          Bookings.showDateField(false)
        }
        jQuery(data).insertAfter(Bookings.bookingDateInputHolder)
        var offset =
          jQuery(Bookings.tourInfoID).offset().top -
          jQuery('.site-header').outerHeight(true)
        jQuery('html, body').animate(
          {
            scrollTop: offset,
          },
          1000
        )
      },
      url: availabilityURL,
    })
  },

  /**
   * HELPER FUNCTIONS
   */
  hideOrShowFields: function (hide = true) {
    //hide all fields
    jQuery(Bookings.bookingFormID + ' .field').each(function (i, el) {
      var ele = jQuery(el)
      var eleID = '#' + ele.attr('id')
      if (!ele.hasClass('always-show')) {
        if (hide) {
          ele.hide()
        } else {
          if (
            ele.attr('id') ===
            'TourBookingForm_BookingForm_NumberOfChildren_Holder'
          ) {
            var maxChildrenAllowed = Bookings.totalNumberOfGuests - 1
            if (maxChildrenAllowed < 1) {
              ele.hide().find('input').removeAttr('required')
              ele.hide().find('input').prop('required', false)
            } else {
              ele.slideDown().prop('required', true)
              ele.slideDown().attr('required', 'required')
            }
          } else if (
            ele.attr('id') ===
            'TourBookingForm_BookingForm_SpecialAssistanceRequiredInfo_Holder'
          ) {
            if (jQuery(Bookings.specialAssistanceCheckbox).is(':checked')) {
              ele.slideDown()
            }
          } else if (eleID === Bookings.cityTownInputHolder) {
            Bookings.showHideCityTownHolder()
          } else if (eleID === Bookings.referralOtherTextInputHolder) {
            //do nothing
          } else {
            ele.slideDown()
          }
        }
      }
    })
    Bookings.hideOrShowReferralTextInput()

    //hide the submit button
    if (hide) {
      jQuery(Bookings.bookingFormID + ' input[type="submit"]').hide()
    } else {
      jQuery(Bookings.bookingFormID + ' input[type="submit"]').slideDown()
    }
  },

  showDateField: function (slide = true) {
    var dateInputHolder = jQuery(Bookings.bookingDateInputHolder)
    dateInputHolder.find('input').hide()
    if (slide) {
      dateInputHolder.slideDown()
    } else {
      dateInputHolder.show()
    }
  },

  removeDynamicallyAddedFields: function () {
    if (jQuery('#max-per-group-msg').length) {
      jQuery('#max-per-group-msg').remove()
    }
    if (jQuery('#date-and-tour-information').length) {
      jQuery('#date-and-tour-information').remove()
    }
  },

  numericFormatter: function (value) {
    var num = value.toString()
    if (num.length > 1) {
      num = num.replace(/^[0]/g, '')
    }
    num = num.replace(/[^0-9-]/g, '')
    return num.toString()
  },

  displayMaxPerGroupMessage: function (afterField) {
    var message =
      '<p class="alert alert-warning" id="max-per-group-msg">' +
      'For bookings greater than ' +
      Bookings.maxPerGroup +
      ', please email ' +
      '<a href="mailto:' +
      Bookings.emailContact +
      '">' +
      Bookings.emailContact +
      '</a>' +
      '</p>'
    jQuery(message).insertAfter(afterField)
  },

  hideSubmitButtonAfterItIsClicked: function () {
    jQuery('form').submit(function (event) {
      event.preventDefault()
      var form = this
      jQuery(form).find('input[type=submit]').prop('disabled', true).hide()
      jQuery(form).find('.Actions').addClass('loading')

      setTimeout(function () {
        form.submit()
      }, 300)
    })
  },

  checkIfUserNeedsToManuallyEnterDate: function (chosenDate, message) {
    const dateFormatMessage =
      'Please enter a date below using this format: DD/MM/YYYY, e.g. 15/1/2023'
    if (message == undefined) {
      message = 'Sorry, an error has occurred. ' + dateFormatMessage
    }
    if (
      isNaN(chosenDate) ||
      (chosenDate.getDate() === 1 &&
        chosenDate.getMonth() === 0 &&
        chosenDate.getFullYear() === 1970)
    ) {
      let input = window.prompt(message)
      if (input === null) {
        location.reload()
        return new Date()
      }
      // check the user has chosen a a valid date
      if (!/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/.exec(input)) {
        return Bookings.checkIfUserNeedsToManuallyEnterDate(NaN)
      }
      input = input.split('/')
      chosenDate = new Date(input[2], input[1] - 1, input[0])
      chosenDate.setHours(0, 0, 0, 0)
      if (isNaN(chosenDate)) {
        return Bookings.checkIfUserNeedsToManuallyEnterDate(NaN)
      }
      // check the user hasn't chossen a date in the past
      const currentDate = new Date()
      const firstDateIsInPastOfSecondDateIgnoringHours = (
        firstDate,
        secondDate
      ) => firstDate.setHours(0, 0, 0, 0) - secondDate.setHours(0, 0, 0, 0) < 0
      if (firstDateIsInPastOfSecondDateIgnoringHours(chosenDate, currentDate)) {
        return Bookings.checkIfUserNeedsToManuallyEnterDate(
          NaN,
          'The date cannot be in the past. ' + dateFormatMessage
        )
      }
      // check the user hasn't chosen a date more than 90 days in advance
      let currentDatePlus90Days = new Date()
      currentDatePlus90Days.setHours(0, 0, 0, 0)
      currentDatePlus90Days.setDate(
        currentDatePlus90Days.getDate() + Bookings.daysInFutureAllowed
      )
      const differenceInDaysBetweenDates = Math.abs(
        Math.floor(
          (chosenDate.getTime() - currentDatePlus90Days.getTime()) /
            (1000 * 3600 * 24)
        )
      )
      if (differenceInDaysBetweenDates > Bookings.daysInFutureAllowed) {
        return Bookings.checkIfUserNeedsToManuallyEnterDate(
          NaN,
          'The date cannot be more than ' +
            Bookings.daysInFutureAllowed +
            ' days in the future. ' +
            dateFormatMessage
        )
      }
    }
    return chosenDate
  },
}

module.exports = Bookings
