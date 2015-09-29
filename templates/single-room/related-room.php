<?php
/**
 * Other room - Show related room for single pages.
 *
 * @author 		ThimPress
 * @package 	Tp-hotel-booking/Templates
 * @version     0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$room = HB_Room::instance( get_the_ID() );
$related = $room->get_related_rooms();
?>
<?php if( $related->posts ): ?>
	<div class="hb_related_other_room has_slider">
		<h2><?php _e( 'Other Rooms', 'tp-hotel-booking' ); ?></h2>
		<?php hotel_booking_room_loop_start(); ?>

			<?php while ( $related->have_posts() ) : $related->the_post(); ?>

				<?php hb_get_template_part( 'content', 'room' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php hotel_booking_room_loop_end(); ?>
		<div class="navigation">
            <div class="prev"><i class="fa fa-angle-left"></i></div>
            <div class="next"><i class="fa fa-angle-right"></i></div>
        </div>
	</div>

	<script type="text/javascript">
	    (function($){
	        "use strict";
	        $(document).ready(function(){
	            $('.hb_related_other_room ul.rooms').carouFredSel({
	                responsive: true,
	                items: {
	                    height: 'auto',
	                    visible: {
	                        min: 4,
	                        max: 4
	                    }
	                },
	                prev: {
	                    button: '.hb_related_other_room .navigation .prev'
	                },
	                next: {
	                    button: '.hb_related_other_room .navigation .next'
	                },
	                mousewheel: true,
	                pauseOnHover: true,
	                onCreate: function()
	                {

	                },
	                swipe: {
	                    onTouch: true,
	                    onMouse: true
	                },
	                scroll : {
	                    items           : 1,
	                    easing          : "swing",
	                    duration        : 700,
	                    pauseOnHover    : true
	                }
	            });
	        });
	    })(jQuery);
	</script>

<?php endif; ?>