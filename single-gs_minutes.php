<?php
get_header(); ?>
<style>
    body, td, th {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10pt;
        color: #000000;
    }
</style>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        global $post;

        $setting_current_year = trim( esc_attr( get_option( 'current_year' ) ) );
        $page_meta_settings = array();


        $months = array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );

        function membersFromIDs( $memberIDs ) {
            $preserve_post = get_post();
            $args = array(
                'post_type' => 'gs_member',
                'posts_per_page' => -1,
                'post__in' => $memberIDs,
            );

            $members = array();
            $query_members = new WP_Query( $args ); // WP_Query is in the global namespace and needs to be referenced with a forward slash

            if($query_members->have_posts()):
                while ($query_members->have_posts()):
                    $query_members->the_post();

                    $meta = get_post_meta( get_the_ID() );

                    $name = $meta['first_name'][0] . ' ' . $meta['last_name'][0];
                    array_push( $members, $name );

                endwhile;
            endif;
            $query_members->reset_postdata();

            sort( $members );

            $post = $preserve_post;

            return $members;
        }

        while ( have_posts() ) : the_post();
            $minutes_id = get_the_ID();
            $meta = get_post_meta( $minutes_id );

            $date = explode( '/', trim($meta['date'][0]) );
            $members = $meta['members'][0];
            ?>
            <h1><?php echo 'Minutes of '. $months[$date[0]] .' '. $date[1] .', '. $date[2] .' meeting'; ?></h1>
            <div class="minutesDetailBox">
               <table>
                    <tbody>
                        <tr>
                            <td class="minutesDetailRowTitle">Members Present</td>
                            <td class="minutesDetailRowContent">
                                <?php
                                    $members = membersFromIDs( explode( ',', $members ) );
                                    for( $i = 0, $c = count( $members ); $i < $c; $i++) {
                                        echo $members[ $i ];
                                        if( $i + 1 != $c )
                                            echo ',<br>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="minutesDetailRowTitle">Recorder</td>
                            <td class="minutesDetailRowContent"><?php echo $meta['recorder'][0]; ?></td>
                        </tr>
                        <tr>
                            <td class="minutesDetailRowTitle">Guests Present</td>
                            <td class="minutesDetailRowContent">
                                <?php echo $meta['guests'][0]; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="minutesDetailRowTitle">Files</td>
                            <td class="minutesDetailRowContent">
                                <a href="/uploadedFiles/Curriculum/Minutes/2015-2016/2016-03-23/2016-03-23 Meeting Graduate Certificate Report Minutes.pdf" target="_blank">2016-03-23 Meeting Graduate Certificate Report Minutes&nbsp;<img src="../../images/downloadBT.gif" alt="Download file" width="14" height="14" border="0"><br></a>
                                <a href="/uploadedFiles/Curriculum/Minutes/2015-2016/2016-03-23/2016-03-23 Meeting Course Minutes.pdf" target="_blank">2016-03-23 Meeting Course Minutes&nbsp;<img src="../../images/downloadBT.gif" alt="Download file" width="14" height="14" border="0"><br></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    <?php
                    if( !empty( $meta['minutes'][0] ) ) {
                        echo $meta['minutes'][0];
                    } else {
                        echo 'No minutes recorded';
                    }
                    ?>
                </p>
            </div>

        <?php
            edit_post_link('Edit Minutes', '', '', $minutes_id);
        endwhile;
        wp_reset_query();

        ?>

</main><!-- .site-main -->

<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
