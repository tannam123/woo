/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';
import { regexp } from '@wordpress/shortcode';

const transforms = {
	from: [
		{
			type: 'block',
			blocks: ['core/search'],
			transform: () => {
				return createBlock('fibosearch/search');
			},
		},
		{
			type: 'block',
			blocks: ['core/legacy-widget'],
			isMatch: ({ idBase, instance }) => {
				if (!instance?.raw) {
					// Can't transform if raw instance is not shown in REST API.
					return false;
				}
				return idBase === 'dgwt_wcas_ajax_search';
			},
			transform: ({ instance }) => {
				const layout = instance.raw.layout;
				const attributes = {};
				if (layout !== 'default') {
					attributes.inheritPluginSettings = false;
					attributes.layout = layout;
				}
				return createBlock('fibosearch/search', attributes);
			},
		},
		{
			type: 'block',
			blocks: ['core/shortcode'],
			transform: () => {
				return createBlock('fibosearch/search');
			},
			isMatch: ({ text }) => {
				const re = regexp('fibosearch');
				const match = re.exec(text);
				return Array.isArray(match) && match[2] === 'fibosearch';
			},
		},
		{
			type: 'block',
			blocks: ['core/shortcode'],
			transform: () => {
				return createBlock('fibosearch/search');
			},
			isMatch: ({ text }) => {
				const re = regexp('wcas-search-form');
				const match = re.exec(text);
				return Array.isArray(match) && match[2] === 'wcas-search-form';
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
	],
};

export default transforms;
