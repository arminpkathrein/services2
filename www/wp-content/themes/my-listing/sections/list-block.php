<?php
	$data = c27()->merge_options([
			'icon' => '',
			'icon_style' => 1,
			'title' => '',
			'items' => [],
			'item_interface' => 'ELEMENTOR_LINK_ARRAY',
            'ref' => '',
			'wrapper_class' => 'block-element grid-item reveal',
		], $data);

	$items = $data['items'];

	if ($data['item_interface'] == 'WP_TERM') {
		$items = [];

		foreach ($data['items'] as $item) {
			$term = new CASE27\Classes\Term( $item );

			$items[] = [
				'_id' => uniqid() . '__list_item',
				'title' => $term->get_name(),
				'icon' => $term->get_icon(),
				'type' => 'link',
				'link_hover_color' => $term->get_color(),
				'text_hover_color' => $term->get_text_color(),
				'link' => [
					'url' => $term->get_link(),
					'is_external' => false,
				],
			];
		}
	}

	if ($data['item_interface'] == 'CASE27_DETAILS_ARRAY') {
		$items = [];

		foreach ($data['items'] as $item) {
			if ( is_serialized( $item['content'] ) ) {
				$item['content'] = join( ', ', unserialize( $item['content'] ) );
			}

			$items[] = [
				'_id' => uniqid() . '__list_item',
				'title' => $item['content'],
				'icon' => $item['icon'],
				'type' => 'plain_text',
			];
		}
	}

	if ($data['item_interface'] == 'CASE27_LINK_ARRAY') {
		$items = [];

		$networks = [
			'facebook'  => ['icon' => 'fa fa-facebook', 'color' => '#3b5998'],
			'twitter'   => ['icon' => 'fa fa-twitter', 'color' => '#4099FF'],
			'google'    => ['icon' => 'fa fa-google-plus', 'color' => '#D34836'],
			'instagram' => ['icon' => 'fa fa-instagram', 'color' => '#e1306c'],
			'youtube'   => ['icon' => 'fa fa-youtube-play', 'color' => '#ff0000'],
			'snapchat'  => ['icon' => 'fa fa-snapchat-ghost', 'color' => '#fffc00'],
			'tumblr'    => ['icon' => 'fa fa-tumblr', 'color' => '#35465c'],
			'reddit'    => ['icon' => 'fa fa-reddit', 'color' => '#ff4500'],
			'linkedin'  => ['icon' => 'fa fa-linkedin', 'color' => '#0077B5'],
			'pinterest' => ['icon' => 'fa fa-pinterest', 'color' => '#C92228'],
			'deviantart' => ['icon' => 'fa fa-deviantart', 'color' => '#05cc47'],
			'vkontakte' => ['icon' => 'fa fa-vk', 'color' => '#5082b9'],
			'soundcloud' => ['icon' => 'fa fa-soundcloud', 'color' => '#ff5500'],
			'default'   => ['icon' => 'fa fa-link', 'color' => '#70ada5'],
		];

		foreach ($data['items'] as $item) {
			$network = 'default';
			$hostname = parse_url($item['content'], PHP_URL_HOST);

			foreach ((array) array_keys($networks) as $ntw) {
				if (strpos(strtolower($hostname), strtolower($ntw)) !== false) $network = $ntw;
			}

			if ($network == 'default') {
				foreach ((array) array_keys($networks) as $ntw) {
					if (strpos(strtolower($item['title']), strtolower($ntw)) !== false) $network = $ntw;
				}
			}

			if (!trim($item['title']) || !trim($item['content'])) {
				continue;
			}

			$items[] = [
				'_id' => uniqid() . '__list_item',
				'title' => $item['title'],
				'icon' => $networks[$network]['icon'],
				'type' => 'link',
				'link_hover_color' => $networks[$network]['color'],
				'link' => [
					'url' => $item['content'],
					'is_external' => true,
				],
			];
		}
	}

?>

<?php if ($items): ?>
	<div class="<?php echo esc_attr( $data['wrapper_class'] ) ?>">
		<div class="element list-block">
			<div class="pf-head">
				<div class="title-style-1 title-style-<?php echo esc_attr( $data['icon_style'] ) ?>">
					<?php if ($data['icon_style'] != 3): ?>
						<?php echo c27()->get_icon_markup($data['icon']) ?>
					<?php endif ?>
					<h5><?php echo esc_html( $data['title'] ) ?></h5>
				</div>
			</div>
			<div class="pf-body">
				<ul class="details-list social-nav">
					<?php foreach ((array) $items as $item): ?>
						<li class="<?php echo esc_attr( "item_{$item['_id']}" ) ?>">
							<?php if ($item['type'] == 'link'):
								$url = $item['link']['url'];
								$target = $item['link']['is_external'] ? 'target="_blank"' : '';

								if (!isset($GLOBALS['case27_custom_styles'])) $GLOBALS['case27_custom_styles'] = '';

								$GLOBALS['case27_custom_styles'] .= '.details-list .item_' . $item['_id'] . ' a:hover i {';
								$GLOBALS['case27_custom_styles'] .= 'background-color: ' . $item['link_hover_color'] . ';';
								$GLOBALS['case27_custom_styles'] .= 'border-color: ' . $item['link_hover_color'] . ';';

								if ( ! empty( $item['text_hover_color'] ) ) {
									$GLOBALS['case27_custom_styles'] .= 'color: ' . $item['text_hover_color'] . ';';
								}

								$GLOBALS['case27_custom_styles'] .= '}';
								?>

								<a href="<?php echo esc_url( $url ) ?>" <?php echo esc_attr( $target ) ?>>
							<?php endif ?>

							<?php if ($item['icon']): ?>
								<?php echo c27()->get_icon_markup($item['icon']) ?>
							<?php endif ?>

							<span><?php echo esc_html( $item['title'] ) ?></span>

							<?php if ($item['type'] == 'link'): ?>
								</a>
							<?php endif ?>
						</li>
					<?php endforeach ?>
				</ul>
			</div>
		</div>
	</div>
<?php endif ?>
