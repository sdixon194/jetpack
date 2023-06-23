/**
 * External dependencies
 */
import { store as blockEditorStore } from '@wordpress/block-editor';
import { getBlockContent } from '@wordpress/blocks';
import { serialize } from '@wordpress/blocks';
import { select } from '@wordpress/data';
import { RichTextValue, create } from '@wordpress/rich-text';
import TurndownService from 'turndown';

// Turndown instance
const turndownService = new TurndownService();

/**
 * Returns partial content from the beginning of the post
 * to the current block, based on the given block clientId.
 *
 * @param {string} clientId - The current block clientId.
 * @returns {string}          The partial content.
 */
export function getPartialContentToBlock( clientId: string ): string {
	if ( ! clientId ) {
		return '';
	}

	const editor = select( 'core/block-editor' );
	const index = editor.getBlockIndex( clientId );
	const blocks = editor.getBlocks().slice( 0, index ) ?? [];

	if ( ! blocks?.length ) {
		return '';
	}

	return turndownService.turndown( serialize( blocks ) );
}

/**
 * Returns content from all blocks,
 * by inspecting the blocks `content` attributes
 *
 * @returns {string} The content.
 */
export function getContentFromBlocks(): string {
	const editor = select( 'core/block-editor' );
	const blocks = editor.getBlocks();

	if ( ! blocks?.length ) {
		return '';
	}

	return turndownService.turndown( serialize( blocks ) );
}

export type GetTextContentFromBlocksProps = {
	clientId: string;
	content: RichTextValue;
	offset: {
		start: number;
		end: number;
	};
};

/**
 * Returns the text content from all selected blocks.
 *
 * @returns {GetTextContentFromBlocksProps} The text content.
 */
export function getTextContentFromBlocks(): GetTextContentFromBlocksProps[] {
	const defaultContent = [];
	const clientIds = select( blockEditorStore ).getSelectedBlockClientIds();

	if ( ! clientIds?.length ) {
		return defaultContent;
	}

	const blocks = select( blockEditorStore ).getBlocksByClientId( clientIds );

	if ( ! blocks?.length ) {
		return defaultContent;
	}

	const startSelection = select( blockEditorStore ).getSelectionStart();
	const endSelection = select( blockEditorStore ).getSelectionEnd();

	const startSelectionBlockIndex = blocks.findIndex(
		block => block.clientId === startSelection.clientId
	);
	const endSelectionBlockIndex = blocks.findIndex(
		block => block.clientId === endSelection.clientId
	);

	let start = startSelection;
	let end = endSelection;

	// If the start selection is after the end selection, this was a selection made from bottom to top.
	// We swap them to make sure we have the right order.
	if ( startSelectionBlockIndex > endSelectionBlockIndex ) {
		start = endSelection;
		end = startSelection;
	}

	return blocks.map( block => {
		const blockTextContent = getBlockTextContent( block.clientId );
		const content = create( { html: blockTextContent } );

		// If they are the same, it means that there is no selection, but just the caret position.
		const hasSelection = start.offset !== end.offset;
		const useStart = hasSelection && start.clientId === block.clientId;
		const useEnd = hasSelection && end.clientId === block.clientId;

		const offset = {
			start: useStart ? start.offset : 0,
			end: useEnd ? end.offset : content.text.length,
		};

		return {
			clientId: block.clientId,
			content,
			offset,
		};
	} );
}

/**
 * Return the block content from the given block clientId.
 *
 * It will try to get the content from the block `content` attribute.
 * Otherwise, it will try to get the content
 * by using the `getBlockContent` function.
 *
 * @param {string} clientId   - The block clientId.
 * @returns {string}            The block content.
 */
export function getBlockTextContent( clientId: string ): string {
	if ( ! clientId ) {
		return '';
	}

	const editor = select( blockEditorStore );
	const block = editor.getBlock( clientId );

	/*
	 * In some context, the block can be undefined,
	 * for instance, when previewing the block.
	 */
	if ( ! block ) {
		return '';
	}

	// Attempt to pick the content from the block `content` attribute.
	if ( block?.attributes?.content ) {
		return block.attributes.content;
	}

	return getBlockContent( block );
}
