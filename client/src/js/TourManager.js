var TourManager = {
  url: '',

  tours: {},

  calendarID: '#calendar',

  checkInCheckboxClass: '.check-in',

  init: function () {
    if (
      jQuery(TourManager.calendarID).length ||
      jQuery('#tour-check-in-page').length
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
    jQuery(TourManager.calendarID).fullCalendar({
      header: {
        left: 'today prev,next',
        center: 'title',
        right: 'listYear,basicDay,basicWeek,month',
      },
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
      defaultView: 'listYear',
      allDaySlot: false,
      minTime: '07:00:00',
      maxTime: '19:00:00',
      events: '/' + TourManager.url + '/all',
      eventRender: function (event, element) {
        var html = element[0].innerHTML
        var popUpLink = '',
          newHTML = ''
        if (event.url) {
          var popUpUrl = event.url.replace('checkinfortour', 'quickview')
          popUpLink =
            '<a href="' +
            popUpUrl +
            '" class="quick-view">' +
            '<svg enable-background="new 0 0 64 64" height="64px" version="1.1" viewBox="0 0 64 64" width="64px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Layer_1"><g><circle cx="32" cy="32" fill="#ccc" r="32"/></g><g opacity="0.2"><g><path d="M48,52.5c-0.64,0-1.28-0.244-1.768-0.732l-12-12c-0.977-0.977-0.977-2.559,0-3.535     c0.976-0.977,2.56-0.977,3.535,0l12,12c0.977,0.977,0.977,2.559,0,3.535C49.28,52.256,48.64,52.5,48,52.5z" fill="#231F20"/></g></g><g><path d="M48,50.5c-0.64,0-1.28-0.244-1.768-0.732l-12-12c-0.977-0.977-0.977-2.559,0-3.535    c0.976-0.977,2.56-0.977,3.535,0l12,12c0.977,0.977,0.977,2.559,0,3.535C49.28,50.256,48.64,50.5,48,50.5z" fill="#4F5D73"/></g><g><ellipse cx="28.386" cy="28.386" fill="#C91630" rx="10.567" ry="10.567" transform="matrix(0.7071 -0.7071 0.7071 0.7071 -11.7579 28.386)"/></g><g opacity="0.2"><g><path d="M28.386,19.819c2.704,0,5.409,1.032,7.472,3.095c4.127,4.127,4.127,10.817,0,14.944     c-2.063,2.063-4.768,3.095-7.472,3.095s-5.409-1.032-7.472-3.095c-4.127-4.127-4.127-10.817,0-14.944     C22.977,20.851,25.682,19.819,28.386,19.819z M28.386,15.819c-3.891,0-7.549,1.515-10.3,4.267c-5.68,5.68-5.68,14.921,0,20.601     c2.751,2.751,6.409,4.267,10.3,4.267s7.549-1.515,10.3-4.267c5.68-5.68,5.68-14.921,0-20.601     C35.935,17.334,32.277,15.819,28.386,15.819L28.386,15.819z" fill="#231F20"/></g></g><g><path d="M28.386,17.819c2.704,0,5.409,1.032,7.472,3.095c4.127,4.127,4.127,10.817,0,14.944    c-2.063,2.063-4.768,3.095-7.472,3.095s-5.409-1.032-7.472-3.095c-4.127-4.127-4.127-10.817,0-14.944    C22.977,18.851,25.682,17.819,28.386,17.819 M28.386,13.819c-3.891,0-7.549,1.515-10.3,4.267c-5.68,5.68-5.68,14.921,0,20.601    c2.751,2.751,6.409,4.267,10.3,4.267s7.549-1.515,10.3-4.267c5.68-5.68,5.68-14.921,0-20.601    C35.935,15.334,32.277,13.819,28.386,13.819L28.386,13.819z" fill="#FFFFFF"/></g></g><g id="Layer_2"/></svg>' +
            '</a>'
          newHTML = html.replace('</a></td>', '</a>' + popUpLink + '</td>')
          element[0].innerHTML = newHTML
        }
      },
      eventAfterRender: function (event, element, view) {
        if (view.type == 'month' || view.type == 'basicWeek') {
          element.find('.fc-title').text(event['abrv-title'])
        }
      },
      viewRender: function (currentView) {
        const moment = require('moment')
        var minDate = moment()
        // Past
        if (minDate >= currentView.start && minDate <= currentView.end) {
          $('.fc-prev-button').prop('disabled', true)
          $('.fc-prev-button').addClass('fc-state-disabled')
        } else {
          $('.fc-prev-button').removeClass('fc-state-disabled')
          $('.fc-prev-button').prop('disabled', false)
        }
      },
    })
  },

  checkInListener: function () {
    jQuery(TourManager.checkInCheckboxClass).on('change', function (e) {
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

module.exports = TourManager
