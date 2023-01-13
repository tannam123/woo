<?php

/**
 * Filter to add plugins to the Rank Math SEO TOC list.
 *
 * @param array TOC plugins.
 */
add_filter( 'rank_math/researches/toc_plugins', function ( $toc_plugins ) {
	$toc_plugins['fixed-toc/fixed-toc.php'] = 'Fixed TOC';

	return $toc_plugins;
} );

/**
 * Applies Rank Math link attributes filter before creating headings data.
 *
 * FTOC Can't recognize heading tags if there are links within headings, because links are inserted extra attributes.
 *
 * @since 3.1.19
 */
add_action( 'fixedtoc_before_creating_data', function () {
	/** @noinspection PhpUndefinedClassInspection */
	/** @noinspection PhpUndefinedNamespaceInspection */
	$rank_math_link_attrs = new RankMath\Frontend\Link_Attributes();
	/** @noinspection PhpUndefinedMethodInspection */
	$rank_math_link_attrs->add_attributes();
} );