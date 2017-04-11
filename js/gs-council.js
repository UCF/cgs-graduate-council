/**
 * Created by br006093 on 2/15/2017.
 */

/**
 * @typedef {Object} Member - Given to us by a page
 * @property {string} first_name
 * @property {string} last_name
 * @property {string} college
 * @property {string} department
 * @property {string} email
 * @property {string} faculty_senate_member
 * @property {string} faculty_senate_steering_committee_member
 * @property {string} highestTitle
 * @property {string} council_serving_years
 * @property {string} appeals_serving_years
 * @property {string} curriculum_serving_years
 * @property {string} policy_serving_years
 * @property {string} program_serving_years
 * @property {string} url
 */


/**
 * @type {{rankOrdering: string[]}}
 */
var gsConstants = {
    rankOrdering: [
        'chair',
        'vice chair',
        'vice-chair',
        'ex-officio',
        'ex officio',
        'member',
        'steering member',
        'liaison from the college of graduate studies',
        'student',
        'student-member',
        'student member',
        ''
    ]
};

//noinspection JSUnusedGlobalSymbols
function memberDetails( membership ) {
    var parts = ['',''];
    if( membership != '' )
        parts = membership.split(' ');

    return {
        year: parts[ 0 ].trim(),
        title: parts[ 1 ].trim()
    };
}


function renderTitles( committee, committeeMembership, years ) {
    var membership = [], r = '', details;
    if( committeeMembership != '' ) {
        membership = committeeMembership;
        for( var i = 0; i < membership.length; i++ ) {
            details = membership[i];
            if( details.years == years ) {
                r += '<ul class="memberSub">';
                r += '<li>' + committee;
                if( details.title.toLowerCase() != 'member' ) {
                    r += ' ( ' + details.title + ' )';
                }
                r += '</li>';
                r += '</ul>';
            }
        }
    }

    return r;
}

/**
 * Sort Members by rank, then by last name
 * @param {Member} a - Member object
 * @param {Member} b - Member object
 * @returns {number}
 */
function sortMembersByMembership(a, b) {
    var indexA = gsConstants.rankOrdering.indexOf(a.highestTitle.toLowerCase()),
        indexB = gsConstants.rankOrdering.indexOf(b.highestTitle.toLowerCase());

    if (indexA > indexB)
        return 1;
    if (indexA < indexB)
        return -1;

    if (a.last_name.toLowerCase() > b.last_name.toLowerCase())
        return 1;
    if (a.last_name.toLowerCase() < b.last_name.toLowerCase())
        return -1;

    return 0;
}

/**
 *
 * @param a
 * @param b
 * @returns {number}
 */
function sortRanks( a, b ) {
    var indexA = gsConstants.rankOrdering.indexOf( a.title.toLowerCase() ),
        indexB = gsConstants.rankOrdering.indexOf( b.title.toLowerCase() );

    if( indexA > indexB)
        return 1;
    if( indexA < indexB)
        return -1;

    return 0;
}

function highestTitle( member, council, years ) {
    var memberships = member[ council ];

    var filtered = [];
    for( var i = 0; i < memberships.length; i++ ) {
        var membership = memberships[ i ];

        if( membership.years == years )
            filtered.push( membership );
    }

    return filtered.sort(sortRanks)[0].title.trim();
}

/**
 *
 * @param {Member} member
 * @param {string} council
 * @param {string} years
 * @param {boolean=} showCouncils
 * @returns {string}
 */
function renderMember( member, council, years, showCouncils ) {
    var councilTitle = '';


    if( member[ council ] )
        councilTitle = highestTitle( member, council, years );

    var r = '';
    r += '<div class="memberBox">';
    r += '<div class="memberName">';
    r += '<a href="mailto:' + member.email + '">' + member.first_name + ' ' + member.last_name + '</a>';
    if( member.faculty_senate_member != '' ) {
        r += ' * ';
    }
    r += '</div>';
    if( councilTitle != 'Member' )
        r += '<div class="memberRank">' + councilTitle + '</div>';

    r += '<div class="memberCollege">';
    r += member.college;
    r += '</div>';

    if( showCouncils == undefined && showCouncils !== false ) {

        r += '<div class="memberDetails">';

        if (member.appeals_serving_years && council != 'appeals_serving_years')
            r += renderTitles('Appeals and Awards', member.appeals_serving_years, years);
        if (member.council_serving_years && council != 'council_serving_years')
            r += renderTitles('Graduate Council', member.council_serving_years, years);
        if (member.curriculum_serving_years && council != 'curriculum_serving_years')
            r += renderTitles('Curriculum', member.curriculum_serving_years, years);
        if (member.policy_serving_years && council != 'policy_serving_years')
            r += renderTitles('Policy and Procedures', member.policy_serving_years, years);
        if (member.program_serving_years && council != 'program_serving_years')
            r += renderTitles('Program Review', member.program_serving_years, years);

        r += '</div>';
    }

    if ( member.url )
        r += '<a href="' + member.url + '">Edit Member</a>';

    r += '</div>';

    return r;
}

