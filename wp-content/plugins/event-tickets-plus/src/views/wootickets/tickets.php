<?php
/**
 * Renders the WooCommerce tickets table/form
 *
 * Override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe/tickets-plus/wootickets/tickets.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @deprecated 4.11.0
 *
 * @since 4.10.7 Restrict quantity selectors to allowed purchase limit and removed unused variables.
 * @since 5.1.0 Updated template link.
 *
 * @version 5.1.0
 *
 * @var bool $global_stock_enabled
 * @var bool $must_login
 */
$post_id = get_the_id();
Tribe__Tickets__Tickets_View::instance()->get_tickets_block( $post_id );
