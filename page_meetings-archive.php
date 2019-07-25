<?php
/**
 * Template Name: Meetings Archive
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

        $setting_current_year   = trim( esc_attr( get_option( 'current_year' ) ) );
        $setting_years          = trim( esc_attr( get_option( 'years' ) ) );
        $setting_committee = '';
        $page_meta_settings = array();

        $has_agenda = false;
        $has_minutes = false;

        $select_years = $setting_current_year;

        $matches = array();
        if( ISSET( $_GET['years'] ) && 1 == preg_match('/^\d+-\d+$/', $_GET['years'], $matches ) ) {
            $select_years = esc_html( $_GET['years'] );
		}


        function getFileUrl( $gs_file_id ) {
            $meta = get_post_meta( $gs_file_id );

            return esc_html( (( !empty( $meta['file_url']  ) )? $meta['file_url'][0]  : '') ) ;
        }


        while ( have_posts() ) : the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $page_meta_settings     = get_post_meta( $id );
            $setting_committee      = valueFromMeta( $page_meta_settings, 'committee' );
            $setting_hasMinutes     = valueFromMeta( $page_meta_settings, 'hasMinutes' );
            $setting_hasAgenda      = valueFromMeta( $page_meta_settings, 'hasAgenda' );
            $setting_hasSubmitDate  = valueFromMeta( $page_meta_settings, 'hasSubmitDate' );
            ?>
            <?php
        endwhile;
        wp_reset_query();

        ?>
        <div class="content-tile">
            <?php
            $args = array(
                'post_type' => 'gs_meetings',
                'posts_per_page' => -1,
                'post_status ' => 'published',
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
            <h2 id="meetings"><?php echo $title; ?></h2>
            <div class="col-xs-3">
                <h3><span>Year</span></h3>
                <ul class="list-no-bullet">
                <?php
                    $years = explode( ',', $setting_years );
                    foreach( $years as $year ) {
                        if( $select_years == $year )
                            echo '<li>' . $year . '</li>';
                        else
                            echo '<li><a href="?years=' . $year . '#meetings">' . $year . '</a></li>';
                    }
                ?>
                </ul>
            </div>
            <?php

            $colCount = 1;
            if( !empty( $setting_hasSubmitDate ) )  $colCount++;
            if( !empty( $setting_hasAgenda ) )      $colCount++;
            if( !empty( $setting_hasMinutes ) )     $colCount++;

            $setting_current_years = explode( '-', $select_years );

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
                            'id'            => $post->ID,
                            'date'          => $date,
                            'stamp'         => $stamp,
                            'meeting'       => $meta['date'][0] . ' ' . sprintf('%02d', $meta['hour'][0] ) .':'.$meta['minutes'][0].' '.$meta['meridiem'][0] . ' - ' . $meta['location'][0],
                            'location'      => valueFromMeta( $meta, 'location' ),
                            'deadline'      => valueFromMeta( $meta, 'deadline' ),
                            'agenda_id'     => valueFromMeta( $meta, 'agenda_id' ),
                            'minutes_id'    => valueFromMeta( $meta, 'minutes_id' )
                        );

                        $meetings[] = $meeting;

                        if( trim( $meeting['agenda_id'] ) != '' )
                            $has_agenda = true;

                        if( trim( $meeting['minutes_id'] ) != '' )
                            $has_minutes = true;
                    }
                endwhile;
            endif;
            ?>
            <div class="col-xs-9">
                <table class="meeting-table">
                    <thead>
                    <tr>
                        <?php
                        echo '<th>Meeting Date and Time</th>';
                        // Only display the “Submit Date” on the current year. There is no need to display this date on previous years.
                        if( !empty($setting_hasSubmitDate) && $select_years == $setting_current_year )
                            echo '<th>Submit Date</th>';
                        // If a column heading has no data for a specific year, do not display the column.
                        if( !empty($setting_hasAgenda) && $has_agenda )
                            echo '<th>Agenda</th>';
                        // If a column heading has no data for a specific year, do not display the column.
                        if( !empty($setting_hasMinutes) && $has_minutes )
                            echo '<th>Minutes</th>';
                        ?>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    wp_reset_query();

                    function stamp_comparator ( $a, $b ) {
                        return strcmp( $a["stamp"], $b["stamp"] ) * -1;
                    }
 
                    usort( $meetings, "stamp_comparator" );

                    if( !count( $meetings )) {
                        echo '<tr><td colspan="' . $colCount . '">No meetings have been found for ' . $select_years . '.</td></tr>';
                    } else {

                        foreach( $meetings as $meeting ) {
                            ?>
                            <tr>
                                <?php
                                echo '<td>'. $meeting['meeting'] . ' ';
                                edit_post_link('Edit Meeting', '', '', $meeting['id'] );
                                echo '</td>';

                                if( !empty($setting_hasSubmitDate) && $select_years == $setting_current_year )
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
            </div>
            <div style="clear: both"></div>
            <div>
                <?php edit_post_link('Edit Page');?>
            </div>
        </div>
    </main><!-- .site-main -->

    <?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
