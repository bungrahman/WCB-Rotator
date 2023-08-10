<?php

/** function wcb_menu() {
    $allowed_roles = array( 'subscriber', 'manage_options' ); // Peran yang diizinkan untuk melihat menu

    if ( array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
        add_menu_page(
            'WCB Schedule Setting',
            'WCB Settings',
            'manage_options',
            'wcb-settings',
            'wcb_settings_page',
            'dashicons-whatsapp', // Icon for the menu item
            30 // Menu position
        );

        // Add a submenu page for button styling settings
        if ( in_array( 'subscriber', wp_get_current_user()->roles ) ) {
            add_submenu_page(
                'wcb-settings', // Parent slug
                'Button Styling Settings', // Page title
                'WCB Button Styling', // Menu title
                'manage_options', // Capability
                'wcb-styling-settings', // Menu slug
                'wcb_styling_settings_page' // Callback function
            );
        }
    }
}
add_action( 'admin_menu', 'wcb_menu' ); */

// Add admin menu for plugin settings
 function wcb_menu() {
    
    add_menu_page(
        'WCB Schedule Setting',
        'WCB Settings',
        'manage_options',
        'wcb-settings',
        'wcb_settings_page',
        'dashicons-whatsapp', // Icon for the menu item
        30 // Menu position
    );
    add_submenu_page(
        'wcb-settings', // Parent slug
        'Input License Key', // Page title
        'Input License', // Menu title
        'manage_options', // Capability
        'wcb-license-page', // Menu slug
        'wcb_license_key_page' // Callback function
    );
    add_submenu_page(
                'wcb-settings', // Parent slug
                'Button Styling Settings', // Page title
                'WCB Button Styling', // Menu title
                'manage_options', // Capability
                'wcb-styling-settings', // Menu slug
                'wcb_styling_settings_page' // Callback function
            );
}
add_action('admin_menu', 'wcb_menu');

function wcb_license_key_page() {
    if (isset($_POST['submit_license'])) {
        $license_key = sanitize_text_field($_POST['license_key']);
        $site_url = get_site_url();
        $md5_hash = md5($site_url);

        if ($license_key === $md5_hash) {
            update_option('wcb_license_key', $license_key);
            echo '<div class="notice notice-success"><p>License key validated successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Invalid license key.</p></div>';
        }
    }

    $current_license_key = get_option('wcb_license_key', '');
    ?>
    <div class="wrap">
        <h2>License Key</h2>
        <form method="post" action="">
            <label for="license_key">Enter your license key:</label><br>
            <input type="text" id="license_key" name="license_key" required value="<?php echo esc_attr($current_license_key); ?>"><br><br>
            <input type="submit" name="submit_license" class="button button-primary" value="Validate License">
        </form>
        <?php
        /**if (isset($md5_hash)) {
            echo '<p>MD5 Hash of Site URL: ' . esc_html($md5_hash) . '</p>';
        }*/
        ?>
    </div>
    <?php
}

