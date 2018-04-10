<?php
namespace {
    if (!defined('ABSPATH')) // wp-config.php defines an ABSPATH variable, this trap ensures that only WP can use this code.
        exit;
}

namespace minutes_type{


    if (!function_exists('minutes_type\new_post_type_minutes')) {
        add_action('wp_enqueue_scripts', 'minutes_type\plugin_scripts', 0); // action, array, priority ( 0 lowest, 10 normal, 10+ higher)
        add_action('init', 'minutes_type\new_post_type_minutes');
        add_action('admin_init', 'minutes_type\plugin_meta_box'); // admin_init is triggered before any other hook when a user accesses the admin area.
        add_action('admin_enqueue_scripts', 'minutes_type\plugin_admin_scripts');
        add_action('save_post', 'minutes_type\plugin_save_post', 10, 2);

        function plugin_admin_scripts()
        {
            global $post_type;

            if ( 'gs_minutes' != $post_type )
                return;

            wp_enqueue_media();

            wp_register_style( 'pikaday', get_template_directory_uri() . '/vendor/pikaday/css/pikaday.css', false, '1.0.0' );
            wp_enqueue_style( 'pikaday' );

            wp_register_script( 'moment', get_template_directory_uri() . '/vendor/moment/moment.js', false, '2.14.1' );
            wp_enqueue_script( 'moment' );

            wp_register_script( 'pikaday', get_template_directory_uri() . '/vendor/pikaday/pikaday.js', false, '1.4.0' );
            wp_enqueue_script( 'pikaday' );
        }

        function new_post_type_minutes()
        {
            $labels = array(
                'name' => __('Minutes'),
                'singular_name' => __('Minutes Notes'),
                'add_new' => 'Add New Minutes',
                'add_new_item' => 'Add New Minutes',
                'edit_item' => 'Edit Minutes',
                'view_item' => 'View Minutes',
                'search_items' => 'Search Minutes',
                'not_found' => 'No Minutes found',
                'not_found_in_trash' => 'No Minutes found in Trash',
                'parent' => 'Parent Minutes'
            );

            register_post_type('gs_minutes',
                array(
                    'labels' => $labels,
                    'description' => 'Minutes details',
                    'public' => true,
                    'has_archive' => true,
                    'rewrite' => array('slug' => 'minutes'),
                    'supports' => array( 'title', 'page-attributes' ),
                    'show_in_menu' => true,
                    'menu_icon' => get_template_directory_uri() . '/images/images-alt2.svg',
                    'menu_position' => 60,
                )
            );
        }

        function plugin_meta_box() {
            add_meta_box(
                'gs_minutes',                       // is the required HTML id attribute
                'Minutes Details',                  // is the text visible in the heading of the meta box section
                'minutes_type\plugin_display_details_meta_box',  // is the callback which renders the contents of the meta box
                'gs_minutes',                        // is the name of the custom post type where the meta box will be displayed
                'normal',                           // defines the part of the page where the edit screen section should be shown
                'high'                              // defines the priority within the context where the boxes should show
            );
            add_meta_box(
                'gs_minutes_details',                       // is the required HTML id attribute
                'Additional Information',                  // is the text visible in the heading of the meta box section
                'minutes_type\plugin_display_side_meta_box',  // is the callback which renders the contents of the meta box
                'gs_minutes',                        // is the name of the custom post type where the meta box will be displayed
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

            $data['council']    = valueFromMeta( $meta, 'council' );
            $data['date']       = valueFromMeta( $meta, 'date' );
            $data['members']    = valueFromMetaArray( $meta, 'members' );
            $data['recorder']   = valueFromMeta( $meta, 'recorder' );
            $data['guests']   = valueFromMeta( $meta, 'guests' );
            $data['staff']   = valueFromMeta( $meta, 'staff' );
            $data['minutes']   = valueFromMeta( $meta, 'minutes' );

            return $data;
        }
        function save_field($id, $post_key, $meta_key, $default = '') {
            if (isset($_POST[$post_key]) && $_POST[$post_key] != '') {
                update_post_meta($id, $meta_key, $_POST[$post_key]);
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
            if ($post->post_type == 'gs_minutes') {
                $council = save_field( $id, 'council', 'council');
                $date = save_field( $id, 'date', 'date');
                save_array( $id, 'members', 'members');
                save_field( $id, 'recorder', 'recorder');
                save_field( $id, 'guests', 'guests');
                save_field( $id, 'staff', 'staff');
                save_field( $id, 'minutes', 'minutes');


                $months = array( '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec' );
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

                $date = explode( '/', trim($date) );

                if( count( $date ) != 3 )
                    return;

                save_field( $id, 'date_chrono', 'date_chrono', $date[2]. $date[0]. $date[1] );

                remove_action( 'save_post', 'minutes_type\plugin_save_post'  );

                wp_update_post(array(
                    'ID' => $id,
                    'post_title' => $date[2] . ' ' . $months[$date[0]] . ' ' . $date[1] . ' - '. $council,
                    'post_name' => $council . '-' . $date[ 2 ] . $date[ 0 ] . $date[ 1 ],
                ));

                add_action( 'save_post', 'minutes_type\plugin_save_post'  );
            }
        }
        function members( $category, $selected ) {
            $args = array(
                'post_type' => 'gs_member',
                'posts_per_page' => -1,
                'meta_query' =>
                    array(
                        array(
                            'key'       => $category,
                            'value'     => '',
                            'compare'   => '!=',
                        )
                    ),
            );

            if( empty( $category ) )
                unset( $args['meta_query'] );

            $members = array();
            $name_sort_array = array();
            $query_members = new \WP_Query( $args ); // WP_Query is in the global namespace and needs to be referenced with a forward slash

            if($query_members->have_posts()):
                while ($query_members->have_posts()):
                    $query_members->the_post();

                    $meta = get_post_meta( get_the_ID() );

                    $name = $meta['first_name'][0] . ' ' . $meta['last_name'][0];
                    $id = get_the_ID();
                    $is_selected = in_array( $id, $selected );
                    array_push( $members, array( 'id' => $id, 'name' => $name, 'selected' => $is_selected ) );
                    array_push( $name_sort_array, $name );

                endwhile;
            endif;
            $query_members->reset_postdata();

            // Sort by name
            array_multisort ( $name_sort_array, SORT_ASC, SORT_STRING, $members );

            return $members;
        }

        function plugin_display_details_meta_box($post) {
            $data = file_data( $post->ID );

            $members = members( $data['council'], $data['members'] );


            ?>
            <div>
                <style>
                    #minutes_details_table th {
                        text-align: right;
                        padding: 0  10px 0 25px;
                        width: 1%;
                        white-space: nowrap;
                    }
                    #minutes_details_table input[type=text] {
                        width: 100%;
                    }
                    #minutes_details_table input[type=datetime] {
                        width: 100%;
                    }
                    #minutes_details_table select {
                        width: 100%;
                    }
                    #minutes_details_table input[type=checkbox] {
                        margin-left: 1px;
                    }
                </style>
                <table id="minutes_details_table" width="100%">
                    <tr>
                        <th><label for="minutes-date">Meeting Date:</label></td>
                        <td>
                            <input id="minutes-date" name="date" type="text" value="<?php echo $data['date']; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="members">Members Present:</label></td>
                        <td>
                            <select id="members" name="members[]" multiple>
                                <?php
                                for($i = 0, $c = count($members); $i < $c; $i++ ) {
                                    $member = $members[$i];
                                    echo '<option value="'. $member[ 'id' ] .'" '. (($member['selected'])? 'selected': '') .'>'. $member['name'] .'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="recorder">Recorder:</label></td>
                        <td>
                            <input id="recorder" name="recorder" type="text" value="<?php echo $data['recorder']; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="guests">Guests Present:</label></td>
                        <td>
                            <input id="guests" type="text" name="guests" value="<?php echo $data['guests']; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="staff">Staff Members Present:</label></td>
                        <td>
                            <input id="staff" name="staff" type="text" value="<?php echo $data['staff']; ?>">
                        </td>
                    </tr>
                    <tr colspan="2">
                        <td><label for="minutes">Minutes</label></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php wp_editor($data['minutes'],'minutes'); ?>
                        </td>
                    </tr>
                </table>
                <script>
                    var picker = new Pikaday({
                        field: document.getElementById('minutes-date'),
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
                            <option value="council_serving_years" <?php if( 'council_serving_years' == $data['council'] ) { echo "selected"; } ?>>Graduate Council</option>
                            <option value="curriculum_serving_years" <?php if( 'curriculum_serving_years' == $data['council'] ) { echo "selected"; } ?>>Curriculum</option>
                            <option value="policy_serving_years" <?php if( 'policy_serving_years' == $data['council'] ) { echo "selected"; } ?>>Policy</option>
                            <option value="appeals_serving_years" <?php if( 'appeals_serving_years' == $data['council'] ) { echo "selected"; } ?>>Appeals</option>
                            <option value="program_serving_years" <?php if( 'program_serving_years' == $data['council'] ) { echo "selected"; } ?>>Program Review and Awards</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Update the Council, and save a draft to get a condensed list of council members.
                    </td>
                </tr>
            </table>
            <?php

        }

        function plugin_scripts()
        {
            if (!is_admin()) {
                //wp_deregister_script('jquery');
                //wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"), false, '1.11.3');
                wp_enqueue_script('jquery');
            }
        }
    }
}
