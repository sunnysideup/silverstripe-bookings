<div class="one-tour-booking clearfix">
<h3>$InitiatingFirstName $InitiatingSurname</h3>
    <dl id="booking-info" class="clearfix">
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
