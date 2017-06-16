<?php
/**
 * Template Name: Council Homepage
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
get_header(); ?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        $title = '';
        function roleDetails( $role ) {
            $parts = explode( ' ', $role );
            $year = $parts[0];
            $parts[0] = '';

            return array(
                'year' => $year,
                'title' => trim( implode( ' ', $parts ) )
            );
        }
        function title( $roles, $year ) {
            $r = '';
            $roles_split = explode( ',', $roles );
            for( $i = 0; $i < count( $roles_split ); $i++ ) {
                $role = $roles_split[ $i ];
                $roleDetails = roleDetails( $role );
                if( $roleDetails['year'] == $year && strpos( strtolower( $roleDetails['title']), 'member' ) === false ) {
                    $r .= $roleDetails['title'];
                }
            }
            return $r;
        }
        function titles( $meta, $key, $group, $year ){
            $r = '';
            if( !empty( $meta[ $key ] ) ) {
                $membership = explode( ',', $meta[ $key ][0] );
                for( $i = 0; $i < count( $membership ); $i++ ) {
                    $roleDetails = roleDetails( $membership[$i] );
                    if( $roleDetails['year'] == $year) {
                        $r .= '<ul class="memberSub">';
                        $r .= '<li>' . $group;
                        if( strtolower( trim( $roleDetails['title'] ) ) != 'member' ) :
                        $r .= '( '. $roleDetails['title'] .' )';
                        endif;
                        $r .= '</li>';
                        $r .= '</ul>';
                    }
                }

            }
            return $r;
        }
        function renderMember( $details, $settings, $year ) {
            $post = get_post();
            $setting_committee = $settings['committee'][0];

            $highest_rank = title( $details[ $setting_committee ][0], $year );

            $r =    '<div class="memberBox">' .
                    '<div class="memberName">' .
                    '<a href="mailto:'. $details['email'][0] .'">'.
                        $details['first_name'][0] . '&nbsp;' . $details['last_name'][0].
                    '</a>';

            if( !empty( $details['faculty_senate_steering_committee_member'][0] ) ) { $r .= '<sup style="font-weight: normal"> ‡</sup>'; }
            if( !empty( $details['faculty_senate_member'][0] ) ) { $r .= ' *'; }

            $r .=   '</div>';


            $r .=   '<div class="memberRank">' . $highest_rank . '</div>';

            $r .= '<div class="memberCollege">'.
                $details['college'][0] .
                '</div>';


            if( !empty(  $settings['show_committees'][0] ) ) {
                if( $setting_committee != 'curriculum_serving_years' )
                    $r .= titles( $details, 'curriculum_serving_years', 'Curriculum', $year );
                if( $setting_committee != 'appeals_serving_years' )
                    $r .= titles( $details, 'appeals_serving_years', 'Appeals and Awards', $year );
                if( $setting_committee != 'policy_serving_years' )
                    $r .= titles( $details, 'policy_serving_years', 'Policy and Procedures', $year );
                if( $setting_committee != 'program_serving_years' )
                    $r .= titles( $details, 'program_serving_years', 'Program Review', $year );
            }

            if ( $url = get_edit_post_link( $post->ID ) ) {
                $r .= '<a href="' . esc_url( $url ) . '">Edit Member</a>';
            }
            $r .= '</div>';

            return array(
                'title' => strtolower( $highest_rank ),
                'card' => $r
            );
        }
        function getFileUrl( $gs_file_id ) {
            $meta = get_post_meta( $gs_file_id );

            return esc_html( (( !empty( $meta['file_url']  ) )? $meta['file_url'][0]  : '') ) ;
        }

        $setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
        $setting_committee = '';
        $page_meta_settings = array();


        while ( have_posts() ) : the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $page_meta_settings     = get_post_meta( $id );
            $setting_committee      = valueFromMeta( $page_meta_settings, 'committee' );
            $setting_hasMinutes     = valueFromMeta( $page_meta_settings, 'hasMinutes' );
            $setting_hasAgenda      = valueFromMeta( $page_meta_settings, 'hasAgenda' );
            $setting_hasSubmitDate  = valueFromMeta( $page_meta_settings, 'hasSubmitDate' );

            $setting_meetingArchiveSlug    = valueFromMeta( $page_meta_settings, 'meetingSlug' );
            $setting_membersArchiveSlug    = valueFromMeta( $page_meta_settings, 'membersSlug' );
            ?>
            <div class="content-tile">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <p>
                <?php the_content(); ?>
            </p>
            </div>
            <?php
        endwhile;
        wp_reset_query();

        ?>
        <div class="content-tile">
            <?php
            $args = array(
                'post_type' => 'gs_meetings',
                'posts_per_page' => -1,
                'meta_query' =>
                    array(
                        array(
                            'key'       => 'council',
                            'value'     => $setting_committee,
                            'compare'   => '=',
                        )
                    ),
            );
            ?>
            <div style="float: right; padding-top: 15px;">
                <a style="font-size: 16px;" href="/<?php echo $setting_meetingArchiveSlug; ?>">Previous Meetings</a>
            </div>
            <h2><?php echo $title; ?> Meeting Schedule</h2>
            <table class="meeting-table">
                <thead>
                <tr>
                    <?php
                    echo '<th>Meeting Date and Time</th>';
                    if( !empty($setting_hasSubmitDate) )
                        echo '<th>Submit Date</th>';
                    if( !empty($setting_hasAgenda))
                        echo '<th>Agenda</th>';
                    if( !empty($setting_hasMinutes) )
                        echo '<th>Minutes</th>';
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php

                $colCount = 1;
                if( !empty( $setting_hasSubmitDate ) )  $colCount++;
                if( !empty( $setting_hasAgenda ) )      $colCount++;
                if( !empty( $setting_hasMinutes ) )     $colCount++;

                $setting_current_years = explode( '-', $setting_current_year );

                $setting_first_year = $setting_current_years[ 0 ];
                $setting_last_year  = $setting_current_years[ 1 ];

                $cutDate        = '8/15/2017';
                $cutDateParts   = explode( '/', $cutDate );
                $cutMonth       = $cutDateParts[ 0 ];
                $cutDay         = $cutDateParts[ 1 ];

                $firstCutDate = mktime( 0, 0, 0, $cutMonth, $cutDay, $setting_first_year );
                $lastCutDate = mktime( 0, 0, 0, $cutMonth, $cutDay, $setting_last_year );

                $meetings = array();
                $query_meetings = new WP_Query( $args );
                if($query_meetings->have_posts()):
                    while ($query_meetings->have_posts()):
                        $query_meetings->the_post();

                        $meta = get_post_meta( $post->ID );

                        $date = explode( '/', $meta['date'][0] );
                        $stamp = mktime( 0, 0, 0, $date[0], $date[1], $date[2] );

                        if( $firstCutDate < $stamp && $stamp < $lastCutDate ) {
                            $meeting = array(
                                'id'            => $post->id,
                                'date'          => $date,
                                'stamp'         => $stamp,
                                'meeting'       => $meta['date'][0] . ' ' . str_replace('0','',$meta['hour'][0] ) .':'.$meta['minutes'][0].' '.$meta['meridiem'][0] . ' - ' . $meta['location'][0],
                                'location'      => valueFromMeta( $meta, 'location' ),
                                'deadline'      => valueFromMeta( $meta, 'deadline' ),
                                'agenda_id'     => valueFromMeta( $meta, 'agenda_id' ),
                                'minutes_id'    => valueFromMeta( $meta, 'minutes_id' )
                            );

                            $meetings[] = $meeting;
                        }
                    endwhile;
                endif;
                wp_reset_query();
				
				function stamp_comparator ( $a, $b ) {
					return strcmp( $a["stamp"], $b["stamp"] ) * -1;
				}
				
				usort( $meetings, "stamp_comparator" );
				
                if( !count( $meetings )) {
                    echo '<tr><td colspan="' . $colCount . '">No meetings have been scheduled as of yet.</td></tr>';
                } else {

                    foreach( $meetings as $meeting ) {
                        ?>
                        <tr>
                            <?php
                            echo '<td>' . $meeting['meeting'] . ' ';
                            edit_post_link('Edit Meeting');
                            echo '</td>';

                            if( !empty($setting_hasSubmitDate) )
                                echo '<td>' . $meeting['deadline'] . '</td>';

                            if( !empty($setting_hasAgenda)) {

                                echo '<td>';
                                if( !empty( $meeting['agenda_id'] ) && $meeting['agenda_id'] != 0 )
                                    echo '<a href="'. getFileUrl( $meeting['agenda_id'] ) .'">Agenda</a>';

                                echo '</td>';

                            }

                            if( !empty($setting_hasMinutes) ) {

                                echo '<td>';
                                if( !empty( $meeting['minutes_id'] ) && $meeting['minutes_id'] != 0 )
                                    echo '<a href="'. getFileUrl( $meeting['minutes_id'] ) .'">Minutes</a>';

                                echo '</td>';

                            }
                            ?>
                        </tr>
                    <?php
                    }
                }
                ?>
                </tbody>
            </table>

            <div style="float: right; padding-top: 15px;">
                <a style="font-size: 16px;" href="/<?php echo $setting_membersArchiveSlug; ?>">Previous Members</a>
            </div>
            <h2><?php echo $title; ?> Members</h2>
			<div class="memberRank">‡ denotes a faculty senate steering committee member</div>
            <div class="memberRank">* denotes a faculty senate member</div>
            <div>
                <?php
                $args = array(
                    'post_type' => 'gs_member',
                    'posts_per_page' => -1,
                    'meta_query' =>
                        array(
                            array(
                                'key'       => $setting_committee,
                                'value'     => $setting_current_year,
                                'compare'   => 'LIKE',
                            )
                        ),
                );
                ?>
                <div>
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
                if($query_members->have_posts()):
                    while ($query_members->have_posts()):
                        $query_members->the_post();

                        $meta = get_post_meta( $post->ID );
                        array_push( $members, renderMember( $meta, $page_meta_settings, $setting_current_year ) );

                    endwhile;
                endif;
                wp_reset_query();

                for( $member_order_counter = 0; $member_order_counter < count( $member_order ); $member_order_counter++ ) {
                    $order = $member_order[ $member_order_counter ];
                    for( $i = 0; $i < count( $members ); $i++ ) {
                        $member = $members[ $i ];

                        if( $member['title'] === $order )
                            echo $member['card'];
                    }
                }
                ?>
                </div>
                <div style="clear: both;"></div>

                <div>
                    <?php edit_post_link('Edit Page');?>
                </div>
            </div>
        </div>
    </main><!-- .site-main -->

    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
