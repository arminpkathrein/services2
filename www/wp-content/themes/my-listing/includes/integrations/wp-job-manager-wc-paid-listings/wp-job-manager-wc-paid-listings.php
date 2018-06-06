<?php

class CASE27_WC_Paid_Listings_Integration {

	public function __construct()
	{
		add_filter('submit_job_steps', [$this, 'submit_job_steps'], 30);

		if (class_exists('WC_Paid_Listings_Orders')) {
			remove_action('woocommerce_before_my_account', [WC_Paid_Listings_Orders::get_instance(), 'my_packages']);
			add_action('case27_woocommerce_after_template_part_myaccount/dashboard.php', [WC_Paid_Listings_Orders::get_instance(), 'my_packages'], 30);
		}
	}


	public function submit_job_steps($steps)
	{
		if (isset($steps['wc-choose-package'])) {
			$steps['wc-choose-package']['view'] = [$this, 'choose_package_view'];
		}

		return $steps;
	}

	public static function filter_wcpl_get_job_packages_args($args) {
			unset($args['meta_query']);

			return $args;
	}

	public static function get_packages($packages = [])
	{
		if (!class_exists('WP_Job_Manager_WCPL_Submit_Job_Form')) return [];

		add_filter('wcpl_get_job_packages_args', [__CLASS__, 'filter_wcpl_get_job_packages_args']);

		$packages = WP_Job_Manager_WCPL_Submit_Job_Form::get_packages( $packages );

		remove_filter('wcpl_get_job_packages_args', [__CLASS__, 'filter_wcpl_get_job_packages_args']);

		return $packages;
	}


	public function choose_package_view($atts = [])
	{
		$form      = WP_Job_Manager_Form_Submit_Job::instance();
		$job_id    = $form->get_job_id();
		$step      = $form->get_step();
		$form_name = $form->form_name;
		$packages      = self::get_packages( isset( $atts['packages'] ) ? explode( ',', $atts['packages'] ) : array() );
		$user_packages = wc_paid_listings_get_user_packages( get_current_user_id(), 'job_listing' );
		$button_text   = 'before' !== get_option( 'job_manager_paid_listings_flow' ) ? __( 'Submit &rarr;', 'my-listing' ) : __( 'Listing Details &rarr;', 'my-listing' );
		?>
		<section class="i-section">
			<div class="container">
				<div class="row section-title reveal reveal_visible">
					<p><?php _e( 'Pricing', 'my-listing' ) ?></p>
					<h2 class="case27-primary-text"><?php _e( 'Choose a Package', 'my-listing' ) ?></h2>
				</div>
				<form method="post" id="job_package_selection">
					<div class="job_listing_packages">
						<?php get_job_manager_template( 'package-selection.php', array( 'packages' => $packages, 'user_packages' => $user_packages ), 'wc-paid-listings', JOB_MANAGER_WCPL_PLUGIN_DIR . '/templates/' ); ?>

						<div class="row section-body">
							<br>
							<input type="submit" name="continue" class="button buttons button-2" style="width: auto; float: right;" value="<?php echo apply_filters( 'submit_job_step_choose_package_submit_text', $button_text ); ?>" />
							<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
							<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
							<input type="hidden" name="job_manager_form" value="<?php echo esc_attr( $form_name ); ?>" />
						</div>
					</div>
				</form>
			</div>
		</section>
		<?php
	}

}

new CASE27_WC_Paid_Listings_Integration;