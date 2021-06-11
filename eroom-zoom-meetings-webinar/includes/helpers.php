<?php
/**
 * @return array of timezones
 */
function stm_zoom_get_timezone_options() {
    $zones_array = array(
        "Pacific/Midway"                 => "(GMT-11:00) Midway Island, Samoa ",
        "Pacific/Pago_Pago"              => "(GMT-11:00) Pago Pago ",
        "Pacific/Honolulu"               => "(GMT-10:00) Hawaii ",
        "America/Anchorage"              => "(GMT-8:00) Alaska ",
        "America/Juneau"                 => "(GMT-8:00) Juneau ",
        "America/Vancouver"              => "(GMT-7:00) Vancouver ",
        "America/Los_Angeles"            => "(GMT-7:00) Pacific Time (US and Canada) ",
        "America/Tijuana"                => "(GMT-7:00) Tijuana ",
        "America/Phoenix"                => "(GMT-7:00) Arizona ",
        "America/Mazatlan"               => "(GMT-7:00) Mazatlan ",
        "America/Chihuahua"              => "(GMT-7:00) Chihuahua ",
        "America/Edmonton"               => "(GMT-6:00) Edmonton ",
        "America/Denver"                 => "(GMT-6:00) Mountain Time (US and Canada) ",
        "America/Regina"                 => "(GMT-6:00) Saskatchewan ",
        "America/Guatemala"              => "(GMT-6:00) Guatemala ",
        "America/Mexico_City"            => "(GMT-6:00) Mexico City ",
        "America/El_Salvador"            => "(GMT-6:00) El Salvador ",
        "America/Managua"                => "(GMT-6:00) Managua ",
        "America/Costa_Rica"             => "(GMT-6:00) Costa Rica ",
        "America/Tegucigalpa"            => "(GMT-6:00) Tegucigalpa ",
        "America/Monterrey"              => "(GMT-6:00) Monterrey ",
        "America/Winnipeg"               => "(GMT-5:00) Winnipeg ",
        "America/Chicago"                => "(GMT-5:00) Central Time (US and Canada) ",
        "America/Panama"                 => "(GMT-5:00) Panama ",
        "America/Bogota"                 => "(GMT-5:00) Bogota ",
        "America/Lima"                   => "(GMT-5:00) Lima ",
        "America/Montreal"               => "(GMT-4:00) Montreal ",
        "America/New_York"               => "(GMT-4:00) Eastern Time (US and Canada) ",
        "America/Indianapolis"           => "(GMT-4:00) Indiana (East) ",
        "America/Puerto_Rico"            => "(GMT-4:00) Puerto Rico ",
        "America/Caracas"                => "(GMT-4:00) Caracas ",
        "America/La_Paz"                 => "(GMT-4:00) La Paz ",
        "America/Guyana"                 => "(GMT-4:00) Guyana ",
        "America/Halifax"                => "(GMT-3:00) Halifax ",
        "America/Santiago"               => "(GMT-3:00) Santiago ",
        "America/Montevideo"             => "(GMT-3:00) Montevideo ",
        "America/Araguaina"              => "(GMT-3:00) Recife ",
        "America/Argentina/Buenos_Aires" => "(GMT-3:00) Buenos Aires ",
        "America/Godthab"                => "(GMT-3:00) Greenland ",
        "America/Sao_Paulo"              => "(GMT-3:00) Sao Paulo ",
        "Canada/Atlantic"                => "(GMT-3:00) Atlantic Time (Canada) ",
        "America/St_Johns"               => "(GMT-2:30) Newfoundland and Labrador ",
        "Atlantic/Cape_Verde"            => "(GMT-1:00) Cape Verde Islands ",
        "Atlantic/Azores"                => "(GMT+0:00) Azores ",
        "UTC"                            => "(GMT+0:00) Universal Time UTC ",
        "Etc/Greenwich"                  => "(GMT+0:00) Greenwich Mean Time ",
        "Atlantic/Reykjavik"             => "(GMT+0:00) Reykjavik ",
        "Europe/Dublin"                  => "(GMT+0:00) Dublin ",
        "Europe/London"                  => "(GMT+0:00) London ",
        "Europe/Lisbon"                  => "(GMT+0:00) Lisbon ",
        "Africa/Nouakchott"              => "(GMT+0:00) Nouakchott ",
        "Europe/Belgrade"                => "(GMT+1:00) Belgrade, Bratislava, Ljubljana ",
        "CET"                            => "(GMT+1:00) Sarajevo, Skopje, Zagreb ",
        "Africa/Casablanca"              => "(GMT+1:00) Casablanca ",
        "Europe/Oslo"                    => "(GMT+1:00) Oslo ",
        "Europe/Copenhagen"              => "(GMT+1:00) Copenhagen ",
        "Europe/Brussels"                => "(GMT+1:00) Brussels ",
        "Europe/Berlin"                  => "(GMT+1:00) Amsterdam, Berlin, Rome, Stockholm, Vienna ",
        "Europe/Amsterdam"               => "(GMT+1:00) Amsterdam ",
        "Europe/Rome"                    => "(GMT+1:00) Rome ",
        "Europe/Stockholm"               => "(GMT+1:00) Stockholm ",
        "Europe/Vienna"                  => "(GMT+1:00) Vienna ",
        "Europe/Luxembourg"              => "(GMT+1:00) Luxembourg ",
        "Europe/Paris"                   => "(GMT+1:00) Paris ",
        "Europe/Zurich"                  => "(GMT+1:00) Zurich ",
        "Europe/Madrid"                  => "(GMT+1:00) Madrid ",
        "Africa/Bangui"                  => "(GMT+1:00) West Central Africa ",
        "Africa/Algiers"                 => "(GMT+1:00) Algiers ",
        "Africa/Tunis"                   => "(GMT+1:00) Tunis ",
        "Europe/Warsaw"                  => "(GMT+1:00) Warsaw ",
        "Europe/Prague"                  => "(GMT+1:00) Prague Bratislava ",
        "Europe/Budapest"                => "(GMT+1:00) Budapest ",
        "Europe/Helsinki"                => "(GMT+2:00) Helsinki ",
        "Africa/Harare"                  => "(GMT+2:00) Harare, Pretoria ",
        "Europe/Sofia"                   => "(GMT+2:00) Sofia ",
        "Europe/Athens"                  => "(GMT+2:00) Athens ",
        "Europe/Bucharest"               => "(GMT+2:00) Bucharest ",
        "Asia/Nicosia"                   => "(GMT+2:00) Nicosia ",
        "Asia/Beirut"                    => "(GMT+2:00) Beirut ",
        "Asia/Jerusalem"                 => "(GMT+2:00) Jerusalem ",
        "Africa/Tripoli"                 => "(GMT+2:00) Tripoli ",
        "Africa/Cairo"                   => "(GMT+2:00) Cairo ",
        "Africa/Johannesburg"            => "(GMT+2:00) Johannesburg ",
        "Africa/Khartoum"                => "(GMT+2:00) Khartoum ",
        "Europe/Kiev"                    => "(GMT+2:00) Kiev ",
        "Africa/Nairobi"                 => "(GMT+3:00) Nairobi ",
        "Europe/Istanbul"                => "(GMT+3:00) Istanbul ",
        "Asia/Damascus"                  => "(GMT+3:00) Damascus ",
        "Asia/Amman"                     => "(GMT+3:00) Amman ",
        "Europe/Moscow"                  => "(GMT+3:00) Moscow ",
        "Asia/Baghdad"                   => "(GMT+3:00) Baghdad ",
        "Asia/Kuwait"                    => "(GMT+3:00) Kuwait ",
        "Asia/Riyadh"                    => "(GMT+3:00) Riyadh ",
        "Asia/Bahrain"                   => "(GMT+3:00) Bahrain ",
        "Asia/Qatar"                     => "(GMT+3:00) Qatar ",
        "Asia/Aden"                      => "(GMT+3:00) Aden ",
        "Africa/Djibouti"                => "(GMT+3:00) Djibouti ",
        "Africa/Mogadishu"               => "(GMT+3:00) Mogadishu ",
        "Europe/Minsk"                   => "(GMT+3:00) Minsk ",
        "Asia/Tehran"                    => "(GMT+3:30) Tehran ",
        "Asia/Dubai"                     => "(GMT+4:00) Dubai ",
        "Asia/Muscat"                    => "(GMT+4:00) Muscat ",
        "Asia/Baku"                      => "(GMT+4:00) Baku, Tbilisi, Yerevan ",
        "Asia/Kabul"                     => "(GMT+4:30) Kabul ",
        "Asia/Yekaterinburg"             => "(GMT+5:00) Yekaterinburg ",
        "Asia/Tashkent"                  => "(GMT+5:00) Islamabad, Karachi, Tashkent ",
        "Asia/Calcutta"                  => "(GMT+5:30) India ",
        "Asia/Kolkata"                   => "(GMT+5:30) Mumbai, Kolkata, New Delhi ",
        "Asia/Kathmandu"                 => "(GMT+5:45) Kathmandu ",
        "Asia/Almaty"                    => "(GMT+6:00) Almaty ",
        "Asia/Dacca"                     => "(GMT+6:00) Dacca ",
        "Asia/Dhaka"                     => "(GMT+6:00) Astana, Dhaka ",
        "Asia/Rangoon"                   => "(GMT+6:30) Rangoon ",
        "Asia/Novosibirsk"               => "(GMT+7:00) Novosibirsk ",
        "Asia/Krasnoyarsk"               => "(GMT+7:00) Krasnoyarsk ",
        "Asia/Bangkok"                   => "(GMT+7:00) Bangkok ",
        "Asia/Saigon"                    => "(GMT+7:00) Vietnam ",
        "Asia/Jakarta"                   => "(GMT+7:00) Jakarta ",
        "Asia/Irkutsk"                   => "(GMT+8:00) Irkutsk ",
        "Asia/Shanghai"                  => "(GMT+8:00) Beijing ",
        "Asia/Hong_Kong"                 => "(GMT+8:00) Hong Kong SAR ",
        "Asia/Taipei"                    => "(GMT+8:00) Taipei ",
        "Asia/Kuala_Lumpur"              => "(GMT+8:00) Kuala Lumpur ",
        "Asia/Singapore"                 => "(GMT+8:00) Singapore ",
        "Australia/Perth"                => "(GMT+8:00) Perth ",
        "Asia/Yakutsk"                   => "(GMT+9:00) Yakutsk ",
        "Asia/Seoul"                     => "(GMT+9:00) Seoul ",
        "Asia/Tokyo"                     => "(GMT+9:00) Osaka, Sapporo, Tokyo ",
        "Australia/Darwin"               => "(GMT+9:30) Darwin ",
        "Asia/Vladivostok"               => "(GMT+10:00) Vladivostok ",
        "Pacific/Port_Moresby"           => "(GMT+10:00) Guam, Port Moresby ",
        "Australia/Brisbane"             => "(GMT+10:00) Brisbane ",
        "Australia/Adelaide"             => "(GMT+10:30) Adelaide ",
        "Australia/Sydney"               => "(GMT+11:00) Canberra, Melbourne, Sydney ",
        "Australia/Hobart"               => "(GMT+11:00) Hobart ",
        "Asia/Magadan"                   => "(GMT+11:00) Magadan ",
        "SST"                            => "(GMT+11:00) Solomon Islands ",
        "Pacific/Noumea"                 => "(GMT+11:00) New Caledonia ",
        "Asia/Kamchatka"                 => "(GMT+12:00) Kamchatka ",
        "Pacific/Fiji"                   => "(GMT+12:00) Fiji Islands, Marshall Islands ",
        "Pacific/Auckland"               => "(GMT+13:00) Auckland, Wellington ",
        "Pacific/Apia"                   => "(GMT+14:00) Independent State of Samoa ",
    );

    return $zones_array;
}

