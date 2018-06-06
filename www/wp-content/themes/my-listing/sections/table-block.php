<?php
	$data = c27()->merge_options([
			'icon' => '',
			'icon_style' => 1,
			'title' => '',
			'rows' => [],
			'wrapper_class' => 'block-element',
		], $data);
?>

<div class="<?php echo esc_attr( $data['wrapper_class'] ) ?>">
	<div class="element table-block">
		<div class="pf-head">
			<div class="title-style-1 title-style-<?php echo esc_attr( $data['icon_style'] ) ?>">
				<?php if ($data['icon_style'] != 3): ?>
					<?php echo c27()->get_icon_markup($data['icon']) ?>
				<?php endif ?>
				<h5><?php echo esc_html( $data['title'] ) ?></h5>
			</div>
		</div>
		<div class="pf-body">
			<ul class="extra-details">
				<?php foreach ((array) $data['rows'] as $row):
					if ( is_serialized( $row['content'] ) ) {
						$row['content'] = join( ', ', unserialize( $row['content'] ) );
					}

					if ( ! trim( $row['content'] ) ) {
						continue;
					}
					?>
					<li>
						<p class="item-attr"><?php echo esc_html( $row['title'] ) ?></p>
						<p class="item-property"><?php echo esc_html( $row['content'] ) ?></p>
					</li>
				<?php endforeach ?>
			</ul>
		</div>
	</div>
</div>
