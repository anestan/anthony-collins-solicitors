<?php

namespace Tribe\Tickets\Plus\Meta;

/**
 * Service Provider for Fields
 *
 * Register classes in the container for Meta functionality and hooks them.
 *
 * @since 4.12.1
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Register classes in the container used by Meta functionalities.
	 *
	 * @since 4.12.1
	 */
	public function register() {
		$this->container->singleton( 'tickets-plus.meta', \Tribe__Tickets_Plus__Meta::class );
		$this->container->singleton( 'tickets-plus.meta.contents', \Tribe__Tickets_Plus__Meta__Contents::class );
		$this->container->singleton( 'tickets-plus.meta.storage', \Tribe__Tickets_Plus__Meta__Storage::class );

		$this->container->singleton( Field_Types_Collection::class, function () {
			return $this->get_field_types_collection();
		} );

		$this->hook();
	}

	/**
	 * Hooks and filters used by Meta functionalities.
	 *
	 * @since 4.12.1
	 */
	protected function hook() {
	}

	/**
	 * Build and populate our Field Types Collection.
	 *
	 * @return Field_Types_Collection
	 * @since 4.12.1
	 *
	 */
	private function get_field_types_collection() {
		$collection = new Field_Types_Collection;

		$collection->add( \Tribe__Tickets_Plus__Meta__Field__Text::class );
		$collection->add( \Tribe__Tickets_Plus__Meta__Field__Radio::class );
		$collection->add( \Tribe__Tickets_Plus__Meta__Field__Checkbox::class );
		$collection->add( \Tribe__Tickets_Plus__Meta__Field__Select::class );
		$collection->add( \Tribe__Tickets_Plus__Meta__Field__Email::class );
		$collection->add( \Tribe__Tickets_Plus__Meta__Field__Telephone::class );
		$collection->add( \Tribe__Tickets_Plus__Meta__Field__Url::class );
		$collection->add( \Tribe__Tickets_Plus__Meta__Field__Birth::class );
		$collection->add( \Tribe__Tickets_Plus__Meta__Field__Datetime::class );

		/**
		 * Allow adding additional supported field types.
		 *
		 * Use `$collection->add( Class_Name )` to add a new field type.
		 * The class must use the `Tribe__Tickets_Plus__Meta__Field__Abstract_Field` abstract class.
		 *
		 * @since 5.1.0
		 *
		 * @param Field_Types_Collection $collection The field type collection object.
		 */
		return apply_filters( 'tribe_tickets_plus_meta_field_types_collection', $collection );
	}

}