/**
 * Require Admin Templates
 */
function admin_pages() {
    require_once STM_ZOOM_PATH . '/admin_templates/main.php';
}

/**
 * Get All Meetings
 * @return array
 */
function get_meetings() {
    $args = array(
        'numberposts' => -1,
        'post_type' => 'stm-zoom'
    );
    $results = array();
    $meetings = get_posts($args);
    foreach ( $meetings as $meeting ) {
        $results[ $meeting->ID ] = $meeting->post_title;
    }
    wp_reset_postdata();
    return $results;
}

/**
 * Get All Webinars
 * @return array
 */
function get_webinars() {
    $args = array(
        'numberposts' => -1,
        'post_type' => 'stm-zoom-webinar'
    );
    $results = array();
    $webinars = get_posts($args);
    foreach ( $webinars as $webinar ) {
        $results[ $webinar->ID ] = $webinar->post_title;
    }
    wp_reset_postdata();
    return $results;
}

/**
 * Get All Meetings and Webinars
 * @return array
 */
function get_meetings_webinars() {
    $args = array(
        'numberposts' => -1,
        'post_type' => array('stm-zoom', 'stm-zoom-webinar')
    );
    $results = array();
    $webinars = get_posts($args);
    foreach ( $webinars as $webinar ) {
        $results[ $webinar->ID ] = $webinar->post_title;
    }
    wp_reset_postdata();
    return $results;
}

/**
 * Template Manager
 * @param $file
 * @return bool|string
 */
function get_zoom_template( $file ) {
    $templates = array(
        get_stylesheet_directory() . '/eroom_templates/',
        get_template_directory() . '/eroom_templates/',
        STM_ZOOM_PATH . '/templates/',
    );

    $templates = apply_filters('stm_zoom_template_pathes', $templates);

    foreach($templates as $template) {
        if(file_exists($template . $file)) {
            return $template . $file;
        }
    }

    return false;
}

/**
 * Get Current Timezone
 * @return string
 */
function get_current_timezone() {
    $timezone_string = get_option( 'timezone_string' );
    if ( ! empty( $timezone_string ) ) {
        return $timezone_string;
    }

    $offset  = get_option( 'gmt_offset' );
    $hours   = (int) $offset;
    $minutes = abs( ( $offset - (int) $offset ) * 60 );
    $seconds = $hours * 60 * 60 + $minutes * 60;

    $timezone = timezone_name_from_abbr('', $seconds, 1);
    if ( $timezone === false )
        $timezone = timezone_name_from_abbr('', $seconds, 0);

    return $timezone;
}