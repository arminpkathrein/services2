<?php

$classes = [
	'fallback.php',
	'helpers.php',

	// Classes.
	'classes/traits/instantiatable.php',
	'classes/dashboard-pages.php',
	'classes/term.php',
	'classes/work-hours.php',
	'classes/listing.php',
	'classes/explore.php',

	// General.
	'init.php',
	'filters.php',
	'assets.php',
	'ajax.php',
	'admin.php',
	'strings.php',
	'plugins/activator.php',
	'shortcodes/shortcodes.php',
	'walkers/listing-reviews.php',

	// Integrations.
	'integrations/advanced-custom-fields/acf.php',
	'integrations/wp-job-manager/wp-job-manager.php',
	'integrations/woocommerce/woocommerce.php',
	'integrations/buddypress/buddypress.php',
	'integrations/wp-job-manager-wc-paid-listings/wp-job-manager-wc-paid-listings.php',
	'integrations/elementor/elementor.php',
	'integrations/contact-form-7/contact-form-7.php',
	'integrations/27collective/share/share.php',
	'integrations/27collective/bookmarks/bookmark.php',
	'integrations/27collective/reviews/review.php',
	'integrations/27collective/breadcrumbs/breadcrumbs.php',
	'integrations/27collective/search/search.php',
	'integrations/27collective/promotions/promotions.php',
	'integrations/27collective/dashboard-pages/pages.php',
	'integrations/27collective/listing-types/designer.php',
	'integrations/27collective/listing-types/listing-type.php',
	'integrations/27collective/reports/reports.php',
	'integrations/27collective/product-vendors/vendors.php',
];

foreach ($classes as $classPath) {
	require_once trailingslashit( CASE27_THEME_DIR ) . 'includes/' . $classPath;
}