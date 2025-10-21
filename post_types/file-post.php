<?php
namespace {
    if (!defined('ABSPATH')) // wp-config.php defines an ABSPATH variable, this trap ensures that only WP can use this code.
        exit;
}

namespace file_post_type{
    
    if (!function_exists('file_post_type\file_post_setup')) {
        add_action('wp_enqueue_scripts', 'file_post_type\plugin_scripts', 0); // action, array, priority ( 0 lowest, 10 normal, 10+ higher)
        add_action('init', 'file_post_type\new_post_type_file_posting');
        add_action('admin_init', 'file_post_type\plugin_meta_box'); // admin_init is triggered before any other hook when a user accesses the admin area.
        add_action('admin_enqueue_scripts', 'file_post_type\plugin_admin_scripts');
        add_action('save_post', 'file_post_type\plugin_save_post', 10, 2);

        function plugin_admin_scripts($page)
        {
            if ('post.php' != $page && 'post-new.php' != $page) {
                return;
            }
            wp_enqueue_media();

            // Registers and enqueues the required javascript.
            wp_register_script('gs-admin-file-post',  get_template_directory_uri() . '/js/gs-admin-file-post.js', array('jquery'));
            wp_enqueue_script('gs-admin-file-post');

            wp_register_style( 'pikaday', get_template_directory_uri() . '/vendor/pikaday/css/pikaday.css', false, '1.0.0' );
            wp_enqueue_style( 'pikaday' );

            wp_register_script( 'moment', get_template_directory_uri() . '/vendor/moment/moment.js', false, '2.14.1' );
            wp_enqueue_script( 'moment' );

            wp_register_script( 'pikaday', get_template_directory_uri() . '/vendor/pikaday/pikaday.js', false, '1.4.0' );
            wp_enqueue_script( 'pikaday' );
        }

        function new_post_type_file_posting()
        {
            $labels = array(
                'name' => __('File posting'),
                'singular_name' => __('File Post'),
                'add_new' => 'Add New File Post',
                'add_new_item' => 'Add New File Post',
                'edit_item' => 'Edit File Post',
                'view_item' => 'View File Post',
                'search_items' => 'Search File Postings',
                'not_found' => 'No File Postings found',
                'not_found_in_trash' => 'No File Postings found in Trash',
                'parent' => 'Parent File Post'
            );

            register_post_type('gs_file',
                array(
                    'labels' => $labels,
                    'description' => 'Post a file and any children',
					'public' => false,
					'publicly_queryable' => false,
					'show_ui' => true,
                    'has_archive' => true,
                    'show_in_rest' => true,
                    'rewrite' => array('slug' => 'uploads'),
                    'supports' => array('title', 'page-attributes'),
                    'show_in_menu' => true,
                    'menu_icon' => get_template_directory_uri() . '/images/images-alt2.svg',
                    'menu_position' => 60,
                )
            );
        }

        function plugin_meta_box() {
            add_meta_box(
                'gs_file',                     // is the required HTML id attribute
                'File Posting',                     // is the text visible in the heading of the meta box section
                'file_post_type\plugin_display_details_meta_box',  // is the callback which renders the contents of the meta box
                'gs_file',                     // is the name of the custom post type where the meta box will be displayed
                'normal',                           // defines the part of the page where the edit screen section should be shown
                'high'                              // defines the priority within the context where the boxes should show
            );

        }

        function file_data( $id ) {
            $meta   = get_post_meta( $id );
            $data  = array();

            $data['file_url']       = esc_html( (( !empty( $meta['file_url']  ) )? $meta['file_url'][0]  : '') );
            $data['committee']      = esc_html( (( !empty( $meta['committee']  ) )? $meta['committee'][0]  : '') );
            $data['document-type']  = esc_html( (( !empty( $meta['document-type']  ) )? $meta['document-type'][0]  : '') );
            $data['year']           = esc_html( (( !empty( $meta['year']  ) )? $meta['year'][0]  : '') );
            $data['date']           = esc_html( (( !empty( $meta['date']  ) )? $meta['date'][0]  : '') );
            $data['policy-name']    = esc_html( (( !empty( $meta['policy-name']  ) )? $meta['policy-name'][0]  : '') );
            $data['policy-status']  = esc_html( (( !empty( $meta['policy-status']  ) )? $meta['policy-status'][0]  : '') );
            $data['policy-url-in-catalog']  = esc_html( (( !empty( $meta['policy-url-in-catalog']  ) )? $meta['policy-url-in-catalog'][0]  : '') );

            return $data;
        }
        function valueFromMeta( $meta, $key ) {
            if( !empty( $meta[ $key ] ) )
                return $meta[ $key ][0];
            else
                return '';
        }
        function save_field($id, $post_key, $meta_key, $default = '') {
            if (isset($_POST[$post_key]) && $_POST[$post_key] != '')
                update_post_meta( $id, $meta_key, $_POST[$post_key] );
            else
                update_post_meta( $id, $meta_key, $default );
        }


        function save_array($id, $post_key, $meta_key) {
            if (isset($_POST[$post_key]) && is_array($_POST[$post_key]) && !empty($_POST[$post_key]))
                update_post_meta($id, $meta_key, $_POST[$post_key]);
        }

        function plugin_save_post($id, $post) {
            if ($post->post_type == 'gs_file') {
                save_field( $id, 'file_url', 'file_url');
                save_field( $id, 'committee', 'committee');
                save_field( $id, 'document-type', 'document-type');
                save_field( $id, 'year', 'year');
                save_field( $id, 'policy-name', 'policy-name');
                save_field( $id, 'policy-status', 'policy-status');
                save_field( $id, 'policy-url-in-catalog', 'policy-url-in-catalog');
				
				// Wordpress 5.0 changed the date-picker-ui to a new format m/d/Y => "Weekday NiceMonth Day Year".
				$date = trim( $_POST['date'] ); // Its important to note that trim returns '' when trimming an unset argument.
				if( ! empty( $date ) ) 
					$date = date( 'm/d/Y', strtotime( $date ) );
                update_post_meta( $id, 'date', $date );
            }
        }
        function plugin_display_details_meta_box($post) {
            $policy_status = array(
                'public_comment' => 'Public Comment',
                'under_review' => 'Under Review',
                'approved' => 'Approved',
                'rejected' => 'Not Approved'
            );
            
            $data = file_data( $post->ID );
            $setting_years = trim( esc_attr( get_option( 'years' ) ) );
            $setting_years = explode( ',', $setting_years );
            ?>
            <style>
                #policy-attributes {
                    padding: 5px 10px 5px 20px;
                    border: 2px solid black;
                    border-radius: 5px;
                    margin: 5px auto;
                    transition: all 1s ease-in-out;
                }
                table.file-posting th, #policy-attributes th.file-posting  {
                    text-align: right;
                    width: 1%;
                    white-space: nowrap;
                    padding: 0 10px 0 20px;
                }

