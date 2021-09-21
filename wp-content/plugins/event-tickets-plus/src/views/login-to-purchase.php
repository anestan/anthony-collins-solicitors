<?php
/**
 * Renders a link displayed to customers when they must first login
 * before being able to purchase tickets.
 *
 * Override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe/tickets-plus/login-to-purchase.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since 4.7
 * @since 5.1.0 Updated template link.
 *
 * @version 5.1.0
 */

$login_url = Tribe__Tickets__Tickets::get_login_url();
?>

<a href="<?php echo esc_attr( $login_url ); ?>"><?php esc_html_e( 'Log in to purchase', 'event-tickets-plus' ); ?></a>
