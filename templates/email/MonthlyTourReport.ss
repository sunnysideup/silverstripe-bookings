<h2>Attendance</h2>
<table class="infotable readonly InformationTable">
	<tbody>
        <tr>
            <th>Total Number of Groups</th>
            <td>$TotalGroups</td>
        </tr>
        <tr>
            <th>Total Number of Attendees</th>
            <td>$TotalBookings</td>
        </tr>
	</tbody>
</table>

<% if $Nationalities %>
    <h2>Nationalities</h2>
    <table class="infotable readonly InformationTable">
        <thead>
            <tr>
                <th>Country</th>
                <th>Number of Groups</th>
            </tr>
        </thead>
        <tbody>
            <% loop $Nationalities %>
                <tr>
                    <td>$Country</td>
                    <td>$Count</td>
                </tr>
            <% end_loop %>
    	</tbody>
    </table>
<% end_if %>

<% if $CitiesAndTowns %>
	<h2>Cities</h2>
	<table class="infotable readonly InformationTable">
		<thead>
			<tr>
				<th>City/Town</th>
				<th>Number of Groups</th>
			</tr>
		</thead>
		<tbody>
			<% loop $CitiesAndTowns %>
				<tr>
					<td>$CityTown</td>
					<td>$Count</td>
				</tr>
			<% end_loop %>
		</tbody>
	</table>
<% end_if %>

<% if $RefferalData %>
    <h2>Refferal Data</h2>
    <table class="infotable readonly InformationTable">
    	<tbody>
            <thead>
                <tr>
                    <th>Refferal Option</th>
                    <th>Number of Groups</th>
                </tr>
            </thead>
            <% loop $RefferalData %>
                <tr>
                    <td>$Title</td>
                    <td>$Count</td>
                </tr>
            <% end_loop %>
    	</tbody>
    </table>
    <% loop $RefferalData %>
        <% if $OtherResponses %>
            <h3>Reponses to "Other" refferal option</h3>
            <ul>
                <% loop $OtherResponses %>
                    <% if $Reponse %>
                        <li>$Reponse</li>
                    <% end_if %>
                <% end_loop %>
            </ul>
        <% end_if %>
    <% end_loop %>
<% end_if %>