// Callback function to display the plugin settings page
function wcb_settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_numbers';
    $show_edit_form = false; // Default to hide the edit form
    $show_add_form = true; // Default to show the add form

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wcb_submit'])) {
        // Process form submission
        $user_name = sanitize_text_field($_POST['user_name']);
        $whatsapp_number = wcb_normalize_whatsapp_number($_POST['whatsapp_number']);
        $online_time = sanitize_text_field($_POST['online_time']);
        $offline_time = sanitize_text_field($_POST['offline_time']);

        if (!empty($user_name) && !empty($whatsapp_number) && !empty($online_time) && !empty($offline_time)) {
            $wpdb->insert(
                $table_name,
                array(
                    'user_name' => $user_name,
                    'whatsapp_number' => $whatsapp_number,
                    'online_time' => $online_time,
                    'offline_time' => $offline_time,
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                )
            );
        }
        
    
        $show_add_form = true;
        add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p>WhatsApp number added successfully.</p></div>';
    });
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wcb_edit_submit'])) {
    $edit_id = absint($_POST['edit_id']);
    $edit_user_name = sanitize_text_field($_POST['edit_user_name']);
    $edit_whatsapp_number = wcb_normalize_whatsapp_number($_POST['edit_whatsapp_number']);
    $edit_online_time = sanitize_text_field($_POST['edit_online_time']);
    $edit_offline_time = sanitize_text_field($_POST['edit_offline_time']);
    
    if (!empty($edit_user_name) && !empty($edit_whatsapp_number) && !empty($edit_online_time) && !empty($edit_offline_time)) {
        $wpdb->update(
            $table_name,
            array(
                'user_name' => $edit_user_name,
                'whatsapp_number' => $edit_whatsapp_number,
                'online_time' => $edit_online_time,
                'offline_time' => $edit_offline_time,
            ),
            array('id' => $edit_id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
    }
    $show_add_form = true;
    $show_edit_form = false;
    add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p>WhatsApp number edited successfully.</p></div>';
    });
    
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        // Process delete action
        $id = absint($_GET['id']);
        $wpdb->delete($table_name, array('id' => $id), array('%d'));
    }
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        // Display edit form
        $id = absint($_GET['id']);
        $result = $wpdb->get_row($wpdb->prepare("SELECT id, user_name, whatsapp_number, online_time, offline_time FROM $table_name WHERE id = %d", $id));
    
        $show_edit_form = true;
        $show_add_form = false;
        add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p>WhatsApp number deleted successfully.</p></div>';
    });
    }

    // Display settings page content
    ?>
    <h1 class="wp-heading-inline">WhatsApp Chat Button Settings</h1>
    <hr class="wp-header-end">
    <p>Settings page to add, edit and delete whatsapp customer service numbers and online schedules, use the <strong>[whatsapp_chat]</strong> shortcode in the footer so that it appears anywhere, and use the <strong>[whatsapp_contacts]</strong> shortcode to display the customer service list on the desired page</p>
    <div class="wrap"></div>
    <div id="col-container" class="wp-clearfix">
        <div id="col-left">
            <div class="col-wrap">
                <?php if ($show_add_form): ?>
                <div class="form-wrap">
                    <h2>Add WhatsApp Number</h2>
                    <form method="post">
                        <label for="user_name">CS Name:</label>
                        <input type="text" id="user_name" name="user_name" required>
                        <br>
                        <label for="whatsapp_number">WhatsApp Number:</label>
                        <input type="text" id="whatsapp_number" name="whatsapp_number" required>
                        <br>
                        <label for="online_time">Online Time (HH:MM):</label>
                        <input type="text" id="online_time" name="online_time" placeholder="HH:MM" required>
                        <br>
                        <label for="offline_time">Offline Time (HH:MM):</label>
                        <input type="text" id="offline_time" name="offline_time" placeholder="HH:MM" required>
                        <br>
            
                        <button type="submit" name="wcb_submit" class="button-primary">Add New</button>
                    </form>
                </div>
                <?php endif; ?>
                <?php if ($show_edit_form): ?>
                <div class="form-wrap">
                    <h2>Edit WhatsApp Number</h2>
                    <form method="post">
                        <input type="hidden" name="edit_id" value="<?php echo $result->id; ?>">
                        <label for="edit_user_name">CS Name:</label>
                        <input type="text" id="edit_user_name" name="edit_user_name" value="<?php echo esc_attr($result->user_name); ?>" required>
                        <br>
                        <label for="edit_whatsapp_number">WhatsApp Number:</label>
                        <input type="text" id="edit_whatsapp_number" name="edit_whatsapp_number" value="<?php echo esc_attr($result->whatsapp_number); ?>" required>
                        <br>
                        <label for="edit_online_time">Online Time</label>
                        <input type="text" id="edit_online_time" name="edit_online_time" value="<?php echo esc_attr($result->online_time); ?>" required>
                        <br>
                        <label for="edit_offline_time">Offline Time</label>
                        <input type="text" id="edit_offline_time" name="edit_offline_time" value="<?php echo esc_attr($result->offline_time); ?>" required>
                        <br>
    
                        <button type="submit" name="wcb_edit_submit" class="button-primary">Save Changes</button>
                    </form>
                    <br>
                    <a href="<?php echo admin_url('admin.php?page=wcb-settings'); ?>" class="button-secondary">Add New</a>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
        <div id="col-right">
            <div class="col-wrap">
                <h2>Costumer Services List</h2>
                
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th>Costumer Services Name</th>
                            <th>WhatsApp Number</th>
                            <th>Online</th>
                            <th>Offline</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $results = $wpdb->get_results("SELECT id, user_name, whatsapp_number, online_time, offline_time FROM $table_name");
                        foreach ($results as $row) {
                            echo "<tr>";
                            echo "<td>" . esc_html($row->user_name) . "</td>";
                            echo "<td>" . esc_html($row->whatsapp_number) . "</td>";
                             echo "<td>" . esc_html($row->online_time) . "</td>"; // Tambahkan ini
                            echo "<td>" . esc_html($row->offline_time) . "</td>"; // Tambahkan ini
                            echo "<td><a href='" . esc_url(add_query_arg(array('action' => 'edit', 'id' => $row->id))) . "'>Edit</a> | <a href='" . esc_url(add_query_arg(array('action' => 'delete', 'id' => $row->id))) . "'>Delete</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}
// Callback function to display the plugin styling settings page
function wcb_styling_settings_page() {
    $license_key = get_option('wcb_license_key', '');

    if ($license_key !== '') {
        ?>
    <div class="wrap">
        <h1>Button Styling Settings</h1>
        <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']) { ?>
            <div id="message" class="updated notice is-dismissible">
                <p><strong>Settings saved.</strong></p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
        <?php } ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('wcb-styling-options');
            do_settings_sections('wcb-styling-settings');
            submit_button();
            ?>
        </form>
        <script>
            jQuery(document).ready(function($) {
                $('.notice-dismiss').on('click', function() {
                    $(this).closest('.notice').fadeOut();
                });
            });
        </script>
    </div>
    <?php
    } else {
        echo '<div class="notice notice-error"><p>You do not have access to this page without a valid license key.</p></div>';
            }
    
}

