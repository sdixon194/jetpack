/**
 * Social Previews panel component.
 *
 * Shows available services and allows opening up the preview modal.
 */

import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useAvailableSerivces } from './useAvailableServices';
import './panel.scss';

const SocialPreviewsPanel = ( { openModal } ) => {
	const availableServices = useAvailableSerivces();

	return (
		<div className="jetpack-social-previews__panel">
			<p>
				{ __(
					'Preview what this will look like on social networks and Google search.',
					'jetpack'
				) }
			</p>

			<div className="jetpack-gutenberg-social-icons">
				{ availableServices.map( service => (
					<service.icon key={ service.name } className="jetpack-social-previews__icon" />
				) ) }
			</div>

			<Button
				variant="secondary"
				onClick={ openModal }
				label={ __( 'Open Social Previews', 'jetpack' ) }
			>
				{ __( 'Preview', 'jetpack' ) }
			</Button>
		</div>
	);
};

export default SocialPreviewsPanel;
