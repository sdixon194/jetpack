<?php
/**
 * Jetpack CRM Automation Quote_Updated trigger.
 *
 * @package automattic/jetpack-crm
 */

namespace Automattic\Jetpack\CRM\Automation\Triggers;

use Automattic\Jetpack\CRM\Automation\Base_Trigger;

/**
 * Adds the Quote_Updated class.
 */
class Quote_Updated extends Base_Trigger {

	/**
	 * Get the slug name of the trigger
	 * @return string
	 */
	public static function get_slug(): string {
		return 'jpcrm/quote_updated';
	}

	/**
	 * Get the title of the trigger.
	 * @return string
	 */
	public static function get_title(): ?string {
		return __( 'Quote Updated', 'zero-bs-crm' );
	}

	/**
	 * Get the description of the trigger.
	 * @return string
	 */
	public static function get_description(): ?string {
		return __( 'Triggered when a quote is updated', 'zero-bs-crm' );
	}

	/**
	 * Get the category of the trigger.
	 * @return string
	 */
	public static function get_category(): ?string {
		return __( 'quote', 'zero-bs-crm' );
	}

	/**
	 * Listen to this trigger's target event.
	 */
	protected function listen_to_event() {
		add_action(
			'jpcrm_quote_update',
			array( $this, 'execute_workflow' )
		);
	}
}
