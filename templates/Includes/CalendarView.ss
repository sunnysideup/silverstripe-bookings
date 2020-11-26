<h2 class="pull-right"><a href="$TourLinks(Booking)" target="_cms" class="external-link">Search Bookings $SVG('search')</a></h2>


<div id="legend">
    <legend>
        <strong>SL</strong> = Remaining Spaces / Total Spaces,
        <strong>G</strong> = Number of Groups,
        <strong>A</strong> = Number of Adults,
        <strong>C</strong> = Number of Children
    </legend>
</div>
<hr/>


<div id="calendar"></div>

<div id="quick-view-modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <a href="#" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
            </div>
            <div class="modal-body">
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>

<hr/>

<div class="edit-tours-in-the-cms">
    <h3>Edit Tours in the CMS</h3>
    <ul>
        <% loop $TourLinks %>
            <li><a href="$CMSListLink" target="_cms" class="external-link">$plural_name</a></li>
        <% end_loop %>
    </ul>
</div>
