<?php if ( $packages || $user_packages ) :
	$checked = 1;
	?>
	<?php if ( $user_packages ) : ?>
		<div class="element">
			<div class="pf-head round-icon">
				<div class="title-style-1">
					<i class="material-icons">shopping_basket</i><h5><?php _e( 'Your Packages:', 'my-listing' ) ?></h5>
				</div>
			</div>
			<div class="pf-body">
				<ul class="job_packages">
					<?php foreach ( $user_packages as $key => $package ) :
						$package = wc_paid_listings_get_package( $package );
						?>
						<li class="user-job-package">
							<div class="md-checkbox">
								<input type="radio" <?php checked( $checked, 1 ); ?> name="job_package" value="user-<?php echo esc_attr( $key ); ?>" id="user-package-<?php echo esc_attr( $package->get_id() ); ?>" />
								<label for="user-package-<?php echo esc_attr( $package->get_id() ); ?>"></label>
							</div>
							<label for="user-package-<?php echo esc_attr( $package->get_id() ); ?>"><?php echo esc_attr( $package->get_title() ); ?></label><br/>
							<?php
							if ( $package->get_limit() ) {
								printf( _n( '%s listing posted out of %d', '%s listings posted out of %d', $package->get_count(), 'my-listing' ), $package->get_count(), $package->get_limit() );
							} else {
								printf( _n( '%s listing posted', '%s listings posted', $package->get_count(), 'my-listing' ), $package->get_count() );
							}

							if ( $package->get_duration() ) {
								printf(  ', ' . _n( 'listed for %s day', 'listed for %s days', $package->get_duration(), 'my-listing' ), $package->get_duration() );
							}

							$checked = 0;
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $packages ) : $i = 0; ?>
		<div class="row section-body">
			<?php foreach ( $packages as $key => $package ) : $i++;
				$product = wc_get_product( $package );

				if ( ! $product->is_type( array( 'job_package', 'job_package_subscription' ) ) || ! $product->is_purchasable() ) {
					continue;
				}

				$selected = isset($_GET['selected_package']) ?  absint($_GET['selected_package']) : null;

				$checked = $selected == $product->get_id();

				$duration = $package->package_duration ? $package->package_duration : false;
				// dump($product);
				?>

				<div class="col-md-4 col-sm-6 col-xs-12 reveal">
					<div class="pricing-item c27-pick-package <?php echo $checked ? 'c27-picked' : '' ?>">
						<h2 class="plan-name"><?php echo $product->get_title() ?></h2>
						<h2 class="plan-price case27-primary-text"><?php echo $product->get_price_html() ?></h2>
						<p class="plan-desc"><?php echo $product->get_short_description() ?></p>
						<div class="plan-features">
							<?php echo $product->get_description() ?>
						</div>
						<a class="select-plan buttons button-1" href="#">
							<i class="material-icons sm-icon">send</i><?php _e( 'Select Plan', 'my-listing' ); ?>
						</a>
						<input type="radio" <?php checked( $checked, 1 ); $checked = 0; ?> name="job_package" class="c27-job-package-radio-button" value="<?php echo esc_attr( $product->get_id() ); ?>" id="package-<?php echo esc_attr( $product->get_id() ); ?>" />
					</div>
				</div>

				<?php if ( $i % 3 == 0 ): ?>
					</div><div class="row">
				<?php endif ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
<?php else : ?>

	<p><?php _e( 'No packages found', 'my-listing' ); ?></p>

<?php endif; ?>

<style type="text/css">
    .elementor-widget:not(.elementor-widget-case27-add-listing-widget) { display: none !important; }
    .elementor-container { max-width: 100% !important; }
    .elementor-column-wrap { padding: 0 !important; }
</style>
