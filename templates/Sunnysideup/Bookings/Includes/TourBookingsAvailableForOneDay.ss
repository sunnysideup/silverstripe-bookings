<div id="date-and-tour-information">

    <h4>$MyDate.Format('EEEE'), $MyDate.DayOfMonth(true) $MyDate.Format('MMM y')</h4>
    <% if $DateInformation %>
        <% with $DateInformation %>
            $PublicContent
        <% end_with %>
    <% end_if %>
    <% if $ListOfToursForOneDay %>
        <ul id="list-of-tours">
            <% loop $ListOfToursForOneDay %>
                <li class="clearfix">
                    <div class="details-section list-of-tours-subsection">
                        $CalculatedPublicContent
                        $StartTime.Nice - $EndTime.Nice
                    </div>
                    <div class="book-now-section list-of-tours-subsection">
                        <% if $IsAvailable %>
                            <a href="#" data-id="$ID" class="btn btn--red book-now">Book Now</a>
                        <% else %>
                            <a href="$LinkToTourPage/waitlist/$ID/$Top.TotalNumberOfGuests" class="btn btn--red waitlist-btn externalLink">Join Waitlist</a>
                        <% end_if %>
                    </div>
                </li>
            <% end_loop %>
        </ul>
    <% else %>
        <p class="message required">There are no tours available on this day</p>
    <% end_if %>
</div>
