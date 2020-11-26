<div id="date-and-tour-information">
    <h6>$MyDate.Format('l, jS M Y')</h6>
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
                            <a href="#" data-id="$ID" class="btn primary-btn book-now">Book Now</a>
                        <% else %>
                            <a href="$LinkToTourPage/waitlist/$ID/$Top.TotalNumberOfGuests" class="btn primary-btn waitlist-btn externalLink">Join Waitlist</a>
                        <% end_if %>
                    </div>
                </li>
            <% end_loop %>
        </ul>
    <% else %>
        <p class="message required">There are no tours available on this day</p>
    <% end_if %>
</div>
