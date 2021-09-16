<?php
/**
 * Template Name: Contact Us
 */

get_header();
?>

<main id="primary" class="site-main">

    <?php
	while ( have_posts() ) :
		the_post();

		get_template_part( 'template-parts/content', 'page' );

	endwhile; // End of the loop.

	// Get the office locations
	$args = array(  
		'post_type' => 'offices',
		'post_status' => 'publish',
	);
	$loop = new WP_Query( $args );

	if ( $loop->have_posts() ):
	?>
    <div id="office-locations" class="container py-5 px-sm-0 mt-n4">

		<div id="speak-expert">
			<div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8">
				<div class="form-wrapper">
					<h3 class="mb-3">Speak to an expert</h3>

					<form action="" class="ask-expert">
						<select name="sector" id="find-sector">
							<option value="">Choose a sector</option>
							<option value="charities">Charities</option>
							<option value="education">Education</option>
							<option value="health-social-care">Health &amp; social care</option>
							<option value="housing">Housing</option>
							<option value="individuals">Individuals</option>
							<option value="local-government">Local government</option>
							<option value="social-business">Social business</option>
						</select>

						<input type="text" class="q" name="find-solicitor" placeholder="or type a solicitor name here">

						<button class="btn btn-default">Find</button>
					</form>
				</div>
			</div>
		</div>

        <div class="row mb-4">
            <div class="col-12">
                <h2>Offices</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <?php
			while ( $loop->have_posts() ):
				$loop->the_post();

				$office_id = get_the_ID();
			?>
                <div class="row-office mb-4 d-flex">
                    <div class="col-12 col-md-6 col-lg-8 pl-sm-5">
                        <div class="office-name mb-4">
                            <?php the_title(); ?>
                        </div>
                        <div class="office-information d-flex flex-row">
                            <div class="address">
                                <strong>Address</strong><br><br>
                                <?php echo get_field( 'address', $office_id ); ?>
                            </div>
                            <div class="contact-details">
                                <strong>Contact details</strong><br><br>
                                <?php if ( get_field( 'email', $office_id ) ): ?>
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/public/images/icon-mail-outline.svg"
                                    width="16" class="mr-2" /><?php echo get_field( 'email', $office_id ); ?><br>
                                <?php endif; ?>

                                <?php if ( get_field( 'phone', $office_id ) ): ?>
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/public/images/icon-phone.svg"
                                    width="16" class="mr-2" /><?php echo get_field( 'phone', $office_id ); ?><br>
                                <?php endif; ?>

                                <?php if ( get_field( 'fax', $office_id ) ): ?>
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/public/images/icon-fax.svg"
                                    width="16" class="mr-2" /><?php echo get_field( 'fax', $office_id ); ?>
                                <?php endif; ?>
                            </div>
                            <div class="opening-times">
                                <strong>Opening times</strong><br><br>
                                <?php
							$opening_times = get_field( 'opening_times', $office_id );
							if ( $opening_times ) {
								foreach ( $opening_times as $time ) {
									echo '<div class="d-flex flex-row">';
									echo '<div class="col-5 px-0">' . $time['days'] . '</div>';
									echo '<div class="col-7">' . $time['time'] . '</div>';
									echo '</div>';
								}
							}
							?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 px-0 position-relative">
                        <?php
					$location = get_field( 'map', $office_id );
					?>
                        <div class="acf-map" data-zoom="13">
                            <div class="marker" data-lat="<?php echo esc_attr($location['lat']); ?>"
                                data-lng="<?php echo esc_attr($location['lng']); ?>"></div>
                        </div>

                        <a href="#" class="get-directions">Get directions</a>
                    </div>
                </div>
                <?php endwhile; ?>

            </div>
        </div>

    </div>
    <?php endif; ?>

    <div id="contact-form" class="container-fluid px-0">
        <div class="container py-5">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="mb-4">Get in touch</h1>
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-8 col-lg-7 col-xl-6">
                            <?php echo apply_shortcodes( '[gravityform id="1" title="false"]' ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main><!-- #main -->

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBy5UazD5tDLu1zwuf7AW2AEv41FsOqThk"></script>
<script type="text/javascript">
(function($) {

    function initMap($el) {

        // Find marker elements within map.
        var $markers = $el.find('.marker');

        // Create gerenic map.
        // Style src: https://snazzymaps.com/style/287599/gmaps-adt
        var mapArgs = {
            zoom: $el.data('zoom') || 16,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            disableDefaultUI: true,
            styles: [{"featureType":"all","elementType":"geometry.fill","stylers":[{"visibility":"on"}]},{"featureType":"all","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#e9e0da"},{"visibility":"off"}]},{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"color":"#e9e0da"},{"visibility":"on"}]},{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"color":"#e9e0da"},{"visibility":"on"}]},{"featureType":"poi","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi.attraction","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.business","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.government","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"poi.medical","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"all","stylers":[{"color":"#b8cf78"},{"saturation":"19"},{"lightness":"-16"}]},{"featureType":"poi.park","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi.place_of_worship","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.school","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.sports_complex","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.sports_complex","elementType":"geometry","stylers":[{"color":"#c7c7c7"},{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"color":"#ffffff"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#ffffff"},{"visibility":"simplified"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"color":"#ffffff"},{"visibility":"off"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"visibility":"simplified"},{"color":"#ffffff"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"visibility":"simplified"}]},{"featureType":"road.local","elementType":"all","stylers":[{"color":"#ffffff"},{"visibility":"simplified"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#84bde9"}]}]
        };
        var map = new google.maps.Map($el[0], mapArgs);

        // Add markers.
        map.markers = [];
        $markers.each(function() {
            initMarker($(this), map);
        });

        // Center map based on markers.
        centerMap(map);

        // Return map instance.
        return map;
    }

    function initMarker($marker, map) {

        // Get position from marker.
        var lat = $marker.data('lat');
        var lng = $marker.data('lng');
        var latLng = {
            lat: parseFloat(lat),
            lng: parseFloat(lng)
        };

        // Create marker instance.
        var marker = new google.maps.Marker({
            position: latLng,
            map: map,
            icon: "<?php echo get_stylesheet_directory_uri(); ?>/public/images/map-pin.svg"
        });

        // Append to reference for later use.
        map.markers.push(marker);

        // If marker contains HTML, add it to an infoWindow.
        if ($marker.html()) {

            // Create info window.
            var infowindow = new google.maps.InfoWindow({
                content: $marker.html()
            });

            // Show info window when marker is clicked.
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.open(map, marker);
            });
        }
    }

    function centerMap(map) {

        // Create map boundaries from all map markers.
        var bounds = new google.maps.LatLngBounds();
        map.markers.forEach(function(marker) {
            bounds.extend({
                lat: marker.position.lat(),
                lng: marker.position.lng()
            });
        });

        // Case: Single marker.
        if (map.markers.length == 1) {
            map.setCenter(bounds.getCenter());

            // Case: Multiple markers.
        } else {
            map.fitBounds(bounds);
        }
    }

    // Render maps on page load.
    $(document).ready(function() {
        $('.acf-map').each(function() {
            var map = initMap($(this));
        });
    });

})(jQuery);
</script>
<?php
// get_sidebar();
get_footer();