<?php

namespace CASE27\Classes;

use \DateTime as DateTime;

class WorkHours {

	protected $hours = [],
			  $raw_hours = [];

	protected $status = '',
			  $message = '',
			  $open_now = false,
			  $active_day = '';

	protected $timezone = '',
			  $weekdays = [],
			  $weekdays_l10n = [];

	public function __construct( $hours ) {
		$this->raw_hours = $hours;
		$this->timezone  = date_default_timezone_get();
		$this->weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
		$this->weekdays_l10n = [
			__( 'Monday', 'my-listing' ),
			__( 'Tuesday', 'my-listing' ),
			__( 'Wednesday', 'my-listing' ),
			__( 'Thursday', 'my-listing' ),
			__( 'Friday', 'my-listing' ),
			__( 'Saturday', 'my-listing' ),
			__( 'Sunday', 'my-listing' )
		];

		$this->weekdays_l10n = array_combine( $this->weekdays, $this->weekdays_l10n );

		$this->to_parseable_format();

		if ( ! empty( $this->raw_hours['timezone'] ) ) {
			date_default_timezone_set( $this->raw_hours['timezone'] );
		}

		$this->parse();

		if ( ! empty( $this->raw_hours['timezone'] ) ) {
			date_default_timezone_set( $this->timezone );
		}
	}

	/*
	 * Convert hours to the format of a multidimensional array with day names as keys,
	 * and each day with arrays of hour ranges, with 'from' and 'to' keys.
	 */
	public function to_parseable_format() {
		foreach ( $this->weekdays as $weekday ) {
			$this->hours[ $weekday ] = [];

			if ( ! empty( $this->raw_hours[ $weekday ] ) ) {
				$this->hours[ $weekday ] = $this->raw_hours[ $weekday ];

				// Convert from the single-range day format used in earlier versions.
				if (
					isset( $this->raw_hours[ $weekday ]['from'] ) ||
					isset( $this->raw_hours[ $weekday ]['to'] )
				) {
					$this->hours[ $weekday ] = [ $this->raw_hours[ $weekday ] ];
				}
			}
		}
	}

	public function parse() {
		$today = isset( $this->hours[ date('l') ]) ? $this->hours[date('l')] : false;
		$yesterday = isset( $this->hours[ date( 'l', strtotime('-1 day') ) ]) ?  $this->hours[ date( 'l', strtotime('-1 day' ) ) ] : false;
		$now = DateTime::createFromFormat( 'H:i', date('H:i') );
		$this->active_day = $now->format('l');

		if ( $today && $this->parse_day( $today, $now ) ) {
			return true;
		}

		if ( $yesterday && $this->parse_day( $yesterday, $now, true ) ) {
			$this->active_day = date( 'l', strtotime('-1 day') );
			return true;
		}
	}


	public function parse_day( $day, $time, $yesterday_flag = false ) {
		foreach ( $day as $range ) {
			if ( empty( $range['from'] ) || empty( $range['to'] ) ) {
				continue;
			}

			$start = DateTime::createFromFormat('H:i', $range['from']);
			$end = DateTime::createFromFormat('H:i', $range['to']);

			if ( ! $start || ! $end ) {
				continue;
			}

			if ( $yesterday_flag ) {
				$start->modify('-1 day');
				$end->modify('-1 day');
			}

			/*
			 * If the end time is smaller than the start time, it means
			 * the end time belongs to tomorrow. E.g. 17:00 - 03:00
			 */
			if ( $end <= $start ) {
				$end->modify('+1 day');
			}

			/*
			 * Business is open.
			 */
			if ( $time >= $start && $time < $end ) {
				// Time until closes, in minutes.
				$time_until_closes = ( $end->getTimestamp() - $time->getTimestamp() ) / 60;

				$this->open_now = true;

				if ( $time_until_closes <= 5 ) {
					$this->status  = 'closing';
					$this->message = __( 'Closes in a few minutes', 'my-listing' );
				} elseif ( $time_until_closes <= 30 ) {
					$this->status  = 'closing';
					$this->message = sprintf( __( 'Closes in %d minutes', 'my-listing' ), ( round( $time_until_closes / 5 ) * 5 ) );
				} else {
					$this->status = 'open';
					$this->message = __( 'Open', 'my-listing' );
				}

				return true;
			}

			/*
			 * Business is closed.
			 */
			if ( $time < $start ) {
				// Time until opens, in minutes.
				$time_until_opens = ( $start->getTimestamp() - $time->getTimestamp() ) / 60;
				// dump('__' . $time_until_opens);

				if ( $time_until_opens <= 5 ) {
					$this->message = __( 'Opens in a few minutes', 'my-listing' );
					$this->status = 'opening';

					return true;
				} elseif ( $time_until_opens <= 30 ) {
					$this->message = sprintf( __( 'Opens in %d minutes', 'my-listing' ), ( round( $time_until_opens / 5 ) * 5 ) );
					$this->status = 'opening';

					return true;
				} else {
					$this->status = 'closed';
					$this->message = __( 'Closed', 'my-listing' );
				}
			}
		}

		$this->status = 'closed';
		$this->message = __( 'Closed', 'my-listing' );

		return false;
	}

	public function get_open_now() {
		return (bool) $this->open_now;
	}

	public function get_status() {
		return $this->status;
	}

	public function get_message() {
		return $this->message;
	}

	public function get_active_day() {
		return $this->active_day;
	}

	public function get_todays_schedule() {
		if ( $this->active_day && ! empty( $this->hours[ $this->active_day ] ) ) {
			$range_count = 0;

			$today = array_filter( array_map( function( $hours ) use ( &$range_count ) {
				if ( empty( $hours['from'] ) || empty( $hours['to'] ) ) {
					return false;
				}

				$start = DateTime::createFromFormat('H:i', $hours['from']);
				$end = DateTime::createFromFormat('H:i', $hours['to']);

				if ( ! $start || ! $end ) {
					return false;
				}

				$range_count++;

				return $this->format_time( $hours['from'] ) . ' - ' . $this->format_time( $hours['to'] );
			}, $this->hours[ $this->active_day ] ) );

			if ( $range_count ) {
				ob_start(); ?>
					<div class="hours-today <?php echo $range_count > 1 ? 'multiple-ranges' : 'single-range' ?>">
						<?php _e( 'Open hours today:', 'my-listing' ) ?>
						<?php echo '<span class="ranges-wrapper"><span class="range active">' . join( '</span><span class="range">', $today ) . '</span></span>' ?>
					</div>
				<?php return ob_get_clean();
			}
		}

		return __( 'Closed today', 'my-listing' );
	}

	public function get_schedule() {
		$days = [];
		foreach ( $this->hours as $weekday => $ranges ) {
			$days[ $weekday ] = [
				'day' => $weekday,
				'day_l10n' => $this->weekdays_l10n[ $weekday ],
				'ranges' => $ranges,
			];
		}

		return $days;
	}

	public function format_time( $time ) {
		return date( get_option('time_format'), strtotime( $time ) );
	}

	public function get_day_ranges( $day ) {
		return (array) $this->hours[ $day ];
	}
}