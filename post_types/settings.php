<?php


namespace {
if (!defined('ABSPATH')) // wp-config.php defines an ABSPATH variable, this trap ensures that only WP can use this code.
exit;
}

namespace gs_settings {
    function settings_admin_menu()
    {
        add_options_page(
            'Graduate Council Site Settings', // The text to be displayed in the title tags of the page when the menu is selected
            'Council Site Settings', // The text to be used for the menu
            'manage_options', // The capability required for this menu to be displayed to the user.
            'graduate_council_site_settings', // The slug name to refer to this menu by (should be unique for this menu).
            'gs_settings\graduate_council_site_settings_page' // The function to be called to output the content for this page.
        );
    }

    function graduate_council_site_settings_page()
    {
        ?>
        <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('graduate_council_site_settings');
            submit_button();
            do_settings_sections('graduate_council_site_settings');
            submit_button();
            ?>
        </form>
    <?php
    }

    function graduate_settings_section_callback()
    {
        //echo '<p>Intro text for our settings section</p>';
    }

    function graduate_settings_current_year_render()
    {
        $setting_current_year = trim(esc_attr(get_option('current_year')));
        echo "<input type='text' name='current_year' value='$setting_current_year' />";
    }
    function graduate_settings_years_render()
    {
        $setting_years = trim(esc_attr(get_option('years')));
        ?>
        <style>
            td, th {
                padding: 1px;
            }
            .table-edited {
                background: #ffeaea;
            }
            .settings-table {
                border-collapse: collapse;
            }
            .settings-table td {
                padding: 0;
            }
        </style>
        <input name="years" type="hidden" value="<?php echo $setting_years; ?>">
        <table id="years-table" class="settings-table">
            <thead>
            <tr>
                <th>Years</th>
                <th colspan="3">Actions</th>
            </tr>
            </thead>
            <tbody id="years-table-tbody"></tbody>
            <tfoot>
            <tr>
                <td colspan="3"><input id="years" type="text" style="width: 100%;"></td>
                <td><button type="button" class="button button-default" onclick="yearsControls.addYear()" style="width: 100%;">Add</button></td>
            </tr>
            </tfoot>
        </table>
        <script>
            var yearsControls = (function(){

                var yearsTable          = document.getElementById("years-table");
                var yearsTableTBody     = document.getElementById("years-table-tbody");
                var yearsAddText        = document.getElementById("years");
                var yearsHiddenInput    = document.getElementsByName("years")[0];

                function renderTBody( tbody, years ) {
                    var r = "";
                    for( var i = 0; i < years.length; i++ ) {
                        var year = years[ i ];

                        r += '<tr>';
                        r += '<td>' + year + '</td>';
                        r += '<td><button type="button" class="button button-default" onclick="yearsControls.upYear(\'' + year + '\')" style="width: 100%;">Up</button></td>';
                        r += '<td><button type="button" class="button button-default" onclick="yearsControls.downYear(\'' + year + '\')" style="width: 100%;">Down</button></td>';
                        r += '<td><button type="button" class="button button-default" onclick="yearsControls.removeYear(\'' + year + '\')" style="width: 100%;">Remove</button></td>';
                        r += '</tr>';
                    }
                    return r;
                }
                function readYears( input ) {
                    var years = [];

                    if( input.value !== '' )
                        years = input.value.split(',');

                    return years;
                }
                function updateYears( input, years ) {
                    yearsTableWasEdited();
                    input.value = years.join(',');
                }
                function upYear( year ) {
                    var years = readYears( yearsHiddenInput );

                    var index = years.indexOf( year );

                    var removed = years.splice( index, 1 );

                    index = index - 1;

                    if( index < 0 )
                        index = years.length;

                    years.splice( index, 0, removed );

                    updateYears( yearsHiddenInput, years );
                    yearsTableTBody.innerHTML = renderTBody( yearsTableTBody, years );
                }
                function downYear( year ) {
                    var years = readYears( yearsHiddenInput );
                    var index = years.indexOf( year );
                    var removed = years.splice( index, 1 );

                    index = index + 1;

                    if( index > years.length )
                        index = 0;

                    years.splice( index, 0, removed );

                    updateYears( yearsHiddenInput, years );
                    yearsTableTBody.innerHTML = renderTBody( yearsTableTBody, years );
                }
                function addYear() {
                    var years = readYears( yearsHiddenInput );
                    years.unshift( yearsAddText.value );

                    updateYears( yearsHiddenInput, years );
                    yearsTableTBody.innerHTML = renderTBody( yearsTableTBody, years );

                    yearsAddText.value = '';
                }
                function removeYear( year ) {
                    var years = readYears( yearsHiddenInput );

                    for( var i = 0; i < years.length; i++ ) {
                        if( years[ i ] == year ) {
                            years.splice( i, 1 );
                            break;
                        }
                    }

                    updateYears( yearsHiddenInput, years );
                    yearsTableTBody.innerHTML = renderTBody( yearsTableTBody, years );
                }
                function yearsTableWasEdited() {
                    if( ! yearsTable.className.indexOf("table-edited") > -1 )
                        yearsTable.className += " table-edited";
                }

                (function init(){
                    var years = readYears( yearsHiddenInput );

                    yearsTableTBody.innerHTML = renderTBody( yearsTableTBody, years );
                })();

                return {
                    addYear: addYear,
                    upYear: upYear,
                    downYear: downYear,
                    removeYear: removeYear
                }
            })();
        </script>
    <?php
    }
    function graduate_settings_college_render()
    {
        $setting_colleges = trim(esc_attr(get_option('colleges')));
        ?>
        <style>
            td, th {
                padding: 1px;
            }
            .table-edited {
                background: #ffeaea;
            }
            .settings-table {
                border-collapse: collapse;
            }
            .settings-table td {
                padding: 0;
            }
        </style>
        <input name="colleges" type="hidden" value="<?php echo $setting_colleges; ?>">
        <table id="colleges-table" class="settings-table">
            <thead>
            <tr>
                <th>Colleges</th>
                <th colspan="3">Actions</th>
            </tr>
            </thead>
            <tbody id="colleges-table-tbody"></tbody>
            <tfoot>
            <tr>
                <td colspan="3"><input id="colleges" type="text" style="width: 100%;"></td>
                <td><button type="button" class="button button-default" onclick="collegesControls.add()" style="width: 100%;">Add</button></td>
            </tr>
            </tfoot>
        </table>
        <script>
                var collegesControls = (function( controllerName ){

                var table          = document.getElementById("colleges-table");
                var tableTBody     = document.getElementById("colleges-table-tbody");
                var addText        = document.getElementById("colleges");
                var hiddenInput    = document.getElementsByName("colleges")[0];

                function renderTBody( tbody, items ) {
                    var r = "";
                    for( var i = 0; i < items.length; i++ ) {
                        var item = items[ i ];

                        r += '<tr>';
                        r += '<td>' + item + '</td>';
                        r += '<td><button type="button" class="button button-default" onclick="' + controllerName + '.up(\'' + item + '\')" style="width: 100%;">Up</button></td>';
                        r += '<td><button type="button" class="button button-default" onclick="' + controllerName + '.down(\'' + item + '\')" style="width: 100%;">Down</button></td>';
                        r += '<td><button type="button" class="button button-default" onclick="' + controllerName + '.remove(\'' + i + '\')" style="width: 100%;">Remove</button></td>';
                        r += '</tr>';
                    }
                    return r;
                }
                function read( input ) {
                    var items = [];

                    if ( input.value !== '' )
                        items = input.value.split( ',' );

                    return items;
                }
                // Given an array of items, updates the
                function updateHidden( input, items ) {
                    tableWasEdited();
                    input.value = items.join(',');
                }
                function up( item ) {
                    var items = read( hiddenInput );

                    var index = items.indexOf( item );

                    var removed = items.splice( index, 1 );

                    index = index - 1;

                    if( index < 0 )
                        index = items.length;

                    items.splice( index, 0, removed );

                    updateHidden( hiddenInput, items );
                    tableTBody.innerHTML = renderTBody( tableTBody, items );
                }
                function down( item ) {
                    var items = read( hiddenInput );
                    var index = items.indexOf( item );
                    var removed = items.splice( index, 1 );

                    index = index + 1;

                    if( index > items.length )
                        index = 0;

                    items.splice( index, 0, removed );

                    updateHidden( hiddenInput, items );
                    tableTBody.innerHTML = renderTBody( tableTBody, items );
                }
                function add() {
                    var items = read( hiddenInput );
                    items.push( addText.value );

                    updateHidden( hiddenInput, items );
                    tableTBody.innerHTML = renderTBody( tableTBody, items );

                    addText.value = '';
                }
                function remove( index ) {
                    var items = read( hiddenInput );

                    items.splice( index, 1 );

                    updateHidden( hiddenInput, items );
                    tableTBody.innerHTML = renderTBody( tableTBody, items );
                }
                function tableWasEdited() {
                    if( ! table.className.indexOf("table-edited") > -1 )
                        table.className += " table-edited";
                }

                (function init(){
                    var items = read( hiddenInput );

                    tableTBody.innerHTML = renderTBody( tableTBody, items );
                })();

                return {
                    up: up,
                    down: down,
                    add: add,
                    remove: remove
                }
            })("collegesControls");
        </script>
    <?php
    }

