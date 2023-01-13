/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';

const transforms = {
	from: [
		{
			type: 'block',
			blocks: ['core/navigation-link'],
			transform: () => {
				return createBlock('fibosearch/search-nav', {
					inheritPluginSettings: false,
					layout: 'icon',
				});
			},
		},
		{
			type: 'block',
			blocks: ['core/search'],
			transform: () => {
				return createBlock('fibosearch/search-nav', {
					inheritPluginSettings: false,
					layout: 'icon',
				});
			},
		},
	],
	to: [
		{
			type: 'block',
			blocks: ['core/search'],
			transform: () => {
				return createBlock('core/search', {
					showLabel: false,
					buttonUseIcon: true,
					buttonPosition: 'button-inside',
				});
			},
		},
		{
			type: 'block',
			blocks: ['core/navigation-link'],
			transform: () => {
				return createBlock('core/navigation-link');
			},
		},
	],
};

export default transforms;