/**
 *
 * @param {Member[]} members
 * @param {string} council
 * @param {string} years
 * @param {boolean=} showCouncils
 * @returns {string}
 */
function renderMembers( members, council, years, showCouncils ) {
    var $htmlMembers = '';
    for( var i = 0; i < members.length; i++ ) {
        $htmlMembers += renderMember( members[ i ], council, years, showCouncils );
    }
    return $htmlMembers;
}

function renderMembersGroup( groupName, members, council, years ) {
    var r = '<div class="membersGroup">';

    if( groupName ) {
        r += '<div class="membershipGroupName">';
        r += groupName;
        r += '</div>';
    }

    r += renderMembers( members, council, years );
    r += '</div>';
    return r;
}

function updateMembers( members, council, years ) {
    var filtered = [];

    for( var i = 0; i < members.length; i++ ) {
        var member = members[ i ];

        for( var j = 0; j < member[ council ].length; j++ ) {
            var membership = member[ council ][ j ];
            if( membership.years == years ) {
                member.highestTitle = highestTitle( member, council, years );
                filtered.push( member );
                break;
            }
        }
    }

    filtered.sort( sortMembersByMembership );

    return filtered;
}

/**
 *
 * @param {Member[]} members
 * @returns {{}}
 */
function groupMembersByCollege( members ) {

    var colleges = {};

    for( var i = 0; i < members.length; i++ ) {
        var member = members[i];

        if( !colleges[ member.college ] ) {
            colleges[ member.college ] = [];
        }

        colleges[ member.college ].push( member );
    }

    return colleges;
}

/**
 * Splits the titles into simple objects
 * @param {Member} member
 * @param {string} council
 */
function generateTitles( member, council ) {
    var titles = member[ council ].split(',');
    member[ council ] = [];

    for( var i = 0; i < titles.length; i++ ) {
        var title = titles[ i ];
        var parts = title.split(' ');
        var years = parts[0];
        parts[0] = '';
        title = parts.join(' ').trim();
        member[ council ].push({
            years: years,
            title: title
        });
    }
}

/**
 * Runs some normalization to make members more easy to use.
 * @param {Member[]} members
 * @returns {Member[]}
 */
function normalizeMembers( members ) {
    for( var i = members.length; i --> 0; ) {
        /** @type {Member} */
        var member = members[ i ];

        generateTitles( member, 'council_serving_years' );
        generateTitles( member, 'curriculum_serving_years' );
        generateTitles( member, 'policy_serving_years' );
        generateTitles( member, 'appeals_serving_years' );
        generateTitles( member, 'program_serving_years' );
    }

    return members;
}

/**
 * Generates an array of years in asc format 20XX-20YY . ex: [2001-2002, 2002-2003]
 * @param {Member[]} members
 * @param {string} council
 * @returns {string[]}
 */
function generateYears( members, council ) {
    var years = [];

    for( var i = members.length; i --> 0; ) {
        var councilMemberships = members[ i ][ council ];

        for( var j = councilMemberships.length; j --> 0; ) {
            var membership = councilMemberships[ j ];

            if( years.indexOf( membership.years ) == -1 )
                years.push( membership.years );
        }
    }

    return years.sort();
}

/**
 * Accepts an array of years in asc format
 * @param {string[]} years - ex: ["2001-2002","2002-2003"]
 * @param {string} currentYear
 * @returns {string} - html
 */
function renderYears( years, currentYear ) {
    var r = '<ul class="list-no-bullet">';

    for( var i = years.length; i --> 0; ) {
        if( years[i] == currentYear )
            r += '<li class="member-year-list-item">' + years[i] + '</li>';
        else
            r += '<li class="member-year-list-item"><a href="?years=' + years[ i ] + '#member">' + years[i] + '</a></li>';
    }

    return r + '</ul>';
}

/**
 *  Returns the value of the URI
 * @param {string} variable
 * @returns {*}
 */
function getQueryVariable( variable ){
    var query = window.location.search.substring( 1 );
    var vars = query.split( "&" );

    for( var i = vars.length; i --> 0; ){
        var part = vars[ i ].split( "=" );

        if( part[ 0 ] == variable )
            return part[ 1 ];
    }

    return false;
}
