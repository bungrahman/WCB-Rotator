  <?php

function wcb_whatsapp_chat_shortcode($atts) {
    // Get all WhatsApp numbers and their online/offline times from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_numbers';
    $numbers_info = $wpdb->get_results("SELECT whatsapp_number, online_time, offline_time FROM $table_name ORDER BY RAND()");

    if (empty($numbers_info)) {
        return ''; // If no number is set, do not display the button
    }
    $whatsapp_text = "Saya ingin bertanya";
    $options = get_option('wcb_styling_options');
    $distance_from_bottom = isset($options['distance_from_bottom']) ? $options['distance_from_bottom'] : '';
    $distance_from_right = isset($options['distance_from_right']) ? $options['distance_from_right'] : '';
    $button_color = isset($options['button_color']) ? $options['button_color'] : '';
    $text_color = isset($options['text_color']) ? $options['text_color'] : '';
    $border_color = isset($options['border_color']) ? $options['border_color'] : '';
    $border_size = isset($options['border_size']) ? $options['border_size'] : '';
    $border_radius = isset($options['border_radius']) ? $options['border_radius'] : '';
    $chat_button_text = isset($options['chat_button_text']) ? $options['chat_button_text'] : 'Chat Now';


    // Generate the HTML for the rotating WhatsApp button with data attributes
    $output = '<div class="whatsapp-chat-rotator" style="bottom: ' . esc_attr($distance_from_bottom) . 'px; right: ' . esc_attr($distance_from_right) . 'px;">';
    foreach ($numbers_info as $info) {
        $output .= '<a href="https://wa.me/' . esc_attr($info->whatsapp_number) . '" class="whatsapp-chat-button" 
                data-number="' . esc_attr($info->whatsapp_number) . '" 
                data-online-time="' . esc_attr($info->online_time) . '"
                data-offline-time="' . esc_attr($info->offline_time) . '"
                style="background-color: ' . esc_attr($button_color) . ';
                       color: ' . esc_attr($text_color) . ';
                       border-width: ' . esc_attr($border_size) . 'px;
                       border-color: ' . esc_attr($border_color) . ';
                       border-radius: ' . esc_attr($border_radius) . 'px;
                       ">' . esc_html($chat_button_text) . '</a>';
    }
    $output .= '<script>';
$output .= 'var defaultWhatsAppText = "' . esc_js($whatsapp_text) . '";'; // Mengirim nilai default
$output .= '</script>';
    $output .= '</div>';
    
    return $output;
}
add_shortcode('whatsapp_chat', 'wcb_whatsapp_chat_shortcode');
