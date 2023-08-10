<?php
/**
* Plugin Name: WhatsApp Chat Button Rotator
* Description: Plugin tombol chat WhatsApp Rotator berdasarkan schedule yang melayang dan bisa dikostumisasi di situs web Anda dan widget untuk menampilkan nomor WhatsApp.
* Version: 1.0.2
* Author: bungrahman
* License: GNU General Public License v2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wbc-rotator
*/

// Load stylesheet and script
function wcb_enqueue_scripts() {
    wp_enqueue_style('wcb-style', plugin_dir_url(__FILE__) . '/assets/css/style.css');
wp_enqueue_script('wcb-script', plugin_dir_url(__FILE__) . '/assets/js/script.js', array('jquery'), '1.0', true);

}
add_action('wp_enqueue_scripts', 'wcb_enqueue_scripts');

// Register WhatsApp Chat Button Widget
function wcb_register_widget() {
    register_widget('WhatsApp_Chat_Button_Widget');
}
add_action('widgets_init', 'wcb_register_widget');

$includes_path = plugin_dir_path( __FILE__ ) . 'includes/';

function wcb_normalize_whatsapp_number($number) {
    $number = preg_replace('/[^0-9]/', '', $number); // Hapus karakter non-angka
    if (substr($number, 0, 1) === '0') {
        $number = '62' . substr($number, 1); // Ganti 0 dengan 62
    } elseif (substr($number, 0, 1) === '+') {
        $number = substr($number, 1); // Hapus karakter '+' jika ada
    }
    return $number;
}
// Menjalankan fungsi saat plugin diaktifkan
register_activation_hook( __FILE__, 'wcb_plugin_activation' );

// Menjalankan fungsi saat plugin dinonaktifkan
register_deactivation_hook( __FILE__, 'wcb_plugin_deactivation' );

// Fungsi untuk dijalankan saat plugin diaktifkan
function wcb_plugin_activation() {
    require_once( plugin_dir_path( __FILE__ ) . 'includes/create-table.php' );
    wcb_create_table();
    
    add_action( 'admin_notices', 'wcb_activation_notice' );
}

function wcb_activation_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html__( 'Plugin telah diaktifkan!.', 'wbc-rotator' ); ?></p>
    </div>
    <?php
}
// Menambahkan tautan pada daftar tindakan plugin
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wcb_plugin_action_links' );

function wcb_plugin_action_links( $links ) {
    // Tautan "Visit plugin site"
    $links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wcb-settings' ) ) . '">' . esc_html__( 'Settings', 'wbc-rotator' ) . '</a>';

    return $links;
}

// Menambahkan tautan pada deskripsi plugin
add_filter( 'plugin_row_meta', 'wcb_plugin_row_meta', 10, 2 );

function wcb_plugin_row_meta( $links, $file ) {
    if ( plugin_basename( __FILE__ ) === $file ) {
        $links[] = '<a href="' . esc_url( 'https://wpai.biz.id/wbc-rotator' ) . '" target="_blank">' . esc_html__( 'Visit plugin site', 'wbc-rotator' ) . '</a>';
    }

    return $links;
}
// Fungsi untuk dijalankan saat plugin dinonaktifkan
function wcb_plugin_deactivation() {
    // Panggil berkas lainnya jika diperlukan
    require_once( plugin_dir_path( __FILE__ ) . 'includes/delete-table.php' );
    delete_option('wcb_license_key');
    delete_option('wcb_styling_options');
    wcb_delete_table();
}

require_once( plugin_dir_path( __FILE__ ) . 'includes/menu-admin.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/wbc-btn-shortcode.php' );

// WhatsApp Chat Button Widget
    class WhatsApp_Chat_Button_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'whatsapp_chat_button_widget',
            'WhatsApp Chat Button Widget',
            array('description' => 'Widget untuk menampilkan List CS.')
        );
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $number = isset($instance['number']) ? esc_attr($instance['number']) : '';
        $random_number = wcb_get_random_whatsapp_number();
        echo do_shortcode('[whatsapp_chat number="' . esc_attr($random_number) . '"]');
        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        echo '<div class="whatsapp-chat-rotator">';
        echo '<a href="https://wa.me/' . esc_attr($number) . '" class="whatsapp-chat-button" data-number="' . esc_attr($number) . '">Chat Sekarang</a>';
        echo '</div>';
        echo $args['after_widget'];
    }
    


    public function form($instance) {
        // Kode untuk menampilkan form pengaturan widget
        $number = isset($instance['number']) ? esc_attr($instance['number']) : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">WhatsApp Number:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        // Kode untuk mengupdate data widget
        $instance = array();
        $instance['number'] = (!empty($new_instance['number'])) ? sanitize_text_field($new_instance['number']) : '';
        return $instance;
    }
}
// AJAX action to get all WhatsApp numbers
add_action('wp_ajax_wcb_get_whatsapp_numbers', 'wcb_get_whatsapp_numbers');
add_action('wp_ajax_nopriv_wcb_get_whatsapp_numbers', 'wcb_get_whatsapp_numbers');

function wcb_get_whatsapp_numbers() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_numbers';
    $numbers = $wpdb->get_col("SELECT whatsapp_number FROM $table_name ORDER BY id DESC");

    wp_send_json(array('numbers' => $numbers));
}
function wcb_get_random_whatsapp_number() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_numbers';
    $numbers = $wpdb->get_col("SELECT whatsapp_number FROM $table_name");
    if (empty($numbers)) {
        return ''; // If no number is set, return an empty string
    }
    $random_number = $numbers[array_rand($numbers)]; // Pick a random number from the list
    return $random_number;
}
require_once( plugin_dir_path( __FILE__ ) . 'includes/wbc-contact-shortcode.php' );

