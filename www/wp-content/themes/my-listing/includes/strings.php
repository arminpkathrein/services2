<?php

namespace CASE27;

class Strings {

	public $strings, $text_domains, $theme;

	public function __construct() {
		add_action( 'after_setup_theme', function() {
			$this->theme = get_translations_for_domain( 'my-listing' );
			$this->strings = $this->get_strings();
			$this->text_domains = array_keys( $this->strings );

			add_filter( 'gettext', function( $translated, $text, $domain ) {
				if ( in_array( $domain, $this->text_domains ) && isset( $this->strings[$domain][$text] ) ) {
					return $this->translate( $this->strings[$domain][$text] );
				}

				return $translated;
			}, 0, 15 );

			add_filter( 'gettext_with_context', function( $translated, $text, $context, $domain ) {
				if ( in_array( $domain, $this->text_domains ) && isset( $this->strings[$domain][$text] ) ) {
					return $this->translate( $this->strings[$domain][$text] );
				}

				return $translated;
			}, 0, 20 );
		});
	}

	private function translate( $text ) {
		if ( is_array( $text ) ) {
			return vsprintf( $this->theme->translate[ $text[0] ], $text[1] );
		}

		return $this->theme->translate( $text ) ? : $text;
	}

	private function get_strings()
	{
		return [
			'wp-job-manager' => [
				'Job' => __('Listing', 'my-listing'),
				'Jobs' => __('Listings', 'my-listing'),
				'job' => __( 'listing', 'my-listing' ),
				'jobs' => __( 'listings', 'my-listing' ),
				'Job Listings' => __('Listings', 'my-listing'),
				'Job category' => __( 'Category', 'my-listing' ),
				'Job categories' => __( 'Categories', 'my-listing' ),
				'Job Categories' => __( 'Categories', 'my-listing' ),
				'job-category' => __( 'category', 'my-listing' ),
				'Job type' => __( 'Type', 'my-listing' ),
				'Job types' => __( 'Types', 'my-listing' ),
				'Job Types' => __( 'Types', 'my-listing' ),
				'Job base' => __( 'Listing base', 'my-listing' ),
				'Job category base' => __( 'Listing category base', 'my-listing' ),
				'Job type base' => __( 'Listing type base', 'my-listing' ),
				'job-type' => __( 'listing-type', 'my-listing' ),
				'Jobs will be shown if within ANY selected category' => __( 'Listings will be shown if within ANY selected category', 'my-listing' ),
				'Jobs will be shown if within ALL selected categories' => __( 'Listings will be shown if within ALL selected categories', 'my-listing' ),
				'Position filled?' => __( 'Listing filled?', 'my-listing' ),
				'A video about your company' => __( 'A video about your listing', 'my-listing' ),
				'Job Submission' => __( 'Listing Submission', 'my-listing' ),
				'Submit Job Form Page' => __( 'Submit Listing Form Page', 'my-listing' ),
				'Job Dashboard Page' => __( 'Listing Dashboard Page', 'my-listing' ),
				'Job Listings Page' => __( 'Listings Page', 'my-listing' ),
				'Add a job via the back-end' => __( 'Add a listing via the back-end', 'my-listing' ),
				'Add a job via the front-end' => __( 'Add a listing via the front-end', 'my-listing' ),
				'Find out more about the front-end job submission form' => __( 'Find out more about the front-end listing submission form', 'my-listing' ),
				'View submitted job listings' => __( 'View submitted listings', 'my-listing' ),
				'Add the [jobs] shortcode to a page to list jobs' => __( 'Add the [jobs] shortcode to a page to list listings', 'my-listing' ),
				'View the job dashboard' => __( 'View the listing dashboard', 'my-listing' ),
				'Find out more about the front-end job dashboard' => __( 'Find out more about the front-end listing dashboard', 'my-listing' ),
				'Job Title' => __( 'Listing Name', 'my-listing' ),
				'Apply for job' => __( 'Apply', 'my-listing' ),
				'To apply for this job please visit the following URL: <a href="%1$s" target="_blank">%1$s &rarr;</a>' => __( 'To apply please visit the following URL: <a href="%1$s" target="_blank">%1$s &rarr;</a>', 'my-listing' ),
				'To apply for this job <strong>email your details to</strong> <a class="job_application_email" href="mailto:%1$s%2$s">%1$s</a>' => __( 'To apply <strong>email your details to</strong> <a class="job_application_email" href="mailto:%1$s%2$s">%1$s</a>', 'my-listing' ),
				'Anywhere' => __( '&mdash;', 'my-listing' ),
			],

			'wp-job-manager-wc-paid-listings' => [
				'Choose a package before entering job details' => __( 'Choose a package before entering listing details', 'my-listing' ),
				'Choose a package after entering job details' => __( 'Choose a package after entering listing details', 'my-listing' ),
				'Job Package' => __( 'Listing Package', 'my-listing' ),
				'Job Package Subscription' => __( 'Listing Package Subscription', 'my-listing' ),
				'Job Listing' => __( 'Listing', 'my-listing' ),
				'Job listing limit' => __( 'Listing limit', 'my-listing' ),
				'Job listing duration' => __( 'Listing duration', 'my-listing' ),
				'The number of days that the job listing will be active.' => __( 'The number of days that the listing will be active', 'my-listing' ),
				'Feature job listings?' => __( 'Feature listings?', 'my-listing' ),
				'Feature this job listing - it will be styled differently and sticky.' => __( 'Feature this listing - it will be styled differently and sticky.', 'my-listing' ),
				'My Job Packages' => __( 'My Listing Packages', 'my-listing' ),
				'Jobs Remaining' => __( 'Listings Remaining', 'my-listing' ),
			],

			'jwapl' => [
				'Job Package' => __( 'Listing Package', 'my-listing' ),
				'Job Package Subscription' => __( 'Listing Package Subscription', 'my-listing' ),
			],
		];
	}
}

new Strings;
