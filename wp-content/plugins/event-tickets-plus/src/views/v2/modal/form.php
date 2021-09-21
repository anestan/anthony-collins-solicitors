<?php
/**
 * The form used for the modal.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets-plus/v2/modal/form.php
 *
 * @link    https://evnt.is/1amp See more documentation about our views templating system.
 *
 * @since   5.1.0
 *
 * @version 5.1.0
 *
 * @var array                              $tickets             List of tickets.
 * @var Tribe__Tickets__Ticket_Object[]    $tickets_on_sale     List of tickets on sale.
 * @var bool                               $has_tickets_on_sale Whether there are tickets on sale.
 * @var Tribe__Tickets__Commerce__Currency $currency            The commerce currency instance.
 * @var bool                               $is_mini             True if it's in mini cart context.
 * @var bool                               $is_modal            True if it's in modal context.
 * @var Tribe__Tickets__Tickets            $provider            The tickets provider class.
 * @var int                                $post_id             The post/event ID.
 */
?>

<form
	id="tribe-tickets__modal-form"
	class="tribe-tickets__form"
	action=""
	method="post"
	enctype='multipart/form-data'
	data-provider="<?php echo esc_attr( $provider->class_name ); ?>"
	autocomplete="off"
	data-provider-id="<?php echo esc_attr( $provider->orm_provider ); ?>"
	data-post-id="<?php echo esc_attr( $post_id ); ?>"
	novalidate
>
	<?php
	$this->template( 'v2/modal/cart' );

	$this->template( 'v2/modal/attendee-registration' );
	?>
</form>
