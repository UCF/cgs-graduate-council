<?php
get_header();
$setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main" role="main">

            	<div class="content-tile">
                    <h1 class="entry-title">About the Graduate Council</h1>
                    <img src="/wp-content/themes/graduate-council/images/mh.png" id="front-page-image"/>
                    <p>
                        UCF graduate education depends on the participation of its faculty on university committees for guidance and decisions. The Graduate Council is a standing committee of the Faculty Senate and is comprised of four Graduate Committees: Appeals, Curriculum, Policy, and Program Review and Awards. A number of other working groups and committees assist with graduate education at UCF and provide valuable services.
                    </p>
                    <p>
                        Refer to the Faculty Constitution, Bylaws Section VII.B for the Graduate Council’s duties and responsibilities, membership, and committees.
                    </p>
                </div><!-- .content-tile -->
                <div style="clear:both;"></div>
              	<div class="content-tile">
                    <div style="float: right;">
                        <select id="committee-select" onchange="actionChangeCouncil( this );dataLayer.push({'event': 'frontpage-member-sort-change'});">
                            <option value="council_serving_years">Show all</option>
                            <option value="byCollege">List by College</option>
                            <option value="appeals_serving_years">Appeals</option>
                            <option value="curriculum_serving_years">Curriculum</option>
                            <option value="policy_serving_years">Policy</option>
                            <option value="program_serving_years">Program Review and Awards</option>
                        </select>
                    </div>
                    <h2>Current Graduate Council Members <?php echo $setting_current_year; ?></h2>
					<div class="memberRank">‡ denotes a faculty senate steering committee member</div>
					<div class="memberRank">* denotes a faculty senate member</div>
                    <?php
    
                    function JSONMember( $meta ) {
                        $post = get_post();
    
                        $r = '{';
                        $r .= '"first_name": "'                                  . $meta['first_name'][0] . '",';
                        $r .= '"last_name": "'                                   . $meta['last_name'][0] . '",';
                        $r .= '"email": "'                                       . $meta['email'][0] . '",';
                        $r .= '"college": "'                                     . $meta['college'][0] . '",';
                        $r .= '"faculty_senate_member": "'                       . $meta['faculty_senate_member'][0] . '",';
                        $r .= '"faculty_senate_steering_committee_member": "'    . $meta['faculty_senate_steering_committee_member'][0] . '",';
    
    
                        $council_serving_years = ( !empty($meta['council_serving_years'][0]) )? $meta['council_serving_years'][0]: '';
                        $curriculum_serving_years = ( !empty($meta['curriculum_serving_years'][0]) )? $meta['curriculum_serving_years'][0]: '';
                        $policy_serving_years = ( !empty($meta['policy_serving_years'][0]) )? $meta['policy_serving_years'][0]: '';
                        $appeals_serving_years = ( !empty($meta['appeals_serving_years'][0]) )? $meta['appeals_serving_years'][0]: '';
                        $program_serving_years = ( !empty($meta['program_serving_years'][0]) )? $meta['program_serving_years'][0]: '';
    
                        $r .= '"council_serving_years": "'                       . $council_serving_years . '",';
                        $r .= '"curriculum_serving_years": "'                    . $curriculum_serving_years . '",';
                        $r .= '"policy_serving_years": "'                        . $policy_serving_years . '",';
                        $r .= '"appeals_serving_years": "'                       . $appeals_serving_years . '",';
                        $r .= '"program_serving_years": "'                       . $program_serving_years . '"';
                        if ( $url = get_edit_post_link( $post->ID ) ) {
                            $r .= ',"url": "'                                     . esc_url( $url ) . '"';
                        }
    
                        $r .= '}';
    
                        return $r;
                    }
    
                    $setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
    
                    $args = array(
                        'post_type' => 'gs_member',
                        'posts_per_page' => -1
                    );
                    ?>
                    <div>
                        <script>
                            var members = [];

                        <?php
                        $member_order = array(
                            'chair',
                            'vice-chair',
                            'liaison from the college of graduate studies',
                            'student',
                            'ex officio',
                            'member',
                            '',
                        );
    
                        $members = array();
                        $query_members = new WP_Query( $args );
                        $first = true;
                        if($query_members->have_posts()):
                            echo 'members = [';
                            while ($query_members->have_posts()):
                                $query_members->the_post();
    
                                $meta = get_post_meta( $post->ID );
    
                                if( !$first )
                                    echo ',';
                                else
                                    $first = false;
                                echo JSONMember( $meta );
    
                            endwhile;
                            echo '];';
                        endif;
                        wp_reset_query();

                        ?>
                        </script>
                        <div id="members"></div>
                        <div style="clear:both;"></div>
                   </div><!-- .content-tile -->
            </main><!-- .site-main -->
            <script>
                var _current_year = "<?php echo $setting_current_year; ?>";
                var $members = document.getElementById('members');
                var showAllGroupName = "All Current Members";

                window.onload = function init() {
                    normalizeMembers( members );

                    var filteredMembers = updateMembers( members, 'council_serving_years', _current_year );

                    $members.innerHTML = renderMembersGroup(showAllGroupName, filteredMembers, 'council_serving_years', _current_year );
                    fixMemberBoxes();
                };

                function actionChangeCouncil( elem ) {
                    var filteredMembers;

                    if( elem.value == 'byCollege' ) {
                        filteredMembers = updateMembers( members, 'council_serving_years', _current_year );
                        var membersByCollege = groupMembersByCollege( filteredMembers );

                        $members.innerHTML = '';

                        var colleges = Object.keys(membersByCollege).sort();

                        for( var i = 0; i < colleges.length; i++ ) {
                            var college = colleges[ i ];
                            $members.innerHTML += renderMembersGroup( college, membersByCollege[college], 'council_serving_years', _current_year);
                        }
                    } else {

                        var groupName = '';

                        switch( elem.value ) {
                            case "council_serving_years":
                                groupName = showAllGroupName;
                                break;
                            case "appeals_serving_years":
                                groupName = "Appeals Council Members";
                                break;
                            case "curriculum_serving_years":
                                groupName = "Curriculum Council Members";
                                break;
                            case "policy_serving_years":
                                groupName = "Policy Council Members";
                                break;
                            case "program_serving_years":
                                groupName = "Program Review and Awards Committee Members";
                                break;
                        }


                        filteredMembers = updateMembers( members, elem.value, _current_year );
                        $members.innerHTML = renderMembersGroup( groupName, filteredMembers, elem.value, _current_year  );
                    }

                    fixMemberBoxes();
                }
            </script>
            <?php get_sidebar( 'content-bottom' ); ?>
        </div>
    </div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
