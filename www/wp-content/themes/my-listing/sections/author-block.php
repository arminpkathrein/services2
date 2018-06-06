<?php
	$data = c27()->merge_options([
			'icon' => '',
			'icon_style' => 1,
			'title' => '',
			'wrapper_class' => 'grid-item reveal',
			'author_id' => '',
			'ref' => '',
		], $data);

	if ( ! absint( $data['author_id'] ) || ! ( $userdata = get_userdata( absint( $data['author_id'] ) ) ) ) {
		return false;
	}

	$user_url = function_exists( 'bp_core_get_user_domain' ) ? bp_core_get_user_domain( $userdata->ID ) : get_author_posts_url( $userdata->ID );

?>

<div class="<?php echo esc_attr( $data['wrapper_class'] ) ?>">
	<div class="element related-listing-block">
		<div class="pf-head">
			<div class="title-style-1 title-style-<?php echo esc_attr( $data['icon_style'] ) ?>">
				<?php if ($data['icon_style'] != 3): ?>
					<?php echo c27()->get_icon_markup($data['icon']) ?>
				<?php endif ?>
				<h5><?php echo esc_html( $data['title'] ) ?></h5>
			</div>
		</div>
		<div class="pf-body">
			<div class="event-host">
				<a href="<?php echo esc_url( $user_url ) ?>">
					<div class="avatar">
						<img src="<?php echo esc_url( get_avatar_url( $userdata->ID ) ) ?>">
					</div>
					<span class="host-name"><?php echo esc_html( $userdata->display_name ) ?></span>
				</a>
			</div>
		</div>
	</div>
</div>