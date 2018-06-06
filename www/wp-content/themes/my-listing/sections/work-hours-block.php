<?php
	$data = c27()->merge_options([
			'icon' => '',
			'icon_style' => 1,
			'title' => '',
			'hours' => [],
			'wrapper_class' => 'grid-item reveal',
			'ref' => '',
		], $data);

	$schedule = new CASE27\Classes\WorkHours( $data['hours'] );

	// dump( $schedule->get_status(), $schedule->get_message(), $schedule->get_active_day(), $schedule->get_open_now() );
?>

<div class="<?php echo esc_attr( $data['wrapper_class'] ) ?>">
	<div class="element work-hours-block">
		<div class="pf-head" data-toggle="collapse" data-target="#open-hours">
			<div class="title-style-1">
				<?php echo c27()->get_icon_markup( $data['icon'] ) ?>
				<h5><span class="<?php echo esc_attr( $schedule->get_status() ) ?> work-hours-status"><?php echo esc_html( $schedule->get_message() ) ?></span></h5>
				<div class="timing-today"><?php echo $schedule->get_todays_schedule() ?></div>
			</div>
		</div>
		<div id="open-hours" class="pf-body collapse">
			<ul class="extra-details">
				<?php foreach ( $schedule->get_schedule() as $weekday ): ?>
					<li>
						<p class="item-attr"><?php echo esc_html( $weekday['day_l10n'] ) ?></p>
						<p class="item-property">
							<?php $range_count = 0; ?>
							<?php foreach ( $weekday['ranges'] as $range ): ?>
								<?php if ( ! empty( $range['from'] ) && ! empty( $range['to'] ) ): $range_count++; ?>
									<span><?php echo esc_html( $schedule->format_time( $range['from'] ) . ' - ' . $schedule->format_time( $range['to'] ) ) ?></span>
								<?php endif ?>
							<?php endforeach ?>

							<?php if ( ! $range_count ): ?>
								<em><?php _e( 'Closed', 'my-listing' ) ?></em>
							<?php endif ?>
						</p>
					</li>
				<?php endforeach ?>

				<?php if ( ! empty( $data['hours']['timezone'] ) ):
					$localTime = new DateTime( 'now', new DateTimeZone( $data['hours']['timezone'] ) );
					?>
					<p class="work-hours-timezone">
						<em><?php printf( __( '%s local time', 'my-listing' ), $localTime->format( get_option( 'time_format' ) . ' ' . get_option( 'date_format' ) ) ) ?></em>
					</p>
				<?php endif ?>
			</ul>
		</div>
	</div>
</div>
