var $members = document.getElementById('members');
var $years = document.getElementById('years');

var members = [];

window.onload = function init(){
    var years = getQueryVariable("years");

    if( !/\d{4,}-\d{4,}/ig.test( years ) )
        years = settings.currentYear;

    $.ajax( {
        url: wpApiSettings.root + 'graduate/v2/members/' + settings.committee, // wpApiSettings is defined in functions.php\twentysixteen_scripts()
        method: 'GET',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
        }
    } ).done( function ( response ) {
        members = response;

        // Generates titles for each committee a member belongs to.
        normalizeMembers( members );

        var filteredMembers = updateMembers( members, settings.committee, years ); // Filters and sorts by Committee, then Alpha

        $members.innerHTML = renderMembers( filteredMembers, settings.committee, years, false );
        $years.innerHTML = renderYears( generateYears( members, settings.committee ), years );
        fixMemberBoxes();
    } );
};