function wcb_input_callback($args) {
    $options = get_option('wcb_styling_options');
    $value = isset($options[$args['name']]) ? $options[$args['name']] : '';

    if ($args['type'] === 'color') {
        echo '<input type="color" name="wcb_styling_options[' . esc_attr($args['name']) . ']" value="' . esc_attr($value) . '">';
    } elseif ($args['type'] === 'number') {
        echo '<input type="number" name="wcb_styling_options[' . esc_attr($args['name']) . ']" value="' . esc_attr($value) . '" min="0"> ' . esc_html($args['suffix']);
    } elseif ($args['type'] === 'text') {
        echo '<input type="text" name="wcb_styling_options[' . esc_attr($args['name']) . ']" value="' . esc_attr($value) . '">';
    }
}

function wcb_styling_section_callback() {
    echo '<p>Customize the styling of the WhatsApp chat button.</p>';
}
function wcb_button_text_callback() {
    $options = get_option('wcb_styling_options');
    $chat_button_text = isset($options['chat_button_text']) ? $options['chat_button_text'] : '';
    echo '<input type="text" name="wcb_styling_options[chat_button_text]" value="' . esc_attr($chat_button_text) . '">';
}


function wcb_styling_settings_init() {
    register_setting('wcb-styling-options', 'wcb_styling_options');
    add_settings_section(
        'wcb-styling-section',
        'Button Styling Options',
        'wcb_styling_section_callback',
        'wcb-styling-settings'
    );

    $fields = array(
        'distance_from_bottom' => array('Adjust Distance from Bottom', 'number', 'px'),
        'distance_from_right' => array('Adjust Distance from Right', 'number', 'px'),
        'button_color' => array('Button Color', 'color'),
        'text_color' => array('Text Color', 'color'),
        'border_color' => array('Border Color', 'color'),
        'border_size' => array('Border Size', 'number', 'px'),
        'border_radius' => array('Border Radius', 'number', 'px'),
        'chat_button_text' => array('Chat Button Text', 'text'),
    );

    foreach ($fields as $field_name => $field_data) {
        $args = array(
            'name' => $field_name,
            'type' => $field_data[1],
            'suffix' => isset($field_data[2]) ? $field_data[2] : '',
        );

        add_settings_field(
            'wcb-' . esc_attr($field_name),
            $field_data[0],
            function () use ($args) {
                wcb_input_callback($args);
            },
            'wcb-styling-settings',
            'wcb-styling-section'
        );
    }
}
add_action('admin_init', 'wcb_styling_settings_init');
