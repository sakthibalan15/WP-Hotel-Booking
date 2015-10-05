<?php
$cart = HB_Cart::instance();
global $hb_settings;
?>
<div id="hotel-booking-payment">

    <form name="hb-payment-form" id="hb-payment-form" method="post" action="<?php echo $search_page;?>">
        <h3><?php _e( 'Booking Rooms', 'tp-hotel-booking' );?></h3>
        <table>
            <thead>
                <th><?php _e( 'Room type', 'tp-hotel-booking' );?></th>
                <th><?php _e( 'Capacity', 'tp-hotel-booking' );?></th>
                <th><?php _e( 'Number of rooms', 'tp-hotel-booking' );?></th>
                <th><?php _e( 'Check - in', 'tp-hotel-booking' ); ?></th>
                <th><?php _e( 'Check - out', 'tp-hotel-booking' ); ?></th>
                <th><?php _e( 'Night', 'tp-hotel-booking' ); ?></th>
                <th><?php _e( 'Action', 'tp-hotel-booking' ); ?></th>
                <th class="hb-align-right"><?php _e( 'Gross Total', 'tp-hotel-booking' ); ?></th>
            </thead>
            <?php if( $rooms = $cart->get_rooms() ): ?>
                <?php foreach( $rooms as $room ): ?>
                        <?php
                            if( ( $num_of_rooms = (int)$room->get_data('quantity') ) == 0 ) continue;
                            $sub_total = $room->get_total( $room->check_in_date, $room->check_out_date, $num_of_rooms, false );
                        ?>
                        <tr>
                            <td><?php echo $room->name;?> (<?php echo $room->capacity_title;?>)</td>
                            <td><?php echo sprintf( _n( '%d adult', '%d adults', $room->capacity, 'tp-hotel-booking' ), $room->capacity );?> </td>
                            <td><input type="number" class="hb_room_number_edit" name="num_of_rooms[<?php echo $room->post->ID;?>]" value="<?php echo $num_of_rooms; ?>" /></td>
                            <td><?php echo $room->check_in_date ?></td>
                            <td><?php echo $room->check_out_date ?></td>
                            <td><?php echo hb_count_nights_two_dates( $room->check_out_date, $room->check_in_date) ?></td>
                            <td>
                                <a href="javascript:void(0)"  lcass="hb_update_cart_item"><?php _e( 'Update', 'tp-hotel-booking' ) ?></a>
                                <a href="javascript:void(0)" class="hb_remove_cart_item"><?php _e( 'Delete', 'tp-hotel-booking' ); ?></a>
                            </td>
                            <td class="hb-align-right">
                                <?php echo hb_format_price( $sub_total );?>
                                <input type="hidden" name="sub_total_of_rooms[<?php echo $room->post->ID;?>]" value="<?php echo $sub_total;?>" />
                            </td>
                        </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if( $hb_settings->get( 'enable_coupon' ) ){?>
                    <?php
                    if( $coupon = get_transient( 'hb_user_coupon_' . session_id() ) ){
                        $coupon = HB_Coupon::instance( $coupon );
                        ?>
                        <tr>
                            <td colspan="3" class="hb-align-right" >
                                <?php printf( __( 'Coupon applied: %s', 'tp-hotel-booking' ), $coupon->coupon_code );?>
                                <p class="hb-remove-coupon" align="right">
                                    <a href="" id="hb-remove-coupon"><?php _e( 'Remove', 'tp-hotel-booking' );?></a>
                                </p>
                            </td>
                            <td class="hb-align-right">
                                -<?php echo hb_format_price( $coupon->discount_value );?>
                            </td>
                        </tr>
                    <?php }else{?>
                        <tr>
                            <td colspan="8" class="hb-align-right" >
                                <input type="text" name="hb-coupon-code" value="" placeholder="<?php _e( 'Coupon', 'tp-hotel-booking' );?>" style="width: 150px; vertical-align: top;" />
                                <button type="button" id="hb-apply-coupon"><?php _e( 'Apply Coupon', 'tp-hotel-booking' );?></button>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                <tr>
                    <td colspan="7"><?php _e( 'Sub Total', 'tp-hotel-booking' );?></td>
                    <td class="hb-align-right">
                        <?php echo hb_format_price( $cart->sub_total );?>
                    </td>
                </tr>
                <?php if( $tax = hb_get_tax_settings() ){?>
                <tr>
                    <td colspan="7">
                        <?php _e( 'Tax', 'tp-hotel-booking' );?>
                        <?php if( $tax < 0 ){?>
                            <span><?php printf( __( '(price including tax)', 'tp-hotel-booking' ) );?></span>
                        <?php }?>
                    </td>
                    <td class="hb-align-right"><?php echo abs( $tax * 100 );?>%</td>
                </tr>
                <?php }?>
                <tr>
                    <td colspan="7"><?php _e( 'Grand Total', 'tp-hotel-booking' ); ?></td>
                    <td class="hb-align-right"><?php echo hb_format_price( $cart->total );?></td>
                </tr>
                <?php if( $advance_payment = $cart->advance_payment ){?>
                <tr>
                    <td colspan="7">
                        <?php printf( __( 'Advance Payment (%s%% of Grand Total)', 'tp-hotel-booking' ), hb_get_advance_payment() );?>
                    </td>
                    <td class="hb-align-right"><?php echo hb_format_price( $advance_payment );?></td>
                </tr>
                    <?php if( hb_get_advance_payment() < 100 ){?>
                    <tr>
                        <td colspan="8" class="hb-align-right">
                            <label>
                                <input type="checkbox" name="pay_all" />
                                <?php _e( 'I want to pay all', 'tp-hotel-booking' );?>
                            </label>
                        </td>
                    </tr>
                    <?php }?>
                <?php }?>
        </table>
        <?php wp_nonce_field( 'hb_customer_place_order', 'hb_customer_place_order_field' );?>
        <!-- <input type="hidden" name="hotel-booking" value="checkout" /> -->
        <input type="hidden" name="action" value="hotel_booking_place_order" />
        <p>
            <a href="<?php echo hb_get_url(array( 'hotel-booking' => 'checkout')) ?>"><?php _e( 'Check out', 'tp-hotel-booking' );?></a>
        </p>
    </form>
</div>