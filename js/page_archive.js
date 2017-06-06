var files = [];
var $html = {
    years: document.getElementsByName( "years" ),
    committees: document.getElementsByName( "committee" ),
    documentTypes: document.getElementsByName( "documentTypes" ),
    files_holder: document.getElementById( "files-holder" )
};

var filters = {
    "year": [],
    "committee": [],
    "document-type": []
};
var sortFilesBy = '+title';

window.onload = function init() {
    $.ajax( { // archives is in file-post.php
        url: wpApiSettings.root + 'graduate/v2/files/', // wpApiSettings is defined in functions.php\twentysixteen_scripts()
        method: 'GET',
        beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
        }
    } ).done( function ( response ) {
        files = response;

        updateFiles( files );
    } );
};

function setSortBy( element ) {

    sortFilesBy = element.value;

    updateFiles( files );
}

function sortByGiven( given, direction ) {
    if( direction == undefined )
        direction = 1;
    return function( a, b) {
        if (a[given] > b[given])
            return -direction;
        if (a[given] < b[given])
            return direction;
        return 0;
    }
}

function updateFilterList( condition, list, item ) {
    if( condition ) { // Add to the list

        if( list.indexOf( item ) > -1 ) // Item already exists
            return ;

        list.push( item ); // Push non-existing item

    } else {
        var index = list.indexOf( item );

        if( index > -1 ) // Item exists
            list.splice( index, 1 );
    }
}
function updateFilter( element, filterName, filter ) {
    updateFilterList( element.checked, filters[ filterName ], filter );
    updateFiles( files );
}
function filterBy( files, filterBy, filterForList ) {
    var list = [];

    for( var i = files.length; i --> 0; ) {
        for( var j = filterForList.length; j --> 0; ) {
            console.log( filterForList[ j ].toLowerCase() );
            if ( files[ i ][ filterBy ].toLowerCase() == filterForList[ j ].toLowerCase() ) {
                list.push( files[ i ] );
                break;
            }
        }
    }

    console.log( filterBy, files, list );

    return list;
}

function updateFiles( files ){
    var filtered = files;

    for( var filterName in filters )
        if( filters[ filterName ].length )
            filtered = filterBy( filtered, filterName, filters[ filterName ] );

    $html.files_holder.innerHTML = renderFiles( filtered );
}
function renderFiles( files ) {
    var r = '';

    var programCodeToProgram = {
        "appeals_serving_years": "Appeals",
        "curriculum_serving_years": "Curriculum",
        "policy_serving_years": "Policy",
        "program_serving_years": "Program Review"
    };

    var displayFileType = {
        'agenda': 'Agenda',
        'minutes': 'Minutes',
        'polices': 'Approved Policies',
        'forms': 'Forms and Files',
        'reports': 'Reports'
    };

    var direction = ( sortFilesBy.charAt(0) == '-')? -1:1;

    files.sort( sortByGiven( sortFilesBy.substr(1), direction ) );

    for( var i = files.length; i --> 0; ) {
        var file = files[ i ];

        r += "<tr>";
        r += "<td><a href='" + file.file_url + "'>" + file.title + "</a></td>";
        r += "<td>" + displayFileType[ file['document-type'] ] + "</td>";
        r += "<td>" + file.year + "</td>";
        r += "<td>" + programCodeToProgram[ file.committee ] + "</td>";
        r += "</tr>";
    }
    if( !files.length ) {
        r += "<tr><td colspan='4'>No results</td></tr>";
    }

    return r;
}