/**
 * External dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { Icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import Edit from './edit';
import transforms from './transforms';
import fibosearchIcon from '../../icons/fibosearch';

/**
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType('fibosearch/search', {
	edit: Edit,
	icon: {
		src: <Icon icon={fibosearchIcon} />,
	},
	save: () => {},
	transforms,
});
