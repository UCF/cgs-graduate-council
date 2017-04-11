<?php
namespace {
    if (!defined('ABSPATH')) // wp-config.php defines an ABSPATH variable, this trap ensures that only WP can use this code.
        exit;
}

namespace member_type{


    if (!function_exists('member_type\new_post_type_member')) {
        add_action('wp_enqueue_scripts', 'member_type\plugin_scripts', 0); // action, array, priority ( 0 lowest, 10 normal, 10+ higher)
        add_action('init', 'member_type\new_post_type_file_posting');
        add_action('admin_init', 'member_type\plugin_meta_box'); // admin_init is triggered before any other hook when a user accesses the admin area.
        add_action('admin_enqueue_scripts', 'member_type\plugin_admin_scripts');
        add_action('save_post', 'member_type\plugin_save_post', 10, 2);

        function plugin_admin_scripts($page)
        {
            if ('post.php' != $page && 'post-new.php' != $page) {
                return;
            }
            wp_enqueue_media();
        }

        function new_post_type_file_posting()
        {
            $labels = array(
                'name' => __('Members'),
                'singular_name' => __('Member'),
                'add_new' => 'Add New Member',
                'add_new_item' => 'Add New Member',
                'edit_item' => 'Edit Member',
                'view_item' => 'View Member',
                'search_items' => 'Search Members',
                'not_found' => 'No Members found',
                'not_found_in_trash' => 'No Members found in Trash',
                'parent' => 'Parent Member'
            );

            register_post_type('gs_member',
                array(
                    'labels' => $labels,
                    'description' => 'Member details',
                    'public' => true,
                    'has_archive' => true,
                    'rewrite' => array('slug' => 'member'),
                    'supports' => array( 'title', 'page-attributes' ),
                    'show_in_menu' => true,
                    'menu_icon' => get_template_directory_uri() . '/images/images-alt2.svg',
                    'menu_position' => 60,
                )
            );
            register_taxonomy(
                'graduate_council_serving_years',
                array( 'gs_member' ),
                array(
                    'labels' => array(
                        'name' => __( 'Graduate Council Serving Years' ),
                        'single' => __( 'Graduate Council Serving Year' ),
                    ),
                    'show_ui' => true,
                    'public' => false,
                )
            );
            register_taxonomy(
                'curriculum_serving_years',
                array( 'gs_member' ),
                array(
                    'labels' => array(
                        'name' => __( 'Curriculum Subcommittee Serving Years' ),
                        'single' => __( 'Curriculum Subcommittee Serving Year' ),
                    ),
                    'show_ui' => true,
                    'public' => false,
                )
            );
            register_taxonomy(
                'policy_serving_years',
                array( 'gs_member' ),
                array(
                    'labels' => array(
                        'name' => __( 'Policy and Procedures Subcommittee Serving Years' ),
                        'single' => __( 'Policy and Procedures Subcommittee Serving Year' ),
                    ),
                    'show_ui' => true,
                    'public' => false,
                )
            );
            register_taxonomy(
                'appeals_serving_years',
                array( 'gs_member' ),
                array(
                    'labels' => array(
                        'name' => __( 'Appeals and Awards Subcommittee Serving Years' ),
                        'single' => __( 'Appeals and Awards Subcommittee Serving Year' ),
                    ),
                    'show_ui' => true,
                    'public' => false,
                )
            );
            register_taxonomy(
                'program_serving_years',
                array( 'gs_member' ),
                array(
                    'labels' => array(
                        'name' => __( 'Program Review Subcommittee Serving Years' ),
                        'single' => __( 'Program Review Subcommittee Serving Year' ),
                    ),
                    'show_ui' => true,
                    'public' => false,
                )
            );
        }
        function remove_tax_meta_boxes () {
            remove_meta_box( 'tagsdiv-graduate_council_serving_years', 'gs_member', 'side' );
            remove_meta_box( 'tagsdiv-curriculum_serving_years', 'gs_member', 'side' );
            remove_meta_box( 'tagsdiv-policy_serving_years', 'gs_member', 'side' );
            remove_meta_box( 'tagsdiv-appeals_serving_years', 'gs_member', 'side' );
            remove_meta_box( 'tagsdiv-program_serving_years', 'gs_member', 'side' );
        }
        add_action( 'admin_menu' , 'member_type\remove_tax_meta_boxes' );

        function plugin_meta_box() {
            add_meta_box(
                'gs_member',                     // is the required HTML id attribute
                'Member Details',                     // is the text visible in the heading of the meta box section
                'member_type\plugin_display_details_meta_box',  // is the callback which renders the contents of the meta box
                'gs_member',                     // is the name of the custom post type where the meta box will be displayed
                'normal',                           // defines the part of the page where the edit screen section should be shown
                'high'                              // defines the priority within the context where the boxes should show
            );
            add_meta_box(
                'gs_member_serving_years',                     // is the required HTML id attribute
                'Serving Years',                     // is the text visible in the heading of the meta box section
                'member_type\plugin_display_details_meta_box2',  // is the callback which renders the contents of the meta box
                'gs_member',                     // is the name of the custom post type where the meta box will be displayed
                'normal',                           // defines the part of the page where the edit screen section should be shown
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

            $data['first_name']                     = valueFromMeta( $meta, 'first_name' );
            $data['last_name']                      = valueFromMeta( $meta, 'last_name' );
            $data['email']                          = valueFromMeta( $meta, 'email' );
            $data['college']                        = valueFromMeta( $meta, 'college' );
            $data['department']                     = valueFromMeta( $meta, 'department' );
            $data['faculty_senate_member']          = valueFromMeta( $meta, 'faculty_senate_member' );
            $data['faculty_senate_steering_committee_member']       = valueFromMeta( $meta, 'faculty_senate_steering_committee_member' );

            $data['council_serving_years']      = valueFromMetaArray( $meta, 'council_serving_years' );
            $data['curriculum_serving_years']   = valueFromMetaArray( $meta, 'curriculum_serving_years' );
            $data['policy_serving_years']       = valueFromMetaArray( $meta, 'policy_serving_years' );
            $data['appeals_serving_years']      = valueFromMetaArray( $meta, 'appeals_serving_years' );
            $data['program_serving_years']      = valueFromMetaArray( $meta, 'program_serving_years' );

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
            if ($post->post_type == 'gs_member') {

                $first_name = save_field( $id, 'first_name', 'first_name');
                $last_name = save_field( $id, 'last_name', 'last_name');
                save_field( $id, 'email', 'email');
                save_field( $id, 'college', 'college');
                save_field( $id, 'department', 'department');
                save_field( $id, 'faculty_senate_member', 'faculty_senate_member');
                save_field( $id, 'faculty_senate_steering_committee_member', 'faculty_senate_steering_committee_member');

                save_field( $id, 'council_serving_years', 'council_serving_years');
                save_field( $id, 'curriculum_serving_years', 'curriculum_serving_years');
                save_field( $id, 'policy_serving_years', 'policy_serving_years');
                save_field( $id, 'appeals_serving_years', 'appeals_serving_years');
                save_field( $id, 'program_serving_years', 'program_serving_years');

                remove_action( 'save_post', 'member_type\plugin_save_post'  );
                wp_update_post(array(
                    'ID' => $id,
                    'post_title' => $last_name . ', ' . $first_name,
                    'post_name' =>  $first_name . '-' . $last_name,
                ));
                add_action( 'save_post', 'member_type\plugin_save_post'  );
            }
        }
        function plugin_display_details_meta_box($post) {
            $data = file_data( $post->ID );
            $setting_colleges = explode(',', trim(esc_attr(get_option('colleges'))));
            ?>
            <div>
                <style>
                    #member_details_table th {
                        text-align: right;
                        padding: 10px  10px 0 25px;
                        width: 1%;
                        white-space: nowrap;
                    }
                    #member_details_table input[type=text] {
                        width: 100%;
                    }
                    #member_details_table select {
                        width: 100%;
                    }
                    #member_details_table input[type=checkbox] {
                        margin-left: 1px;
                    }
                </style>
                <table id="member_details_table" width="100%">
                    <tr>
                        <th><label for="first_name">First Name:</label></th>
                        <td>
                            <input type="text" name="first_name" id="first_name"
                                   value="<?php echo $data['first_name'] ?>"/>
                        </td>
                        <th style="text-align: right;"><label for="last_name">Last Name:</label></th>
                        <td>
                            <input type="text" name="last_name" id="last_name"
                                   value="<?php echo $data['last_name'] ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="email">Email:</label></th>
                        <td>
                            <input type="text" name="email" id="email"
                                   value="<?php echo $data['email'] ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="college">College (or) Unit:</label></th>
                        <td>
                            <select name="college" id="college">
                            <?php
                                foreach( $setting_colleges as $college ) {
                                    if( $data['college'] == $college ) {
                                        echo "<option selected>" . $college . "</option>";
                                    } else {
                                        echo "<option>" . $college . "</option>";
                                    }
                                }
                            ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="department">Department:</label></th>
                        <td>
                            <input type="text" name="department" id="department"
                                   value="<?php echo $data['department'] ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="faculty_senate_member">Faculty Senate Member:</label></th>
                        <td>
                            <input type="checkbox" id="faculty_senate_member" name="faculty_senate_member"
                                <?php if( !empty( $data['faculty_senate_member'] ) ) echo 'checked=checked' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="faculty_senate_steering_committee_member">F.S. Steering Committee Member:</label></th>
                        <td>
                            <input type="checkbox" id="faculty_senate_steering_committee_member" name="faculty_senate_steering_committee_member"
                                <?php if( !empty( $data['faculty_senate_steering_committee_member'] ) ) echo 'checked=checked' ?>>
                        </td>
                    </tr>
                </table>
            </div>
        <?php
        }
        function select_multiple_categories( $tax, $name, $data ) {
            $my_terms = get_terms( $tax, array(
                'orderby' => 'name',
                'order' => 'DESC',
                'hide_empty' => 0,
            ));
            echo '<select multiple="multiple" name="'. $name .'" id="'. $name .'">';
            echo '<option></option>';
            foreach ( $my_terms as $tax_term ) {
                $selected = !empty( $data ) && in_array( $tax_term->name, $data ) ? ' selected="selected" ' : '';
                echo '<option '. $selected .'>'. $tax_term->name .'</option>';
            }
            echo '</select>';
        }
        function select_categories( $tax, $name, $data, $usingSelected = true ) {
            $my_terms = get_terms( $tax, array(
                'orderby' => 'name',
                'order' => 'DESC',
                'hide_empty' => 0,
            ));
            echo '<select name="'. $name .'" id="'. $name .'">';
            echo '<option></option>';
            foreach ( $my_terms as $tax_term ) {
                $selected = '';
                if( $usingSelected )
                    $selected = !empty( $data ) && in_array( $tax_term->name, $data ) ? ' selected="selected" ' : '';
                echo '<option '. $selected .'>'. $tax_term->name .'</option>';
            }
            echo '</select>';
        }
        function plugin_display_details_meta_box2($post) {
            $data = file_data( $post->ID );
            ?>
            <div>
                <style>
                    .member_details_table {
                        border-collapse: collapse;
                    }
                    .member_details_table td {
                        padding: 10px 0 10px 0;
                    }
                    .member_details_table th.table-label {
                        vertical-align: top;
                        text-align: right;
                        padding: 30px 10px 0 25px;
                        width: 1%;
                        white-space: nowrap;
                    }
                    .member_details_table input[type=text] {
                        width: 100%;
                    }
                    .member_details_table select[multiple] {
                        width: 100%;
                        height: auto;
                    }
                    .member_details_table select {
                        width: 100%;
                        height: auto;
                    }
                    .member_details_table input[type=checkbox] {
                        margin-left: 1px;
                    }
                    .small-table-column {
                        text-align: right;
                        width: 1%;
                        white-space: nowrap;
                    }
                    .role-table {
                        border-collapse: collapse;
                    }
                    .role-table .button {
                        width: 100%;
                    }
                    .role-table thead th {
                        text-align: left;
                        padding: 0 10px;
                        border-bottom: 1px solid #ddd;
                    }
                    .role-table tbody td, .role-table tfoot td {
                        padding: 0;
                    }
                    .role-table .role-column {
                        padding: 0 10px;
                        border-bottom: 1px solid #ddd;
                    }
                </style>
                <table class="member_details_table" width="100%">
                    <tr>
                        <th class="table-label"><label for="council_serving_years">Graduate Council Serving Years:</label></th>
                        <td>
                            <input id="council_hidden" name="council_serving_years" type="hidden" value="<?php echo implode( ',', $data['council_serving_years'] ); ?>">
                            <table class="role-table" width="100%">
                                <thead><tr><th>Memberships</th><th>Actions</th></tr></thead>
                                <tbody id="council_body"><tr><td colspan="2">No History</td></tr></tbody>
                                <tfoot>
                                <tr>
                                    <td>
                                        <?php select_categories(
                                            'graduate_council_serving_years',
                                            'council_select',
                                            $data['council_serving_years'],
                                            false
                                        ); ?>
                                    </td>
                                    <td class="small-table-column">
                                        <button class="button" type="button" id="council_add" onclick="addRole('graduateCouncil')">Add</button>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th class="table-label"><label for="curriculum_serving_years">Curriculum Subcommittee Serving Years:</label></th>
                        <td>
                            <input id="curriculum_hidden" name="curriculum_serving_years" type="hidden" value="<?php echo implode( ',', $data['curriculum_serving_years'] ); ?>">
                            <table class="role-table" width="100%">
                                <thead><tr><th>Memberships</th><th>Actions</th></tr></thead>
                                <tbody id="curriculum_body"><tr><td colspan="2">No History</td></tr></tbody>
                                <tfoot>
                                <tr>
                                    <td>
                                        <?php select_categories(
                                            'curriculum_serving_years',
                                            'curriculum_select',
                                            $data['curriculum_serving_years'],
                                            false
                                        ); ?>
                                    </td>
                                    <td class="small-table-column">
                                        <button class="button" type="button" id="curriculum_add" onclick="addRole('curriculum')">Add</button>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th class="table-label"><label for="policy_serving_years">Policy and Procedures Subcommittee Serving Years:</label></th>
                        <td>
                            <input id="policy_hidden" name="policy_serving_years" type="hidden" value="<?php echo implode( ',', $data['policy_serving_years'] ); ?>">
                            <table class="role-table" width="100%">
                                <thead><tr><th>Memberships</th><th>Actions</th></tr></thead>
                                <tbody id="policy_body"><tr><td colspan="2">No History</td></tr></tbody>
                                <tfoot>
                                <tr>
                                    <td>
                                        <?php select_categories(
                                            'policy_serving_years',
                                            'policy_select',
                                            $data['policy_serving_years'],
                                            false
                                        ); ?>
                                    </td>
                                    <td class="small-table-column">
                                        <button class="button" type="button" id="policy_add" onclick="addRole('policy')">Add</button>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th class="table-label"><label for="appeals_select">Appeals and Awards Subcommittee Serving Years:</label></th>
                        <td>
                            <input id="appeals_hidden" name="appeals_serving_years" type="hidden" value="<?php echo implode( ',', $data['appeals_serving_years'] ); ?>">
                            <table class="role-table" width="100%">
                                <thead><tr><th>Memberships</th><th>Actions</th></tr></thead>
                                <tbody id="appeals_body"><tr><td colspan="2">No History</td></tr></tbody>
                                <tfoot>
                                <tr>
                                    <td>
                                        <?php select_categories(
                                            'appeals_serving_years',
                                            'appeals_select',
                                            $data['appeals_serving_years'],
                                            false
                                        ); ?>
                                    </td>
                                    <td class="small-table-column">
                                        <button class="button" type="button" id="appeals_add" onclick="addRole('appeals')">Add</button>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th class="table-label"><label for="program_select">Program Review Subcommittee Serving Years:</label></th>
                        <td>
                            <input id="program_hidden" name="program_serving_years" type="hidden" value="<?php echo implode( ',', $data['program_serving_years'] ); ?>">
                            <table class="role-table" width="100%">
                                <thead><tr><th>Memberships</th><th>Actions</th></tr></thead>
                                <tbody id="program_body"><tr><td colspan="2">No History</td></tr></tbody>
                                <tfoot>
                                    <tr>
                                        <td>
                                        <?php select_categories(
                                            'program_serving_years',
                                            'program_select',
                                            $data['program_serving_years'],
                                            false
                                        ); ?>
                                        </td>
                                        <td class="small-table-column">
                                        <button class="button" type="button" id="program_add" onclick="addRole('program')">Add</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <script>
                var councils = {
                    graduateCouncil: {
                        add: document.getElementById('council_add'),
                        body: document.getElementById('council_body'),
                        hidden: document.getElementById('council_hidden'),
                        select: document.getElementById('council_select')
                    },
                    curriculum: {
                        add: document.getElementById('curriculum_add'),
                        body: document.getElementById('curriculum_body'),
                        hidden: document.getElementById('curriculum_hidden'),
                        select: document.getElementById('curriculum_select')
                    },
                    policy: {
                        add: document.getElementById('policy_add'),
                        body: document.getElementById('policy_body'),
                        hidden: document.getElementById('policy_hidden'),
                        select: document.getElementById('policy_select')
                    },
                    appeals: {
                        add: document.getElementById('appeals_add'),
                        body: document.getElementById('appeals_body'),
                        hidden: document.getElementById('appeals_hidden'),
                        select: document.getElementById('appeals_select')
                    },
                    program: {
                        add: document.getElementById('program_add'),
                        body: document.getElementById('program_body'),
                        hidden: document.getElementById('program_hidden'),
                        select: document.getElementById('program_select')
                    }
                };

                function readRoles( element ) {
                    var value = element.value.split(',');
                    if( value.length > 0 && value[0] != '' )
                        return value;
                    else
                        return [];
                }
                function renderRoles( council, roles ) {
                    var r = '';

                    for( var i = 0; i < roles.length; i++ ) {
                        r += '<tr>';
                        r += '<td class="role-column">' + roles[ i ] + '</td>';
                        r += '<td><button class="button" type="button" onclick="removeRole( \'' + council + '\',\'' + roles[i] + '\')">Remove</button></td>';
                        r += '</tr>';
                    }

                    return r;
                }
                function addRole( council ) {
                    var role = councils[ council ].select.value;
                    councils[ council ].select.value = '';

                    if( role != '' ) {
                        var roles = readRoles(councils[council].hidden);
                        roles.push(role);

                        console.log(roles);

                        councils[council].hidden.value = roles.join(',');

                        councils[council].body.innerHTML = renderRoles(council, roles);
                    }
                }
                function removeRole( council, role ) {
                    var roles = readRoles( councils[ council ].hidden );

                    roles.splice( roles.indexOf( role ), 1 );

                    councils[ council ].hidden.value = roles.join(',');

                    councils[ council ].body.innerHTML = renderRoles( council, roles );
                }
                (function initialize(){
                    for( var council in councils ) {
                        if( councils.hasOwnProperty( council ) ) {
                            var roles = readRoles( councils[ council ].hidden );
                            councils[ council ].body.innerHTML = renderRoles( council, roles );
                        }
                    }
                })();
            </script>
        <?php
        }
        function plugin_scripts()
        {
            if (!is_admin()) {
                wp_deregister_script('jquery');
                wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"), false, '1.11.3');
                wp_enqueue_script('jquery');
            }
        }

        // ---
        // This function adds meta data to the page template
        // Add the category meta box to the file template admin page.
        //
        function wpse70958_add_meta_boxes( $post ) {

            // Get the page template post meta
            $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
            // If the current page uses our specific
            // template, then output our custom metabox

            if ( 'page_council-homepage.php' == $page_template ) {
                add_meta_box(
                    'wpse70958-custom-metabox', // Metabox HTML ID attribute
                    'Members Settings', // Metabox title
                    'member_type\wpse70598_page_template_metabox', // callback name
                    'page', // post type
                    'side', // context (advanced, normal, or side)
                    'high' // priority (high, core, default or low)
                );
            }
            if ( 'page_members-archive.php' == $page_template ) {
                add_meta_box(
                    'wpse70958-custom-metabox', // Metabox HTML ID attribute
                    'Members Settings', // Metabox title
                    'member_type\wpse70598_members_archive_metabox', // callback name
                    'page', // post type
                    'side', // context (advanced, normal, or side)
                    'high' // priority (high, core, default or low)
                );
            }
        }

        // Make sure to use "_" instead of "-"
        function wpse70598_page_template_metabox( $post ) {
            // Define the meta box form fields here
            $meta = get_post_meta( $post->ID );
            $show_committees    = valueFromMeta( $meta, 'show_committees' );
            $committee          = valueFromMeta( $meta, 'committee' );

            $meetingSlug        = valueFromMeta( $meta, 'meetingSlug' );
            $membersSlug        = valueFromMeta( $meta, 'membersSlug' );
            ?>
            <style>
                h3.setting-heading {
                    margin: 5px 0;
                }
            </style>
            <div>
                <h3 class="setting-heading"><label for="committee">Committee:</label></h3>
                <select id="committee" name="committee">
                    <option value="" <?php if( $committee == '' ) { echo 'selected'; } ?>></option>
                    <option value="appeals_serving_years" <?php if( $committee == 'appeals_serving_years' ) { echo 'selected'; } ?>>Appeals</option>
                    <option value="curriculum_serving_years" <?php if( $committee == 'curriculum_serving_years' ) { echo 'selected'; } ?>>Curriculum</option>
                    <option value="policy_serving_years" <?php if( $committee == 'policy_serving_years' ) { echo 'selected'; } ?>>Policy</option>
                    <option value="program_serving_years" <?php if( $committee == 'program_serving_years' ) { echo 'selected'; } ?>>Program Review and Awards</option>
                </select>
            </div>
            <h3 class="setting-heading">Member Settings:</h3>
            <div>
                <label for="show_committees">Show Member Committees:</label>
                <input id="show_committees" type="checkbox" name="show_committees" <?php if( !empty( $show_committees ) ) { echo 'checked="checked"'; } ?>>
            </div>
            <h3 class="setting-heading">Meeting Settings:</h3>
            <div>
                <label for="hasSubmitDate">Has Submit Date:</label>
                <input id="hasSubmitDate" name="hasSubmitDate" type="checkbox" <?php if( $meta['hasSubmitDate'][0] ) { echo "checked";} ?>>
            </div>
            <div>
                <label for="hasAgenda">Has Agenda:</label>
                <input id="hasAgenda" name="hasAgenda" type="checkbox" <?php if( $meta['hasAgenda'][0] ) { echo "checked";} ?>>
            </div>
            <div>
                <label for="hasMinutes">Has Minutes:</label>
                <input id="hasMinutes" name="hasMinutes" type="checkbox" <?php if( $meta['hasMinutes'][0] ) { echo "checked";} ?>>
            </div>
            <div>
                <label for="meetingSlug">Meetings Archive Slug:</label>
                <input id="meetingSlug" name="meetingSlug" type="text" value="<?php echo $meetingSlug; ?>" placeholder="Meetings archive slug link">
            </div>
            <div>
                <label for="membersSlug">Members Archive Slug:</label>
                <input id="memberSlug" name="membersSlug" type="text" value="<?php echo $membersSlug; ?>" placeholder="Members archive slug link">
            </div>
        <?php
        }
        function wpse70598_members_archive_metabox( $post ) {
            // Define the meta box form fields here
            $meta = get_post_meta( $post->ID );
            $show_committees    = valueFromMeta( $meta, 'show_committees' );
            $committee    = valueFromMeta( $meta, 'committee' );
            ?>
            <style>
                h3.setting-heading {
                    margin: 5px 0;
                }
            </style>
            <div>
                <h3 class="setting-heading"><label for="committee">Committee:</label></h3>
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

        function wpse70958_save_custom_post_meta( $ID ) {
            $page_template = get_post_meta( $ID, '_wp_page_template', true );
            // If the current page uses our specific
            // template, then output our custom metabox

            if ( 'page_council-homepage.php' == $page_template ) {
                save_field($ID, 'show_committees', 'show_committees');
                save_field($ID, 'committee', 'committee');
                save_field($ID, 'hasSubmitDate', 'hasSubmitDate');
                save_field($ID, 'hasAgenda', 'hasAgenda');
                save_field($ID, 'hasMinutes', 'hasMinutes');

                save_field($ID, 'meetingSlug', 'meetingSlug');
                save_field($ID, 'membersSlug', 'membersSlug');
            }
            if ( 'page_members-archive.php' == $page_template ) {
                save_field($ID, 'show_committees', 'show_committees');
                save_field($ID, 'committee', 'committee');
            }
        }
        add_action( 'publish_page', 'member_type\wpse70958_save_custom_post_meta' );
        add_action( 'draft_page', 'member_type\wpse70958_save_custom_post_meta' );
        add_action( 'future_page', 'member_type\wpse70958_save_custom_post_meta' );
        add_action( 'add_meta_boxes_page', 'member_type\wpse70958_add_meta_boxes' );
    }
}
