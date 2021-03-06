<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

$cart = WP_Hotel_Booking::instance()->cart;
global $hb_settings;

do_action( 'hotel_booking_before_checkout_form' );

?>
		<h3 class="unlink"> <a href="/e-cycle-search/" style="color: #0F74BD;">Choose Dates</a> > <a href="/e-cycle-search/" style="color: #0F74BD;">Choose Bike(s)</a> > <strong>Customer Details</strong></h3>
    <div id="hotel-booking-payment">

        <form name="hb-payment-form" id="hb-payment-form" method="post" action="<?php echo isset( $search_page ) ? $search_page : ''; ?>">
						<h3 style="float:left"><?php _e( 'Booking E-Cycles', 'wp-hotel-booking' ); ?></h3>
						<a href="<?php echo hb_get_cart_url() ?>"
							 class="hb_button hb_checkout pull-right"><?php _e( 'Modify Cart', 'wp-hotel-booking' ); ?>
						 </a>
            <table class="hb_table">
                <thead>
                <th class="hb_room_type"><?php _e( 'Cycle type', 'wp-hotel-booking' ); ?></th>
                <th class="hb_capacity" style="display: none;"><?php _e( 'Capacity', 'wp-hotel-booking' ); ?></th>
                <th class="hb_quantity"><?php _e( 'Quantity', 'wp-hotel-booking' ); ?></th>
                <th class="hb_check_in" style="display: none;"><?php _e( 'From', 'wp-hotel-booking' ); ?></th>
                <th class="hb_check_out" style="display: none;"><?php _e( 'To', 'wp-hotel-booking' ); ?></th>
                <th class="hb_night" style="display: none;"><?php _e( 'Night', 'wp-hotel-booking' ); ?></th>
                <th class="hb_gross_total"><?php _e( 'Gross Total', 'wp-hotel-booking' ); ?></th>
                </thead>
				<?php if ( $rooms = $cart->get_rooms() ): ?>
					<?php foreach ( $rooms as $cart_id => $room ): ?>
						<?php
						if ( ( $num_of_rooms = (int) $room->get_data( 'quantity' ) ) == 0 ) continue;
						$cart_extra = WP_Hotel_Booking::instance()->cart->get_extra_packages( $cart_id );
						$sub_total  = $room->get_total( $room->check_in_date, $room->check_out_date, $num_of_rooms, false );
						?>
                        <tr class="hb_checkout_item" data-cart-id="<?php echo esc_attr( $cart_id ); ?>">
                            <td class="hb_room_type"<?php echo defined( 'TP_HB_EXTRA' ) && $cart_extra ? ' rowspan="' . ( count( $cart_extra ) + 2 ) . '"' : '' ?>>
                                <a href="<?php echo esc_url( get_permalink( $room->ID ) ); ?>"><?php echo esc_html( $room->name ); ?><?php // printf( '%s', $room->capacity_title ? ' (' . $room->capacity_title . ')' : '' ); ?></a>
                            </td>
                            <td class="hb_capacity" style="display: none;"><?php echo sprintf( _n( '%d adult', '%d adults', $room->capacity, 'wp-hotel-booking' ), $room->capacity ); ?> </td>
                            <td class="hb_quantity"><?php printf( '%s', $num_of_rooms ); ?><?php echo " cycles " . hb_format_price( $room->amount_singular ) . "/Day <br/>" . ltrim(hb_count_nights_two_dates($room->get_data('check_in_date'), $room->get_data('check_out_date')), "-") . " Days"; ?></td>
                            <td class="hb_check_in" style="display: none;"><?php echo date_i18n( hb_get_date_format(), strtotime( $room->get_data( 'check_in_date' ) ) ) ?></td>
                            <td class="hb_check_out" style="display: none;"><?php echo date_i18n( hb_get_date_format(), strtotime( $room->get_data( 'check_out_date' ) ) ) ?></td>
                            <td class="hb_night" style="display: none;"><?php echo hb_count_nights_two_dates( $room->get_data( 'check_out_date' ), $room->get_data( 'check_in_date' ) ) ?></td>
                            <td class="hb_gross_total">
								<?php echo hb_format_price( $room->total ); ?>
                            </td>
                        </tr>
						<?php do_action( 'hotel_booking_cart_after_item', $room, $cart_id ); ?>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php do_action( 'hotel_booking_before_cart_total' ); ?>

                <tr class="hb_sub_total">
                    <td colspan="7"><?php _e( 'Sub Total', 'wp-hotel-booking' ); ?>
                        <span class="hb-align-right hb_sub_total_value">
                        <?php echo hb_format_price( $cart->sub_total ); ?>
                    </span>
                    </td>
                </tr>

				<?php if ( $tax = hb_get_tax_settings() ) { ?>
                    <tr class="hb_advance_tax" style="display: none;">
                        <td colspan="7">
							<?php _e( 'Tax', 'wp-hotel-booking' ); ?>
							<?php if ( $tax < 0 ) { ?>
                                <span><?php printf( __( '(price including tax)', 'wp-hotel-booking' ) ); ?></span>
							<?php } ?>
                            <span class="hb-align-right"><?php echo apply_filters( 'hotel_booking_cart_tax_display', hb_format_price( $cart->total - $cart->sub_total ) ); // abs( $tax * 100 ) . '%' ?></span>
                        </td>
                    </tr>
				<?php } ?>

                <tr class="hb_advance_grand_total" style="display: none;">
                    <td colspan="7">
						<?php _e( 'Grand Total', 'wp-hotel-booking' ); ?>
                        <span class="hb-align-right hb_grand_total_value"><?php echo hb_format_price( $cart->total ); ?></span>
                    </td>
                </tr>
				<?php $advance_payment = ''; ?>
				<?php if ( $advance_payment = $cart->advance_payment ) { ?>
                    <tr class="hb_advance_payment" style="display: none;">
                        <td colspan="7">
							<?php printf( __( 'Advance Payment (%s%% of Grand Total)', 'wp-hotel-booking' ), hb_get_advance_payment() ); ?>
                            <span class="hb-align-right hb_advance_payment_value"><?php echo hb_format_price( $advance_payment ); ?></span>
                        </td>
                    </tr>
					<?php if ( hb_get_advance_payment() < 100 ) { ?>
                        <tr class="hb_payment_all" style="display: none;">
                            <td colspan="7" class="hb-align-right">
                                <label class="hb-align-right">
                                    <input type="checkbox" name="pay_all" />
									<?php _e( 'I want to pay all', 'wp-hotel-booking' ); ?>
                                </label>
                            </td>
                        </tr>
					<?php } ?>
				<?php } ?>

            </table>

						<div class="row">
							<div class="col-md-12">
								<h5>Special discounts, use these coupons: 15+days, 30+days, 45+days <br/> Volunteer contact for special price.</h5>
							</div>

						</div>

			<?php if ( !is_user_logged_in() && !hb_settings()->get( 'guest_checkout' ) && get_option( 'users_can_register' ) ) : ?>

				<?php printf( __( 'You have to <strong><a href="%s">login</a></strong> or <strong><a href="%s">register</a></strong> to checkout.', 'wp-hotel-booking' ), wp_login_url( hb_get_checkout_url() ), wp_registration_url() ) ?>

			<?php else : ?>

				<?php hb_get_template( 'checkout/customer.php', array( 'customer' => $customer ) ); ?>
				<?php hb_get_template( 'checkout/payment-method.php', array( 'customer' => $customer ) ); ?>
				<?php hb_get_template( 'checkout/addition-information.php' ); ?>
				<?php wp_nonce_field( 'hb_customer_place_order', 'hb_customer_place_order_field' ); ?>

                <input type="hidden" name="hotel-booking" value="place_order" />
                <input type="hidden" name="action" value="hotel_booking_place_order" />
                <input type="hidden" name="total_advance" value="<?php echo esc_attr( $cart->advance_payment ? $cart->advance_payment : $cart->total ); ?>" />
                <input type="hidden" name="total_price" value="<?php echo esc_attr( $cart->total ); ?>" />
                <input type="hidden" name="currency" value="<?php echo esc_attr( hb_get_currency() ) ?>">
				<?php if ( $tos_page_id = hb_get_page_id( 'terms' ) ) { ?>
                    <p style="visibility:hidden;">
                        <label>
                            <input type="checkbox" name="tos" value="1" checked='checked'/>
							<?php printf( __( 'I agree with ', 'wp-hotel-booking' ) . '<a href="%s" target="_blank">%s</a>', get_permalink( $tos_page_id ), get_the_title( $tos_page_id ) ); ?>
                        </label>
                    </p>
				<?php } ?>
                <p>
                    <button type="submit" class="hb_button"><?php _e( 'Place Reservation', 'wp-hotel-booking' ); ?></button>
                </p>

			<?php endif; ?>
        </form>
    </div>

<?php do_action( 'hotel_booking_after_checkout_form' ); ?>
