<?php
/**
 * Tickets: Commerce Fields.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/tickets/commerce/fields.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1amp Help article for RSVP & Ticket template files.
 *
 * @since   5.1.0
 *
 * @version 5.1.0
 *
 * @var Tribe__Tickets_Plus__Template      $this                Template object.
 * @var Tribe__Tickets__Ticket_Object[]    $tickets             List of tickets.
 * @var Tribe__Tickets__Ticket_Object[]    $tickets_on_sale     List of tickets on sale.
 * @var bool                               $has_tickets_on_sale Whether there are tickets on sale.
 * @var Tribe__Tickets__Commerce__Currency $currency            The commerce currency instance.
 * @var bool                               $is_mini             True if it's in mini cart context.
 * @var bool                               $is_modal            True if it's in modal context.
 * @var Tribe__Tickets__Tickets            $provider            The tickets provider class.
 * @var string                             $provider_id         The tickets provider class name.
 * @var string                             $cart_url            The cart URL.
 */

$this->template( 'v2/tickets/commerce/fields/' . $provider->orm_provider );
?>
<input name="provider" value="<?php echo esc_attr( $provider->class_name ); ?>" class="tribe-tickets-provider" type="hidden">
