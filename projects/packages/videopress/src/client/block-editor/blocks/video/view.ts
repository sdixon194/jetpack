/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Preview on Hover effect for VideoPress videos.
 *
 * @returns {void}
 */
function previewOnHoverEffect(): void {
	/*
	 * Pick all VideoPress video block intances,
	 * based on the class name.
	 */
	const videoPlayers = document.querySelectorAll( '.wp-block-jetpack-videopress' );
	if ( videoPlayers.length === 0 ) {
		return;
	}

	videoPlayers.forEach( function ( videoPlayerElement: HTMLDivElement ) {
		// Get the iFrame element.
		const iFrame = videoPlayerElement.querySelector( 'iframe' );
		if ( ! iFrame ) {
			return;
		}

		// If the VideoPress iFrame API is not available, return.
		if ( ! window?.VideoPressIframeApi ) {
			return;
		}

		/*
		 * Try to pick the POH data from the data container element.
		 * If it fails, log the error and return.
		 */
		const dataContainer = videoPlayerElement.querySelector( 'script[type="application/json"]' );
		if ( ! dataContainer ) {
			return;
		}

		let previewOnHoverData: {
			previewAtTime: number;
			previewLoopDuration: number;
			autoplay: boolean;
			showControls: boolean;
		};

		try {
			previewOnHoverData = JSON.parse( dataContainer.innerHTML );
		} catch ( error ) {
			console.error( error ); // eslint-disable-line no-console
			return;
		}

		// Clean the data container element. It isn't needed anymore.
		dataContainer.remove();

		let userHasInteracted = false;

		const iframeApi = window.VideoPressIframeApi( iFrame, () => {
			iframeApi.status.onPlayerStatusChanged( ( oldStatus, newStatus ) => {
				if ( oldStatus === 'ready' && newStatus === 'playing' ) {
					iframeApi.controls.pause();
					iframeApi.controls.seek( previewOnHoverData.previewAtTime );
				}
			} );

			iframeApi.status.onTimeUpdate( playbackTime => {
				if ( userHasInteracted ) {
					return;
				}

				const playback = playbackTime * 1000;
				const start = previewOnHoverData.previewAtTime;
				const end = start + previewOnHoverData.previewLoopDuration;

				if ( playback < start || playback > end ) {
					iframeApi.controls.seek( start );
				}
			} );
		} );

		const overlay = videoPlayerElement.querySelector( '.jetpack-videopress-player__overlay' );
		if ( ! overlay ) {
			return;
		}

		const playButton = videoPlayerElement.querySelector(
			'.jetpack-videopress-player__play-button'
		);

		/*
		 * Disable PreviewOnHover (pOH) when the player
		 * should show the controls and
		 * once the user clicks on the video (overlay).
		 */
		if ( previewOnHoverData.showControls ) {
			overlay.addEventListener( 'click', () => {
				// Set the userHasInteracted flag to true.
				userHasInteracted = true;

				// Delete overlay and play button elements.
				overlay.remove();
				playButton?.remove();

				// Playback the video from the beginning.
				iframeApi.controls.seek( 0 );
			} );
		}

		overlay.addEventListener( 'mouseenter', () => {
			// Adding a delay to do not overlap the client fade-in animation.
			setTimeout( () => playButton?.classList.add( 'is-behind' ), 500 );
			iframeApi.controls.play();
		} );

		overlay.addEventListener( 'mouseleave', () => {
			playButton?.classList.remove( 'is-behind' );
			iframeApi.controls.pause();
		} );
	} );
}

domReady( function () {
	previewOnHoverEffect();
} );
