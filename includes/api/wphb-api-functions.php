<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

const API_NAMESPACE = 'wp-hotel-booking/v1';

global $wpdb;

if ( !function_exists( 'v1_booking_items' ) ) {

    function v1_booking_items() {
        global $wpdb;
        $query = $wpdb->prepare( "
                    SELECT item.*, im.meta_key, im.meta_value FROM $wpdb->hotel_booking_order_items AS item
                        RIGHT JOIN $wpdb->posts AS booking ON item.order_id = booking.ID
                        RIGHT JOIN wp_hotel_booking_order_itemmeta AS im ON im.hotel_booking_order_item_id = item.order_item_id
                        WHERE item.order_id IS NOT NULL
                        ORDER BY item.order_item_id;
                ", []);

        // $result = $wpdb->get_results( $query );
        $rows = $wpdb->get_results( $query );
        $result = array();
        $current_booking_item = array();

        foreach ($rows as $row){
            if (!$current_booking_item['order_item_id']) {
                $current_booking_item = (array) $row;
                continue;
            }
            if ($current_booking_item['order_item_id'] && $current_booking_item['order_item_id'] != $row->order_item_id) {
                array_push($result, $current_booking_item);
                $current_booking_item =  (array) $row;
                continue;
            }
            $valid_fields = array('qty', 'total');
            if(in_array($row->meta_key, $valid_fields)) {
              $current_booking_item = array_merge($current_booking_item, array($row->meta_key => $row->meta_value));
            }
        }
        array_push($result, $current_booking_item);

        header('Cache-control: private');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: filename=booking-items.csv');

        flush();
        $out = fopen('php://output', 'w');

        fputcsv($out, array_keys($result[0]));

        foreach($result as $row)
        {
           fputcsv($out, array_values($row));
        }
        fclose($out);
        exit();
        return $result;
    }

}

if ( !function_exists( 'v1_bookings' ) ) {

    function v1_bookings() {
        global $wpdb;
        $query = $wpdb->prepare( "
                    SELECT ID, post_date, post_content, post_status, meta.meta_value, meta.meta_key FROM $wpdb->posts as post, wp_postmeta as meta WHERE meta.post_id = post.ID and post.post_type = 'hb_booking' ORDER BY ID
                ", []);
        $rows = $wpdb->get_results( $query );

        $result=array();
        $current_booking = array();

        foreach ($rows as $row){
            if (!$current_booking['ID']) {
                $current_booking = (array) $row;
                continue;
            }
            if ($current_booking['ID'] && $current_booking['ID'] != $row->ID) {
                array_push($result, $current_booking);
                $current_booking =  (array) $row;
                continue;
            }
            $valid_fields = array('_hb_advance_payment', '_hb_customer_title', '_hb_customer_first_name', '_hb_customer_last_name', '_hb_customer_address', '_hb_customer_city', '_hb_customer_state', '_hb_customer_postal_code', '_hb_customer_country', '_hb_customer_phone', '_hb_customer_email', '_hb_customer_fax', '_hb_booking_key', '_edit_lock');
            if(in_array($row->meta_key, $valid_fields)) {
              $current_booking = array_merge($current_booking, array($row->meta_key => $row->meta_value));
            }
        }
        array_push($result, $current_booking);
        header('Cache-control: private');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: filename=bookings.csv');

        flush();
        $out = fopen('php://output', 'w');

        fputcsv($out, array_keys($result[0]));

        foreach($result as $row)
        {
           fputcsv($out, array_values($row));
        }
        // fputcsv($out, [1,2,3]);
        fclose($out);
        exit();
        return $result;

    }

}


if ( !function_exists( 'hotel_booking_api_init' ) ) {

    function hotel_booking_api_init() {
        register_rest_route( API_NAMESPACE, '/booking-items/', array(
            'methods' => 'GET',
            'callback' => 'v1_booking_items',
        ));

        register_rest_route( API_NAMESPACE, '/bookings/', array(
            'methods' => 'GET',
            'callback' => 'v1_bookings',
        ) );

        global $wp_post_types;

        //be sure to set this to the name of your post type!
        $post_type_name = 'hb_booking';
        if( isset( $wp_post_types[ $post_type_name ] ) ) {
            $wp_post_types[$post_type_name]->show_in_rest = true;
            $wp_post_types[$post_type_name]->rest_base = 'bookings';
            $wp_post_types[$post_type_name]->rest_controller_class = 'WP_REST_Posts_Controller';
        }

        global $wp_status_types;

    }

    add_action( 'rest_api_init', 'hotel_booking_api_init');

}
