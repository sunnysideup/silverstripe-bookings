<div class="one-tour-booking">
<h3><a href="$EditLink(true)">$InitiatingFirstName $InitiatingSurname</a></h3>
    <dl id="booking-info" class="clearfix">
        <% with $Tour %>
            <dd>Date:</dd>
            <dt>$Date.Format('EEEE'), $Date.DayOfMonth(true) $Date.Format('MMM y')</dt>
            <dd>Time:</dd>
            <dt>$StartTime.Nice - $EndTime.Nice</dt>
        <% end_with %>
        <dd>Booking Code:</dd>
        <dt>$Code</dt>
        <dd>Contact:</dd>
        <dt>$InitiatingFirstName</dt>
        <dd>Email:</dd>
        <dt>$InitiatingEmail</dt>
        <dd>Phone:</dd>
        <dt>$PrimaryPhone</dt>
        <dd>Total number of guests:</dd>
        <dt>$TotalNumberOfGuests</dt>
        <dd>Number of adults:</dd>
        <dt>$NumberOfAdults</dt>
        <dd>Number of children:</dd>
        <dt>$NumberOfChildren</dt>
        <% if $SpecialAssistanceRequired %>
            <dd>Assistance Requested:</dd>
            <dt>$SpecialAssistanceRequiredInfo</dt>
        <% end_if %>
    </dl>
</div>
