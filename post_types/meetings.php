<?php
namespace {
    if (!defined('ABSPATH')) // wp-config.php defines an ABSPATH variable, this trap ensures that only WP can use this code.
        exit;
}

namespace meetings_type{


    if (!function_exists('meetings_type\new_post_type_meetings')) {
        add_action('wp_enqueue_scripts', 'meetings_type\plugin_scripts', 0); // action, array, priority ( 0 lowest, 10 normal, 10+ higher)
        add_action('init', 'meetings_type\new_post_type_meetings');
        add_action('admin_init', 'meetings_type\plugin_meta_box'); // admin_init is triggered before any other hook when a user accesses the admin area.
        add_action('admin_enqueue_scripts', 'meetings_type\plugin_admin_scripts');
        add_action('save_post', 'meetings_type\plugin_save_post', 10, 2);

        function plugin_admin_scripts()
        {
            global $post_type;

            if ( 'gs_meetings' != $post_type )
                return;

            wp_enqueue_media();

            wp_register_style( 'pikaday', get_template_directory_uri() . '/vendor/pikaday/css/pikaday.css', false, '1.0.0' );
            wp_enqueue_style( 'pikaday' );

            wp_register_script( 'moment', get_template_directory_uri() . '/vendor/moment/moment.js', false, '2.14.1' );
            wp_enqueue_script( 'moment' );

            wp_register_script( 'pikaday', get_template_directory_uri() . '/vendor/pikaday/pikaday.js', false, '1.4.0' );
            wp_enqueue_script( 'pikaday' );

        }

        function new_post_type_meetings()
        {
            $labels = array(
                'name' => __('Meetings'),
                'singular_name' => __('Meeting'),
                'add_new' => 'Add New Meeting',
                'add_new_item' => 'Add New Meeting',
                'edit_item' => 'Edit Meetings',
                'view_item' => 'View Meetings',
                'search_items' => 'Search Meetings',
                'not_found' => 'No Meetings found',
                'not_found_in_trash' => 'No Meetings found in Trash',
                'parent' => 'Parent Meetings'
            );

            register_post_type('gs_meetings',
                array(
                    'labels' => $labels,
                    'description' => 'Meetings details',
                    'public' => true,
                    'has_archive' => true,
                    'rewrite' => array('slug' => 'meetings'),
                    'supports' => array( 'title', 'page-attributes' ),
                    'show_in_menu' => true,
                    'menu_icon' => get_template_directory_uri() . '/images/images-alt2.svg',
                    'menu_position' => 60,
                )
            );
        }

        function plugin_meta_box() {
            add_meta_box(
                'gs_meetings',                       // is the required HTML id attribute
                'Meeting Details',                  // is the text visible in the heading of the meta box section
                'meetings_type\plugin_display_details_meta_box',  // is the callback which renders the contents of the meta box
                'gs_meetings',                        // is the name of the custom post type where the meta box will be displayed
                'normal',                           // defines the part of the page where the edit screen section should be shown
                'high'                              // defines the priority within the context where the boxes should show
            );
            add_meta_box(
                'gs_meetings_details',                       // is the required HTML id attribute
                'Additional Information',                  // is the text visible in the heading of the meta box section
                'meetings_type\plugin_display_side_meta_box',  // is the callback which renders the contents of the meta box
                'gs_meetings',                        // is the name of the custom post type where the meta box will be displayed
                'side',                           // defines the part of the page where the edit screen section should be shown
                'high'                              // defines the priority within the context where the boxes should show
            );
        }
        function valueFromMeta( $meta, $key ) {
            if( !empty( $meta[ $key ] ) )
                return $meta[ $key ][0];
            else
                return '';
        }
        function valueFromMetaArray( $meta, $key ) {
            if( !empty( $meta[ $key ] ) )
                return explode( ',', $meta[ $key ][0] );
            else
                return array();
        }
        function file_data( $id ) {
            $meta   = get_post_meta( $id );
            $data  = array();

            $data['council']        = valueFromMeta( $meta, 'council' );
            $data['date']           = valueFromMeta( $meta, 'date' );
            $data['hour']           = valueFromMeta( $meta, 'hour' );
            $data['minutes']        = valueFromMeta( $meta, 'minutes' );
            $data['meridiem']       = valueFromMeta( $meta, 'meridiem' );
            $data['location']       = valueFromMeta( $meta, 'location' );
            $data['deadline']       = valueFromMeta( $meta, 'deadline' );
            $data['minutes_id']     = valueFromMeta( $meta, 'minutes_id' );
            $data['agenda_id']      = valueFromMeta( $meta, 'agenda_id' );

            return $data;
        }
        function save_field($id, $post_key, $meta_key, $default = '') {
            if (isset($_POST[$post_key]) && $_POST[$post_key] != '') {
                update_post_meta($id, $meta_key, trim($_POST[$post_key]));
                return $_POST[$post_key];
            } else {
                update_post_meta($id, $meta_key, $default);
                return $default;
            }
        }


        function save_array($id, $post_key, $meta_key) {
            if (isset($_POST[$post_key]) && is_array($_POST[$post_key]) && !empty($_POST[$post_key]))
                update_post_meta($id, $meta_key, implode( ',', $_POST[$post_key] ) );
        }

        function plugin_save_post($id, $post) {
            if ($post->post_type == 'gs_meetings') {
                $council = save_field( $id, 'council', 'council');
                // Wordpress 5.0 changed the date-picker-ui to a new format m/d/Y => "Weekday NiceMonth Day Year".
                $date = trim( $_POST['date'] );
                $oldDateFormat = date( 'm/d/Y', strtotime( $date ) );
                update_post_meta( $id, 'date', $oldDateFormat );


                save_field( $id, 'hour', 'hour');
                save_field( $id, 'minutes', 'minutes');
                save_field( $id, 'meridiem', 'meridiem');
                save_field( $id, 'location', 'location');
                save_field( $id, 'deadline', 'deadline');
                save_field( $id, 'minutes_id', 'minutes_id');
                save_field( $id, 'agenda_id', 'agenda_id');

                $months = array( '00' => '??', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec' );
                switch( $council ) {
                    case "council_serving_years":
                        $council = "Graduate Council";
                        break;
                    case "curriculum_serving_years":
                        $council = "Curriculum";
                        break;
                    case "policy_serving_years":
                        $council = "Policy";
                        break;
                    case "appeals_serving_years":
                        $council = "Appeals";
                        break;
                    case "program_serving_years":
                        $council = "Program Review and Awards";
                        break;
                    case "":
                        $council = "";
                        break;
                }

                $date = explode( '/', $oldDateFormat );
                if( count( $date ) != 3 ) {
                    $date[0] = '00';
                    $date[1] = '??';
                    $date[2] = '??';
                }

                remove_action( 'save_post', 'meetings_type\plugin_save_post'  );
                wp_update_post(array(
                    'ID' => $id,
                    'post_title' => $council . ' - ' . $date[2] . ' ' . $date[0] . ' ' . $date[1],
                    'post_name' => $council . '-' . $date[ 2 ] . $date[ 0 ] . $date[ 1 ],
                ));
                add_action( 'save_post', 'meetings_type\plugin_save_post'  );

            }
        }

        function minutes( $council, $selected ) {
            $args = array(
                'post_type' => 'gs_file',
                'posts_per_page' => -1,
                'meta_key' => 'date',
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'meta_query' =>
                    array(
                        'relation' => 'AND',
                        array(
                            'key'       => 'document-type',
                            'value'     => 'minutes',
                            'compare'   => '=',
                        ),
                        array(
                            'key'       => 'committee',
                            'value'     => $council,
                            'compare'   => '=',
                        )
                    ),
            );

            if( empty( $council ) )
                unset( $args['meta_query'] );

            $minutes = array();
            $query_minutes = new \WP_Query( $args ); // WP_Query is in the global namespace and needs to be referenced with a forward slash

            if($query_minutes->have_posts()):
                while ($query_minutes->have_posts()):
                    $query_minutes->the_post();

                    $name = \get_the_title();
                    $id = \get_the_ID();
                    $is_selected = $id == $selected;
                    array_push( $minutes, array( 'id' => $id, 'name' => $name, 'selected' => $is_selected ) );
                endwhile;
            endif;
            $query_minutes->reset_postdata();

            return $minutes;
        }
        function agenda( $council, $selected ) {
            $args = array(
                'post_type' => 'gs_file',
                'posts_per_page' => -1,
                'meta_key' => 'date',
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'meta_query' =>
                    array(
                        'relation' => 'AND',
                        array(
                            'key'       => 'document-type',
                            'value'     => 'agenda',
                            'compare'   => '=',
                        ),
                        array(
                            'key'       => 'committee',
                            'value'     => $council,
                            'compare'   => '=',
                        ),
                        array(
                            'key'       => 'used',
                            'value'     => '0',
                            'compare'   => '!=',
                        )
                    ),
            );

            if( empty( $council ) )
                unset( $args['meta_query'] );

            $agendas = array();
            $query_agenda = new \WP_Query( $args ); // WP_Query is in the global namespace and needs to be referenced with a forward slash

            if($query_agenda->have_posts()):
                while ($query_agenda->have_posts()):
                    $query_agenda->the_post();

                    $name = \get_the_title();
                    $id = \get_the_ID();
                    $is_selected = $id == $selected;
                    array_push( $agendas, array( 'id' => $id, 'name' => $name, 'selected' => $is_selected ) );
                endwhile;
            endif;
            $query_agenda->reset_postdata();

            return $agendas;
        }


        function get_all_unused_file_postings_by_type( $current_ID, $file_type, $council, $type ) {
            global $wpdb;

            if( $current_ID  == '' )
                $current_ID = 0;

            $file_posting = $wpdb->get_results(
                "SELECT ID, post_title FROM wordpress_posts as post " .
                "LEFT JOIN wordpress_postmeta as meta " .
                "   ON post.ID = meta.post_id ".
                "WHERE 	post.post_type LIKE '%file%' " .
                "   AND meta.meta_key = 'document-type' ".
                "   AND meta.meta_value = '$file_type' ".
                "   AND post.post_status = 'publish' " .
                "   AND post.ID IN ( " .
                "       -- LIST OF ALL FILE POST IDS IN A COUNCIL \n".
                "       SELECT p.ID FROM wordpress_posts as p " .
                "       JOIN wordpress_postmeta as m ".
                "           ON p.ID = m.post_id ".
                "       WHERE p.post_type LIKE '%file%' ".
                "       AND p.post_status = 'publish' ".
                "       AND m.meta_key = 'committee' ".
                "       AND m.meta_value = '$council' ".
                "   ) " .
                "   AND ( post.ID NOT IN ( " .
                "       -- LIST OF USED IDs \n".
                "       SELECT m.meta_value FROM wordpress_posts as p " .
                "       JOIN wordpress_postmeta as m ".
                "           ON p.ID = m.post_id ".
                "       WHERE p.post_type LIKE '%meeting%' ".
                "       AND p.post_status = 'publish' ".
                "       AND m.meta_key = '$type' ".
                "       AND m.meta_value != '' ".
                " ) ".
                " OR post.ID IN ( $current_ID ) ". // The currently selected file is used by this post so include the ID too
                " ) "
            );

            $results = array();

            foreach( $file_posting as $post ) {
                array_push( $results, array(
                    'id' => $post->ID,
                    'selected' => $post->ID == $current_ID,
                    'title' => $post->post_title
                ) );
            }

            return $results;
        }

        function plugin_display_details_meta_box($post) {
            $data = file_data( $post->ID );
            $council = $data['council'];

            $minutes = get_all_unused_file_postings_by_type( $data['minutes_id'], 'minutes', $council, 'minutes_id');
            $agendas = get_all_unused_file_postings_by_type( $data['agenda_id'], 'agenda', $council, 'agenda_id');
            ?>
            <div>
                <style>
                    #meeting_details_table th {
                        text-align: right;
                        padding: 0  10px 0 25px;
                        width: 1%;
                        white-space: nowrap;
                    }
                    #meeting_details_table input[type=text] {
                        width: 100%;
                    }
                    #meeting_details_table input[type=text].minutes {
                        width: 50px;
                        height: 28px;
                        padding: 2px;
                    }
                    #meeting_details_table input[type=checkbox] {
                        margin-left: 1px;
                    }
                </style>
                <table id="meeting_details_table" width="100%">
                    <tr>
                        <th><label for="minutes-date">Meeting Date:</label></th>
                        <td>
                            <input id="minutes-date" name="date" type="text" value="<?php echo $data['date']; ?>" placeholder="mm/dd/yyyy">
                        </td>
                        <th><label>Time:</label></th>
                        <td>
                            <select name="hour">
                                <option <?php if( $data['hour'] == '01' ) { echo 'selected'; } ?>>01</option>
                                <option <?php if( $data['hour'] == '02' ) { echo 'selected'; } ?>>02</option>
                                <option <?php if( $data['hour'] == '03' ) { echo 'selected'; } ?>>03</option>
                                <option <?php if( $data['hour'] == '04' ) { echo 'selected'; } ?>>04</option>
                                <option <?php if( $data['hour'] == '05' ) { echo 'selected'; } ?>>05</option>
                                <option <?php if( $data['hour'] == '06' ) { echo 'selected'; } ?>>06</option>
                                <option <?php if( $data['hour'] == '07' ) { echo 'selected'; } ?>>07</option>
                                <option <?php if( $data['hour'] == '08' ) { echo 'selected'; } ?>>08</option>
                                <option <?php if( $data['hour'] == '09' ) { echo 'selected'; } ?>>09</option>
                                <option <?php if( $data['hour'] == '10' ) { echo 'selected'; } ?>>10</option>
                                <option <?php if( $data['hour'] == '11' ) { echo 'selected'; } ?>>11</option>
                                <option <?php if( $data['hour'] == '12' ) { echo 'selected'; } ?>>12</option>
                            </select>
                            <select name="minutes">
                                <option <?php if( $data['minutes'] == '00' ) { echo 'selected'; } ?>>00</option>
                                <option <?php if( $data['minutes'] == '05' ) { echo 'selected'; } ?>>05</option>
                                <option <?php if( $data['minutes'] == '10' ) { echo 'selected'; } ?>>10</option>
                                <option <?php if( $data['minutes'] == '15' ) { echo 'selected'; } ?>>15</option>
                                <option <?php if( $data['minutes'] == '20' ) { echo 'selected'; } ?>>20</option>
                                <option <?php if( $data['minutes'] == '25' ) { echo 'selected'; } ?>>25</option>
                                <option <?php if( $data['minutes'] == '30' ) { echo 'selected'; } ?>>30</option>
                                <option <?php if( $data['minutes'] == '35' ) { echo 'selected'; } ?>>35</option>
                                <option <?php if( $data['minutes'] == '40' ) { echo 'selected'; } ?>>40</option>
                                <option <?php if( $data['minutes'] == '45' ) { echo 'selected'; } ?>>45</option>
                                <option <?php if( $data['minutes'] == '50' ) { echo 'selected'; } ?>>50</option>
                                <option <?php if( $data['minutes'] == '55' ) { echo 'selected'; } ?>>55</option>
                            </select>
                            <select name="meridiem">
                                <option <?php if( $data['meridiem'] == 'am' ) { echo 'selected'; } ?>>am</option>
                                <option <?php if( $data['meridiem'] == 'pm' ) { echo 'selected'; } ?>>pm</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="location">Location:</label></th>
                        <td colspan="3"><input id="location" name="location" type="text" value="<?php echo $data['location']; ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="deadline">Agenda deadline:</label></th>
                        <td colspan="3"><input id="deadline" name="deadline" type="text" value="<?php echo $data['deadline']; ?>" placeholder="mm/dd/yyyy"></td>
                    </tr>
                    <tr>
                        <th><label for="agenda">Link to an Agenda</label></th>
                        <td>
                            <select id="agenda" name="agenda_id">
                                <option></option>
                                <?php
                                for($i = 0, $c = count($agendas); $i < $c; $i++ ) {
                                    $agenda = $agendas[$i];
                                    echo '<option value="'. $agenda[ 'id' ] .'" '. (($agenda['selected'])? 'selected': '') .'>'. $agenda['title'] .'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="minutes_id">Link to a Minutes</label></th>
                        <td>
                            <select id="minutes_id" name="minutes_id">
                                <option></option>
                                <?php
                                for($i = 0, $c = count($minutes); $i < $c; $i++ ) {
                                    $minute = $minutes[$i];
                                    echo '<option value="'. $minute[ 'id' ] .'" '. (($minute['selected'])? 'selected': '') .'>'. $minute['title'] .'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <script>
                    var picker = new Pikaday({
                        field: document.getElementById('minutes-date'),
                        format: 'L'
                    });
                    var picker2 = new Pikaday({
                        field: document.getElementById('deadline'),
                        format: 'L'
                    });
                </script>
            </div>
        <?php
        }

        function plugin_display_side_meta_box( $post ){
            $data = file_data( $post->ID );
            ?>
            <style>
                #additional-info select {
                    width: 100%;
                }
            </style>
            <table id="additional-info">
                <tr>
                    <td><label for="council"></label>Council:</td>
                    <td>
                        <select id="council" name="council">
                            <option <?php if( '' == $data['council'] ) { echo "selected"; } ?>></option>
                            <option value="curriculum_serving_years" <?php if( 'curriculum_serving_years' == $data['council'] ) { echo "selected"; } ?>>Curriculum</option>
                            <option value="policy_serving_years" <?php if( 'policy_serving_years' == $data['council'] ) { echo "selected"; } ?>>Policy</option>
                            <option value="appeals_serving_years" <?php if( 'appeals_serving_years' == $data['council'] ) { echo "selected"; } ?>>Appeals</option>
                            <option value="program_serving_years" <?php if( 'program_serving_years' == $data['council'] ) { echo "selected"; } ?>>Program Review and Awards</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php

        }

        function plugin_scripts()
        {
            if (!is_admin()) {
                wp_enqueue_script('jquery');
            }
        }

        // ---
        // This function adds meta data to the page template
        // Add the category meta box to the file template admin page.
        //
        function council_add_meta_boxes( $post ) {

            // Get the page template post meta
            $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
            // If the current page uses our specific
            // template, then output our custom metabox

            if ( 'page_meetings.php' == $page_template ) {
                add_meta_box(
                    'custom-metabox', // Metabox HTML ID attribute
                    'Meetings Settings', // Metabox title
                    'meetings_type\council_page_template_metabox', // callback name
                    'page', // post type
                    'side', // context (advanced, normal, or side)
                    'high' // priority (high, core, default or low)
                );
            }
            if ( 'page_meetings-archive.php' == $page_template ) {
                add_meta_box(
                    'custom-metabox', // Metabox HTML ID attribute
                    'Archive Settings', // Metabox title
                    'meetings_type\meetings_archive_page_template_metabox', // callback name
                    'page', // post type
                    'side', // context (advanced, normal, or side)
                    'high' // priority (high, core, default or low)
                );
            }
        }

        function council_page_template_metabox( $post ) {
            // Define the meta box form fields here
            $meta = get_post_meta( $post->ID );
            $committee    = valueFromMeta( $meta, 'committee' );
            ?>
            <div>
                <label for="committee">Committee:</label>
                <select id="committee" name="committee">
                    <option value="" <?php if( $committee == '' ) { echo 'selected'; } ?>></option>
                    <option value="appeals_serving_years" <?php if( $committee == 'appeals_serving_years' ) { echo 'selected'; } ?>>Appeals</option>
                    <option value="curriculum_serving_years" <?php if( $committee == 'curriculum_serving_years' ) { echo 'selected'; } ?>>Curriculum</option>
                    <option value="policy_serving_years" <?php if( $committee == 'policy_serving_years' ) { echo 'selected'; } ?>>Policy</option>
                    <option value="program_serving_years" <?php if( $committee == 'program_serving_years' ) { echo 'selected'; } ?>>Program Review and Awards</option>
                </select>
            </div>
        <?php
        }

        function meetings_archive_page_template_metabox( $post ) {
            // Define the meta box form fields here
            $meta = get_post_meta( $post->ID );
            $committee    = valueFromMeta( $meta, 'committee' );
            ?>
            <div>
                <label for="committee">Committee:</label>
                <select id="committee" name="committee">
                    <option value="" <?php if( $committee == '' ) { echo 'selected'; } ?>></option>
                    <option value="appeals_serving_years" <?php if( $committee == 'appeals_serving_years' ) { echo 'selected'; } ?>>Appeals</option>
                    <option value="curriculum_serving_years" <?php if( $committee == 'curriculum_serving_years' ) { echo 'selected'; } ?>>Curriculum</option>
                    <option value="policy_serving_years" <?php if( $committee == 'policy_serving_years' ) { echo 'selected'; } ?>>Policy</option>
                    <option value="program_serving_years" <?php if( $committee == 'program_serving_years' ) { echo 'selected'; } ?>>Program Review and Awards</option>
                </select>
            </div>
            <h3 class="setting-heading">Meeting Settings:</h3>
            <div>
                <input id="hasSubmitDate" name="hasSubmitDate" type="checkbox" <?php if( $meta['hasSubmitDate'][0] ) { echo "checked";} ?>>
                <label for="hasSubmitDate">Has Submit Date</label>
            </div>
            <div>
                <input id="hasAgenda" name="hasAgenda" type="checkbox" <?php if( $meta['hasAgenda'][0] ) { echo "checked";} ?>>
                <label for="hasAgenda">Has Agenda</label>
            </div>
            <div>
                <input id="hasMinutes" name="hasMinutes" type="checkbox" <?php if( $meta['hasMinutes'][0] ) { echo "checked";} ?>>
                <label for="hasMinutes">Has Minutes</label>
            </div>
        <?php
        }

        function council_save_custom_post_meta( $ID ) {
            $page_template = get_post_meta( $ID, '_wp_page_template', true );
            // If the current page uses our specific
            // template, then output our custom metabox

            if ( 'page_meetings.php' == $page_template ) {
                save_field($ID, 'committee', 'committee');
            }
            if ( 'page_meetings-archive.php' == $page_template ) {
                save_field($ID, 'committee',        'committee');
                save_field($ID, 'hasSubmitDate',    'hasSubmitDate');
                save_field($ID, 'hasAgenda',        'hasAgenda');
                save_field($ID, 'hasMinutes',       'hasMinutes');
            }
        }
        add_action( 'publish_page', 'meetings_type\council_save_custom_post_meta' );
        add_action( 'draft_page', 'meetings_type\council_save_custom_post_meta' );
        add_action( 'future_page', 'meetings_type\council_save_custom_post_meta' );
        add_action( 'add_meta_boxes_page', 'meetings_type\council_add_meta_boxes' );
    }
}
