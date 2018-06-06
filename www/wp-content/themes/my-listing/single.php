<?php get_header();

while(have_posts()): the_post();

	if (get_post_type() == 'job_listing'):

		get_job_manager_template_part( 'content-single', 'job_listing' );

	else: ?>

	<?php get_template_part('partials/content', 'single') ?>

	<?php endif ?>
<?php endwhile ?>

<?php get_footer() ?>