<?php
/**
 * Jetpack CRM Automation Update_Contact action.
 *
 * @package automattic/jetpack-crm
 */

namespace Automattic\Jetpack\CRM\Automation\Actions;

use Automattic\Jetpack\CRM\Automation\Base_Action;

/**
 * Adds the Update_Contact class.
 */
class Update_Contact extends Base_Action {

	/**
	 * Get the slug name of the step
	 *
	 * @return string
	 */
	public static function get_slug(): string {
		return 'jpcrm/update_contact';
	}

	/**
	 * Get the title of the step
	 *
	 * @return string
	 */
	public static function get_title(): ?string {
		return 'Update Contact Action';
	}

	/**
	 * Get the description of the step
	 *
	 * @return string
	 */
	public static function get_description(): ?string {
		return 'Action to update the contact';
	}

	/**
	 * Get the type of the step
	 *
	 * @return string
	 */
	public static function get_type(): string {
		return 'contacts';
	}

	/**
	 * Get the category of the step
	 *
	 * @return string
	 */
	public static function get_category(): ?string {
		return 'actions';
	}

	/**
	 * Get the allowed triggers
	 *
	 * @return array
	 */
	public static function get_allowed_triggers(): ?array {
		return array();
	}

	/**
	 * Update the DAL with the new contact data.
	 *
	 * @param array $contact_data The contact data to be updated.
	 */
	public function execute( array $contact_data ) {
		global $zbs;

		$contact_data['data'] = array_replace( $contact_data['data'], $this->attributes['data'] );
		$zbs->DAL->contacts->addUpdateContact( $contact_data ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

}
