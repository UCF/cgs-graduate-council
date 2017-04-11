<?php



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$file_name = "CouncilMember.csv";

function normalizeMembership( $s ) {
    $e = explode( ' | ', $s );
    $t = implode( ' ', $e );
    $e = explode( ';', $t );
    return implode( ',', $e );
}

function addMemberShips( $memberships, $s ) {
    $e = explode( ',', $s );

    $membershipsLen = count( $memberships );
    $eLen = count( $e );

    for( $j = 0; $j < $eLen; $j++ ) {
        $found = false;
        for( $i = 0; $i < $membershipsLen; $i++ ) {
            if( $memberships[ $i ] == $e[ $j ] )
                $found = true;
        }
        if( !$found )
            array_push( $memberships, $e[ $j ] );
    }



    return $memberships;
}

/* To see the contents of the file
if (($handle = fopen( $file_name, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
        }
    }
    fclose($handle);
}
*/

function membershipExists(  $con, $groupName, $membershipName  ) {
    $stmt = $con->prepare("SELECT a.taxonomy, b.name FROM wordpress.wordpress_term_taxonomy a JOIN wordpress.wordpress_terms b ON a.term_id = b.term_id WHERE a.taxonomy = ? AND b.name = ?");

    $stmt->bind_param("ss", $groupName, $membershipName);

    $stmt->execute();

    $stmt->bind_result( $tax, $name );

    if($stmt->fetch()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

function generateSlug( $name ) {
    $e = explode( ' ', $name );
    $r = implode( '-', $e );
    return strtolower( $r );
}

function insertTerm( $con, $term ) {
    $stmt = $con->prepare("INSERT INTO `wordpress`.`wordpress_terms` (`name`,`slug`) VALUES (?,?)");

    $slug = generateSlug( $term );

    $stmt->bind_param("ss", $term, $slug );

    $stmt->execute();

    $termId = $stmt->insert_id;

    $stmt->close();

    return $termId;
}

function insertTermTaxonomy( $con, $taxonomy, $term_id ) {
    $stmt = $con->prepare("INSERT INTO `wordpress`.`wordpress_term_taxonomy` (`term_id`,`taxonomy`,`description`) VALUES (?,?,?)");

    $description = '';

    $stmt->bind_param("sss", $term_id, $taxonomy, $description );

    $stmt->execute();

    $stmt->close();
}

function tryInsertMembership( $con, $groupName, $membershipName ) {
    if( trim( $membershipName ) != '' && !membershipExists( $con, $groupName, $membershipName ) ) {

        $id = insertTerm( $con, $membershipName );

        insertTermTaxonomy( $con, $groupName, $id );
        return true;
    }
    return false;
}



$first = true;

$members = array();

$con = mysqli_connect("localhost","wpuser","Test123!wp","wordpress");
if ( $con->connect_error)
    die("Connection failed: " . $con->connect_error);

    $stmtDelete = $con->prepare("DELETE FROM wordpress_posts WHERE post_type = ?");
    $post_type = 'gs_member';
    $stmtDelete->bind_param('s', $post_type);
    $stmtDelete->execute();

    $stmt = $con->prepare("INSERT INTO wordpress_posts (
                            `post_author`,
                            `post_date`,
                            `post_date_gmt`,
                            `post_content`,
                            `post_title`,
                            `post_excerpt`,
                            `post_status`,
                            `comment_status`,
                            `ping_status`,
                            `post_password`,
                            `post_name`,
                            `to_ping`,
                            `pinged`,
                            `post_modified`,
                            `post_modified_gmt`,
                            `post_content_filtered`,
                            `post_parent`,
                            `guid`,
                            `menu_order`,
                            `post_type`,
                            `post_mime_type`,
                            `comment_count`
                        ) VALUES ( ?,now(),utc_timestamp(),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? )");

    $stmt->bind_param("isssssssssssssisissi",
        $post_author,
        $post_content,
        $post_title,
        $post_excerpt,
        $post_status,
        $comment_status,
        $ping_status,
        $password,
        $post_name,
        $to_ping,
        $pinged,
        $post_modified,
        $post_modified_gmt,
        $post_content_filtered,
        $post_parent,
        $guid,
        $menu_order,
        $post_type,
        $post_mime_type,
        $comment_count);

    $stmtMeta = $con->prepare("INSERT INTO wordpress_postmeta ( `post_id`, `meta_key`, `meta_value` ) VALUES ( ?, ?, ? )");
    $stmtMeta->bind_param( "iss", $ID, $meta_key, $meta_value );


$councilMemberships = array();
$curriculumMemberships = array();
$policyMemberships = array();
$appealsMemberships = array();
$programMemberships = array();


if (($handle = fopen( $file_name, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $member = array(
            'ID'                                        => $data[0],
            'first_name'                                => $data[1],
            'last_name'                                 => $data[2],
            'date_modified'                             => $data[3],
            'editor'                                    => $data[4],
            'status'                                    => $data[5],
            'email'                                     => $data[6],
            'college'                                   => $data[7],
            'department'                                => $data[8],
            'faculty_senate_member'                     => ( $data[9] == 'No')? '' : 'On',
            'faculty_senate_steering_committee_member'  => ( $data[10] == 'No')? '' : 'On',
            'council_serving_years'                     => normalizeMembership( $data[11] ),
            'curriculum_serving_years'                  => normalizeMembership( $data[12] ),
            'policy_serving_years'                      => normalizeMembership( $data[13] ),
            'appeals_serving_years'                     => normalizeMembership( $data[14] ),
            'program_serving_years'                     => normalizeMembership( $data[15] )
        );
        //print_r( $member );

        if( !$first ) {
            array_push($members, $member);


            $post_author = 1;
            $post_content = '';
            $post_title = $member['last_name'] . ', ' . $member['first_name'];
            $post_excerpt = '';
            $post_status = 'publish';
            $comment_status = 'closed';
            $ping_status = 'closed';
            $password = '';
            $post_name = $member['first_name'] . '-' . $member['last_name'];
            $to_ping = '';
            $pinged = '';
            $post_modified = '0000-00-01 00:00:00';
            $post_modified_gmt = '0000-00-01 00:00:00';
            $post_content_filtered = '';
            $post_parent = 0;
            $guid = '';
            $menu_order = 0;
            $post_type = 'gs_member';
            $post_mime_type = '';
            $comment_count = 0;

            if( !$stmt->execute() )
                echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;

            $ID = $stmt->insert_id;
            echo $ID . ' ' . $member['first_name'] . ' ' . $member['last_name'] . '<br>';

            $meta_key = "first_name";
            $meta_value = $member["first_name"];
            $stmtMeta->execute();

            $meta_key = "last_name";
            $meta_value = $member["last_name"];
            $stmtMeta->execute();

            $meta_key = "email";
            $meta_value = $member["email"];
            $stmtMeta->execute();

            $meta_key = "college";
            $meta_value = $member["college"];
            $stmtMeta->execute();

            $meta_key = "department";
            $meta_value = $member["department"];
            $stmtMeta->execute();

            $meta_key = "faculty_senate_member";
            $meta_value = $member["faculty_senate_member"];
            $stmtMeta->execute();

            $meta_key = "faculty_senate_steering_committee_member";
            $meta_value = $member["faculty_senate_steering_committee_member"];
            $stmtMeta->execute();

            $meta_key = "council_serving_years";
            $meta_value = $member["council_serving_years"];
            $stmtMeta->execute();

            $meta_key = "curriculum_serving_years";
            $meta_value = $member["curriculum_serving_years"];
            $stmtMeta->execute();

            $meta_key = "policy_serving_years";
            $meta_value = $member["policy_serving_years"];
            $stmtMeta->execute();

            $meta_key = "appeals_serving_years";
            $meta_value = $member["appeals_serving_years"];
            $stmtMeta->execute();

            $meta_key = "program_serving_years";
            $meta_value = $member["program_serving_years"];
            $stmtMeta->execute();

            $councilMemberships     = addMemberShips( $councilMemberships, $member["council_serving_years"] );
            $curriculumMemberships  = addMemberShips( $curriculumMemberships, $member["curriculum_serving_years"] );
            $policyMemberships      = addMemberShips( $policyMemberships, $member["policy_serving_years"] );
            $appealsMemberships     = addMemberShips( $appealsMemberships, $member["appeals_serving_years"] );
            $programMemberships     = addMemberShips( $programMemberships, $member["program_serving_years"] );
        }

        $first = false;
    }
    sort( $councilMemberships );
    sort( $curriculumMemberships );
    sort( $policyMemberships );
    sort( $appealsMemberships );
    sort( $programMemberships );

    for( $i = 0, $len = count( $councilMemberships ); $i < $len; $i++ ) {
        echo $councilMemberships[ $i ] . tryInsertMembership($con,'graduate_council_serving_years',$councilMemberships[ $i ]) . "<br>";
    }
    echo "<br>";
    for( $i = 0, $len = count( $curriculumMemberships ); $i < $len; $i++ ) {
        echo $curriculumMemberships[ $i ] . tryInsertMembership($con,'curriculum_serving_years',$curriculumMemberships[ $i ]) . "<br>";
    }
    echo "<br>";
    for( $i = 0, $len = count( $policyMemberships ); $i < $len; $i++ ) {
        echo $policyMemberships[ $i ] . tryInsertMembership($con,'policy_serving_years',$policyMemberships[ $i ]) . "<br>";
    }
    echo "<br>";
    for( $i = 0, $len = count( $appealsMemberships ); $i < $len; $i++ ) {
        echo $appealsMemberships[ $i ] . tryInsertMembership($con,'appeals_serving_years',$appealsMemberships[ $i ]) . "<br>";
    }
    echo "<br>";
    for( $i = 0, $len = count( $programMemberships ); $i < $len; $i++ ) {
        echo $programMemberships[ $i ] . tryInsertMembership($con,'program_serving_years',$programMemberships[ $i ]) . "<br>";
    }
    echo "<br>";


    print_r( $councilMemberships ); echo "<br><br>";
    print_r( $curriculumMemberships ); echo "<br><br>";
    print_r( $policyMemberships ); echo "<br><br>";
    print_r( $appealsMemberships ); echo "<br><br>";
    print_r( $programMemberships ); echo "<br><br>";
    fclose( $handle );
}




