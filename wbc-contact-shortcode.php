<?php

function wcb_get_whatsapp_contacts() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_numbers';
    $contacts = $wpdb->get_results("SELECT user_name, whatsapp_number FROM $table_name");
    return $contacts;
}
function wcb_whatsapp_contacts_shortcode() {
    $contacts = wcb_get_whatsapp_contacts();

    if (empty($contacts)) {
        return ''; // If no contact is set, do not display the list
    }

    $output = '<ul class="whatsapp-contact-list">';
    foreach ($contacts as $contact) {
        $output .= '<li><span class="whatsapp-icon"></span>' . esc_html($contact->user_name) . ': ' . esc_html($contact->whatsapp_number) . '</li>';
    }
    $output .= '</ul>';
    return $output;
}
add_shortcode('whatsapp_contacts', 'wcb_whatsapp_contacts_shortcode');