                .policy-attributes-hidden{
                    visibility: hidden;
                    opacity: 0;
                    max-height: 0px;
                    transform: translate3(0,-200px,-100px);
                }

                .policy-attributes-visible{
                    visibility: visible;
                    opacity: 1;
                    max-height: 500px;
                    transform: translate3d(0,0,0);
                }

                .date-label-out {
                    transform: translate3d(-100px,0,0);
                    opacity: 0;
                }

                .date-label-in {
                    transform: translate3d(0,0,0);
                    opacity: 1;
                }

                .date-label-in, .date-label-out {
                    transition: 1s ease-in-out;
                    text-align: right;
                }

                .date-label-container {
                    display: inline-grid;
                }

                .date-label-out, .date-label-in{
                    grid-column: 1;
                    grid-row: 1;
                }

                #document_type_radio label {
                    line-height: 2;
                }
            </style>
            <div>
                <table class="file-posting" width="100%">
                    <tr>
                        <th><label for="year">Year:</label></th>
                        <td>
                            <select id="year" name="year">
                                <option></option>
                                <?php
                                for( $i = 0, $l = count( $setting_years ); $i < $l; $i++ )
                                    if( $setting_years[ $i ] == $data['year'] )
                                        echo "<option selected>" . $setting_years[$i] . "</option>";
                                    else
                                        echo "<option>" . $setting_years[$i] . "</option>";
                                ?>
                            </select>
                        </td>
                        <th><label for="committee">Committee:</label></th>
                        <td>
                            <select name="committee">
                                <option></option>
                                <option value="appeals_serving_years" <?php if( $data['committee'] == 'appeals_serving_years' )         echo "selected"; ?>>Appeals</option>
                                <option value="curriculum_serving_years" <?php if( $data['committee'] == 'curriculum_serving_years' )   echo "selected"; ?>>Curriculum</option>
                                <option value="policy_serving_years" <?php if( $data['committee'] == 'policy_serving_years' )           echo "selected"; ?>>Policy</option>
                                <option value="program_serving_years" <?php if( $data['committee'] == 'program_serving_years' )         echo "selected"; ?>>Program Review and Awards</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label id="date-label" for="date">
                            <div class="date-label-container">
                                <div class="<?php if( $data['document-type'] != 'policies' ) echo "date-label-in"; else echo "date-label-out"; ?> date-label">Date:</div>
                                <div class="<?php if( 'policies' == $data['document-type'] ) echo "date-label-in"; else echo "date-label-out"; ?> effective-date-label">Effective Date:</div>
                            </div>
                        </label></th>
                        <td>
                            <input style="width: 100%" type="text" name="date" id="date" value="<?php echo $data['date'] ?>"/>
                        </td>
                        <th><label for="document-type">Document Type:</label></th>
                        <td>
                            <select name="document-type" onchange="handleDocumentTypeChange(this)">
                                <option></option>
                                <option value="agenda" <?php if( $data['document-type'] == 'agenda' ) echo "selected"; ?>>Agenda</option>
                                <option value="minutes" <?php if( $data['document-type'] == 'minutes' ) echo "selected"; ?>>Minutes</option>
                                <option value="reports" <?php if( $data['document-type'] == 'reports' ) echo "selected"; ?>>Reports</option>
                                <option value="forms" <?php if( $data['document-type'] == 'forms' ) echo "selected"; ?>>Forms and Files</option>
                                <option value="policies" <?php if( $data['document-type'] == 'policies' ) echo "selected"; ?>>Policies</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <table id="policy-attributes" class="<?php if( 'policies' == $data['document-type'] ) echo "policy-attributes-visible"; else echo "policy-attributes-hidden"; ?>" <?php if( 'policies' != $data['document-type'] ) echo " style=\"position: absolute;\""; ?>width="100%">
                    <tr>
                        <th><label for="policy-name">Policy:</label></th>
                        <td>
                            <input style="width: 100%" type="text" name="policy-name" id="policy-name" value="<?php echo $data['policy-name'] ?>"/>
                        </td>
                        <th><label for="policy-status">Status:<?php ; ?></label></th>
                        <td>
                            <select name="policy-status">
                                <option></option>
                                <option value="<?= $policy_status['public_comment'] ?>" <?php if( $data['policy-status'] == $policy_status['public_comment'] ) echo "selected"; ?>><?= $policy_status['public_comment'] ?></option>
                                <option value="<?= $policy_status['under_review'] ?>" <?php if( $data['policy-status'] == $policy_status['under_review'] ) echo "selected"; ?>><?= $policy_status['under_review'] ?></option>
                                <option value="<?= $policy_status['approved'] ?>" <?php if( $data['policy-status'] == $policy_status['approved'] ) echo "selected"; ?>><?= $policy_status['approved'] ?></option>
                                <option value="<?= $policy_status['rejected'] ?>" <?php if( $data['policy-status'] == $policy_status['rejected'] ) echo "selected"; ?>><?= $policy_status['rejected'] ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th class="file-posting" ><label for="policy-url-in-catalog">Link to Policy in Graduate Catalog:</label></th>
                        <td width="70%">
                            <input style="width: 100%" type="text" name="policy-url-in-catalog" id="policy-url-in-catalog" value="<?php echo $data['policy-url-in-catalog'] ?>"/>
                        </td>
                    </tr>
                </table>
                <table class="file-posting" width="100%">
                    <tr>
                        <th><label for="meta-image">File Path:</th>
                        <td>

                            <input style="width: 100%" type="text" name="file_url" id="meta-image"
                                   value="<?php echo $data['file_url'] ?>"/>
                        </td>
                        <td style="width: 1%">
                            <input type="button" id="meta-file-button" class="button" value="Choose or Upload a File"/>
                        </td>
                    </tr>
                </table>
                <script>
                    var picker = new Pikaday({
                        field: document.getElementById('date'),
                        format: 'L'
                    });
                    addEventListener("transitionend", (event) => {});
                    ontransitionend = (event) => {};
                    function handleDocumentTypeChange(e) {
                        $policy_table = document.getElementById('policy-attributes');
                        $startOutElement = document.getElementsByClassName('date-label-out')[0];
                        $startInElement = document.getElementsByClassName('date-label-in')[0];
                        $isCurrentPoliciesType = $startInElement?.classList.contains('effective-date-label');
                        if(('policies' == e?.value && !$isCurrentPoliciesType) || ('policies' != e?.value && $isCurrentPoliciesType)) {
                            $startOutElement?.classList.toggle('date-label-out');
                            $startOutElement?.classList.toggle('date-label-in');
                            $startInElement?.classList.toggle('date-label-out');
                            $startInElement?.classList.toggle('date-label-in');
                            $policy_table?.classList.toggle("policy-attributes-hidden");
                            $policy_table?.classList.toggle("policy-attributes-visible");
                            if ($isCurrentPoliciesType) {
                                document.getElementById('policy-attributes').ontransitionend = () => {
                                    document.getElementById('policy-attributes').style["position"] = "absolute";
                                };
                            } else {
                                document.getElementById('policy-attributes').style["position"] = "unset";
                                document.getElementById('policy-attributes').ontransitionend = () => {};
                            }
                        }
                    }
                </script>
            </div>
        <?php
        }
