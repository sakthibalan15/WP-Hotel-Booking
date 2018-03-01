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
                    SELECT item.* FROM $wpdb->hotel_booking_order_items AS item
                        RIGHT JOIN $wpdb->posts AS booking ON item.order_id = booking.ID
                        WHERE item.order_id IS NOT NULL ORDER BY item.order_item_id
                ", []);
        return $wpdb->get_results( $query );
    }

}

if ( !function_exists( 'v1_bookings' ) ) {

    function v1_bookings() {
        global $wpdb;
        $query = $wpdb->prepare( "
                    SELECT * FROM $wpdb->posts as post, wp_postmeta as meta WHERE meta.post_id = post.ID and post.post_type = 'hb_booking' ORDER BY ID
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
            $current_booking = array_merge($current_booking, array($row->meta_key => $row->meta_value));
        }
        array_push($result, $current_booking);
        return $result;

    }

}


if ( !function_exists( 'hotel_booking_api_init' ) ) {

    function hotel_booking_api_init() {
        register_rest_route( API_NAMESPACE, '/booking-items/', array(
            'methods' => 'GET',
            'callback' => 'v1_booking_items',
        ) );

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

