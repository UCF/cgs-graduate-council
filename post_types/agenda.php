<?php
namespace {
    if (!defined('ABSPATH')) // wp-config.php defines an ABSPATH variable, this trap ensures that only WP can use this code.
        exit;
}

namespace agenda_type{


    if (!function_exists('agenda_type\new_post_type_Agenda')) {
        add_action('init', 'agenda_type\new_post_type_Agenda');
        add_action('admin_init', 'agenda_type\plugin_meta_box'); // admin_init is triggered before any other hook when a user accesses the admin area.
        add_action('admin_enqueue_scripts', 'agenda_type\plugin_admin_scripts');
        add_action('save_post', 'agenda_type\plugin_save_post', 10, 2);

        function plugin_admin_scripts()
        {
            global $post_type;

            if ( 'gs_agenda' != $post_type )
                return;

            wp_enqueue_media();

            wp_register_style( 'pikaday', get_template_directory_uri() . '/vendor/pikaday/css/pikaday.css', false, '1.0.0' );
            wp_enqueue_style( 'pikaday' );

            wp_register_script( 'moment', get_template_directory_uri() . '/vendor/moment/moment.js', false, '2.14.1' );
            wp_enqueue_script( 'moment' );

            wp_register_script( 'pikaday', get_template_directory_uri() . '/vendor/pikaday/pikaday.js', false, '1.4.0' );
            wp_enqueue_script( 'pikaday' );
        }

        function new_post_type_Agenda()
        {
            $labels = array(
                'name' => __('Agenda'),
                'singular_name' => __('Agenda Notes'),
                'add_new' => 'Add New Agenda',
                'edit_item' => 'Edit Agenda',
                'view_item' => 'View Agenda',
                'search_items' => 'Search Agenda',
                'not_found' => 'No Agenda found',
                'not_found_in_trash' => 'No Agenda found in Trash',
                'parent' => 'Parent Agenda'
            );

            register_post_type('gs_agenda',
                array(
                    'labels' => $labels,
                    'description' => 'Agenda details',
                    'public' => true,
                    'has_archive' => true,
                    'rewrite' => array('slug' => 'Agenda'),
                    'supports' => array( 'page-attributes' ),
                    'show_in_menu' => true,
                    'menu_icon' => get_template_directory_uri() . '/images/images-alt2.svg',
                    'menu_position' => 60,
                )
            );
        }

        function plugin_meta_box() {
            add_meta_box(
                'gs_agenda',                       // is the required HTML id attribute
                'Agenda Details',                  // is the text visible in the heading of the meta box section
                'agenda_type\plugin_display_details_meta_box',  // is the callback which renders the contents of the meta box
                'gs_agenda',                        // is the name of the custom post type where the meta box will be displayed
                'normal',                           // defines the part of the page where the edit screen section should be shown
                'high'                              // defines the priority within the context where the boxes should show
            );
            add_meta_box(
                'gs_agenda_details',                       // is the required HTML id attribute
                'Additional Information',                  // is the text visible in the heading of the meta box section
                'agenda_type\plugin_display_side_meta_box',  // is the callback which renders the contents of the meta box
                'gs_agenda',                        // is the name of the custom post type where the meta box will be displayed
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
            $data['file_url']   = valueFromMeta( $meta, 'file_url' );

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
            if ($post->post_type == 'gs_agenda') {
                $date = save_field( $id, 'date', 'date');
                $council = save_field( $id, 'council', 'council');
                save_field( $id, 'file_url', 'file_url');

                $months = array( '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec' );
                switch( $council ) {
                    case "council_serving_years":
                        $council = "Graduate Council";
                        break;
                    case "curriculum_serving_years":
                        $council = "Curriculum";
                        break;
                    case "policy_serving_years":
                        $council = "Policy and Procedures";
                        break;
                    case "appeals_serving_years":
                        $council = "Appeals and Awards";
                        break;
                    case "program_serving_years":
                        $council = "Program Review";
                        break;
                    case "":
                        $council = "";
                        break;
                }

                $date = explode( '/', trim($date) );

                if( count( $date ) != 3 )
                    return;

                save_field( $id, 'date_chrono', 'date_chrono', $date[2]. $date[0]. $date[1] );

                remove_action( 'save_post', 'agenda_type\plugin_save_post'  );

                wp_update_post(array(
                    'ID' => $id,
                    'post_title' => $date[2] . ' ' . $months[$date[0]] . ' ' . $date[1] . ' - '. $council,
                    'post_name' => $council . '-' . $date[ 2 ] . $date[ 0 ] . $date[ 1 ],
                ));

                add_action( 'save_post', 'agenda_type\plugin_save_post'  );
            }
        }

        function plugin_display_details_meta_box($post) {
            $data = file_data( $post->ID );

            ?>
            <div>
                <style>
                    #Agenda_details_table th {
                        text-align: right;
                        padding: 0  10px 0 25px;
                        width: .1%;
                        white-space: nowrap;
                    }
                    #Agenda_details_table input[type=text] {
                        width: 100%;
                    }
                    #Agenda_details_table input[type=datetime] {
                        width: 100%;
                    }
                    #Agenda_details_table select {
                        width: 100%;
                    }
                    #Agenda_details_table input[type=checkbox] {
                        margin-left: 1px;
                    }
                </style>
                <table id="Agenda_details_table" width="100%">
                    <tr>
                        <th><label for="Agenda-date">Meeting Date:</label></th>
                        <td>
                            <input id="Agenda-date" name="date" type="text" value="<?php echo $data['date']; ?>">
                        </td>
                        <th><label for="agenda-file">File:</label></th>
                        <td>
                            <input type="text" name="file_url" id="agenda-file" value="<?php echo $data['file_url'] ?>"/>
                        </td>
                        <td style="width: 1%">
                            <input type="button" id="meta-file-button" class="button" value="Choose or Upload a File"/>
                        </td>
                    </tr>
                </table>
                <script>
                    var picker = new Pikaday({
                        field: document.getElementById('Agenda-date'),
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
                            <option value="policy_serving_years" <?php if( 'policy_serving_years' == $data['council'] ) { echo "selected"; } ?>>Policy and Procedures</option>
                            <option value="appeals_serving_years" <?php if( 'appeals_serving_years' == $data['council'] ) { echo "selected"; } ?>>Appeals and Awards</option>
                            <option value="program_serving_years" <?php if( 'program_serving_years' == $data['council'] ) { echo "selected"; } ?>>Program Review</option>
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
    }
}