function plugin_scripts()
        {
            if (!is_admin()) {
                wp_deregister_script('jquery');
                wp_register_script('jquery', ("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"), false, '1.11.3');
                wp_enqueue_script('jquery');
            }
        }

        // ---
        // This function adds meta data to the page template
        // Add the category meta box to the file template admin page.
        //
        function add_page_settings_metabox( $post ) {

            // Get the page template post meta
            $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
            // If the current page uses our specific
            // template, then output our custom metabox

            if ( 'page_filedirectory.php' == $page_template ) {
                add_meta_box(
                    'custom-metabox', // Metabox HTML ID attribute
                    'Members Settings', // Metabox title
                    'file_post_type\page_settings_metabox', // callback name
                    'page', // post type
                    'side', // context (advanced, normal, or side)
                    'high' // priority (high, core, default or low)
                );
            }
        }

        // Make sure to use "_" instead of "-"
        function page_settings_metabox( $post ) {
            // Define the meta box form fields here
            $meta = get_post_meta( $post->ID );
            $documentType   = valueFromMeta( $meta, 'document-type' );
            $committee      = valueFromMeta( $meta, 'committee' );
            ?>
            <style>
                .file-directory-meta-box select {
                    width: 100%;
                }
            </style>
            <div class="file-directory-meta-box">
                <label for="document-type">Document Type:</label>
                <select id="document-type" name="document-type">
                    <option></option>
                    <option value="agenda" <?php if( $documentType == 'agenda' ) echo "selected"; ?>>Agenda</option>
                    <option value="minutes" <?php if( $documentType == 'minutes' ) echo "selected"; ?>>Minutes</option>
                    <option value="forms" <?php if( $documentType == 'forms' ) echo "selected"; ?>>Forms and Files</option>
                    <option value="reports" <?php if( $documentType == 'reports' ) echo "selected"; ?>>Reports</option>
                    <option value="policies" <?php if( $documentType == 'policies' ) echo "selected"; ?>>Policies</option>
                </select>
                <label for="committee">Committee:</label>
                <select id="committee" name="committee">
                    <option value=""></option>
                    <option value="council_serving_years" <?php if( $committee == 'council_serving_years' )         echo "selected"; ?>>Council</option>
                    <option value="appeals_serving_years" <?php if( $committee == 'appeals_serving_years' )         echo "selected"; ?>>Appeals</option>
                    <option value="curriculum_serving_years" <?php if( $committee == 'curriculum_serving_years' )   echo "selected"; ?>>Curriculum</option>
                    <option value="policy_serving_years" <?php if( $committee == 'policy_serving_years' )           echo "selected"; ?>>Policy</option>
                    <option value="program_serving_years" <?php if( $committee == 'program_serving_years' )         echo "selected"; ?>>Program Review and Awards</option>
                </select>
                <label>Use Current Year Only:
                    <input name="current_year_only" type="checkbox" <?php if( $meta['current_year_only'][0] == 'on' ) echo "checked='checked'"; ?>>
                </label>
            </div>
        <?php
        }

        function save_custom_post_meta( $ID ) {
            $page_template = get_post_meta( $ID, '_wp_page_template', true );
            // If the current page uses our specific
            // template, then output our custom metabox

            if ( 'page_filedirectory.php' == $page_template ) {
                save_field($ID, 'committee', 'committee');
                save_field($ID, 'document-type', 'document-type');
                save_field($ID, 'current_year_only', 'current_year_only');
            }
        }
        add_action( 'publish_page', 'file_post_type\save_custom_post_meta' );
        add_action( 'draft_page', 'file_post_type\save_custom_post_meta' );
        add_action( 'future_page', 'file_post_type\save_custom_post_meta' );
        add_action( 'add_meta_boxes_page', 'file_post_type\add_page_settings_metabox' );


		function remove_default_document_type_meta_box() {
			remove_meta_box('tagsdiv-document-type', 'gs_file', 'side');
		}
		add_action('add_meta_boxes', 'file_post_type\remove_default_document_type_meta_box');


		function add_document_type_radio_meta_box() {

            global $wp_meta_boxes;

            // Check if pageparentdiv is registered for gs_file in the side context
            $has_pageparent = isset($wp_meta_boxes['gs_file']['side']['core']['pageparentdiv']);

            // Remove/add default page-attributes meta box to ensure it appears after document types
            if ($has_pageparent) remove_meta_box('pageparentdiv', 'gs_file', 'side');

            // Why we're here
			add_meta_box(
				'document_type_radio',
				'Document Type',
				'file_post_type\document_type_radio_meta_box_callback',
				'gs_file',
				'side',
				'core'
			);

            // Now bring back page-attributes
            if ($has_pageparent) add_meta_box(
                'pageparentdiv',
                __('Page Attributes'),
                'page_attributes_meta_box',
                'gs_file',
                'side',
                'default'
            );
		}
		add_action('add_meta_boxes', 'file_post_type\add_document_type_radio_meta_box');

		function document_type_radio_meta_box_callback($post) {
			$taxonomy = 'document-type';
			$terms = get_terms([
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
			]);
			$current_terms = wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'ids']);

            echo '<div>';
			foreach ($terms as $term) {
				echo '<label><input type="radio" name="document_type_term" value="' . esc_attr($term->term_id) . '" ' . checked(in_array($term->term_id, $current_terms), true, false) . '> ' . esc_html($term->name) . '</label><br>';
			}
            echo '<input type="text" name="new_document_type_term" placeholder="Add new document type" style="margin-top:10px;width:100%;" />';
            echo '</div>';
		}

		function save_document_type_radio_selection($post_id) {
			if (isset($_POST['document_type_term'])) {
				$term_id = intval($_POST['document_type_term']);
				wp_set_post_terms($post_id, [$term_id], 'document-type');
			}
            if (!empty($_POST['new_document_type_term'])) {
                $new_term = sanitize_text_field($_POST['new_document_type_term']);
                $term = term_exists($new_term, 'document-type');
                if (!$term) {
                    $term = wp_insert_term($new_term, 'document-type');
                }
                if (!is_wp_error($term)) {
                    wp_set_post_terms($post_id, [$term['term_id']], 'document-type');
                }
            }
		}
		add_action('save_post', 'file_post_type\save_document_type_radio_selection');

    }
}
