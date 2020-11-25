
import { Calendar } from 'fullcalendar'

var TourManager = {
  url: '',

  tours: {},

  calendarID: '#calendar',
  calendarIDwithoutHash: 'calendar',

  checkInCheckboxClass: '.tour-checkin-content__input-field',

  init: function () {
    if (
      jQuery(TourManager.calendarID).length ||
      jQuery('.tour-checkin-content').length
    ) {
      if (typeof TourBookingsInPageData === 'object') {
        for (var key in TourBookingsInPageData) {
          if (TourBookingsInPageData.hasOwnProperty(key)) {
            TourManager[key] = TourBookingsInPageData[key]
          }
        }
      }
      TourManager.activateCalendar()
      TourManager.checkInListener()
      TourManager.quickViewListener()
      TourManager.printButtonListener()
    }
  },

  activateCalendar: function () {

    const calendarEl = document.getElementById(TourManager.calendarIDwithoutHash)
    const calendar = new Calendar(
      calendarEl,
      // {
      //   initialView: 'dayGridMonth',

      // }
      {
        buttonText: {
          today: 'Today',
          month: 'Month',
          basicWeek: 'Week',
          basicDay: 'Day',
          listYear: 'List',
        },
        views: {
          basicDay: {
            timeFormat: 'H:mm a',
          },
          basicWeek: {
            columnFormat: 'ddd D/M',
            timeFormat: 'H:mm a',
          },
        },
        allDaySlot: false,
        events: '/' + TourManager.url + '/all',

      }

    );
    calendar.render()
  },

  checkInListener: function () {
    jQuery(TourManager.checkInCheckboxClass).on('change', function (e) {
      console.log('sdfsdf')
      var hasArrived = false
      var el = jQuery(this)
      el.parent().addClass('loading')
      if (el.is(':checked')) {
        hasArrived = true
      }
      var bookingID = el.data('id')
      var jsonURL = '/' + TourManager.url + '/confirmonecheckin'
      jQuery.ajax({
        beforeSend: function () {},
        complete: function () {},
        dataType: 'json',
        data: {
          id: bookingID,
          arrived: hasArrived,
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.log(errorThrown)
        },
        success: function (data, textStatus, jqXHR) {
          if (data) {
            el.parent().removeClass('loading')
          }
        },
        url: jsonURL,
      })
    })
  },

  quickViewListener: function () {
    jQuery(document).on('click', '.quick-view', function (e) {
      jQuery('#quick-view-modal')
        .modal('show')
        .find('.modal-body')
        .load(jQuery(this).attr('href'))
      return false
    })
  },

  printButtonListener: function () {
    jQuery('#print-bookings').on('click', function (e) {
      window.print()
      return false
    })
  },
}

export default TourManager
