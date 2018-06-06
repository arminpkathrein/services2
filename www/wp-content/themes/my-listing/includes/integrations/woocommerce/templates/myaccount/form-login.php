<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<section class="i-section no-modal">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<?php wc_print_notices(); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<?php c27()->get_partial('account/login-form') ?>
			</div>
			<div class="col-md-6">
				<?php c27()->get_partial('account/register-form') ?>
			</div>
		</div>
	</div>
</section>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