    function council_member_settings_init()
    {

        register_setting('graduate_council_site_settings', 'current_year');
        register_setting('graduate_council_site_settings', 'years');
        register_setting('graduate_council_site_settings', 'colleges');


        add_settings_section(
            'graduate_general_settings', // Slug-name to identify the section. Used in the 'id' attribute of tags.
            'General Settings', // Formatted title of the section. Shown as the heading for the section.
            'gs_settings\graduate_settings_section_callback', // Function that echos out any content at the top of the section (between heading and fields).
            'graduate_council_site_settings' // The slug-name of the settings page on which to show the section. Built-in pages include 'general', 'reading', 'writing', 'discussion', 'media', etc.
        );

        add_settings_field(
            'graduate_text_field_0', // String for use in the 'id' attribute of tags.
            'Current Year', // Title of the field.
            'gs_settings\graduate_settings_current_year_render', // Function that fills the field with the desired inputs as part of the larger form. Passed a single argument, the $args array. Name and id of the input should match the $id given to this function. The function should echo its output.
            'graduate_council_site_settings', // The menu page on which to display this field. Should match $menu_slug from add_theme_page() or from do_settings_sections().
            'graduate_general_settings' // The section of the settings page in which to show the box (default or a section you added with add_settings_section(), look at the page in the source to see what the existing ones are.)
        );

        add_settings_field(
            'graduate_text_field_1', // String for use in the 'id' attribute of tags.
            'Years', // Title of the field.
            'gs_settings\graduate_settings_years_render', // Function that fills the field with the desired inputs as part of the larger form. Passed a single argument, the $args array. Name and id of the input should match the $id given to this function. The function should echo its output.
            'graduate_council_site_settings', // The menu page on which to display this field. Should match $menu_slug from add_theme_page() or from do_settings_sections().
            'graduate_general_settings' // The section of the settings page in which to show the box (default or a section you added with add_settings_section(), look at the page in the source to see what the existing ones are.)
        );

        add_settings_field(
            'graduate_text_field_2', // String for use in the 'id' attribute of tags.
            'Colleges', // Title of the field.
            'gs_settings\graduate_settings_college_render', // Function that fills the field with the desired inputs as part of the larger form. Passed a single argument, the $args array. Name and id of the input should match the $id given to this function. The function should echo its output.
            'graduate_council_site_settings', // The menu page on which to display this field. Should match $menu_slug from add_theme_page() or from do_settings_sections().
            'graduate_general_settings' // The section of the settings page in which to show the box (default or a section you added with add_settings_section(), look at the page in the source to see what the existing ones are.)
        );
    }

    add_action('admin_init', 'gs_settings\council_member_settings_init');
    add_action('admin_menu', 'gs_settings\settings_admin_menu');
}