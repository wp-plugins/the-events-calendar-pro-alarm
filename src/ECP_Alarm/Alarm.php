<?php
namespace Fragen\ECP_Alarm;

class Alarm {

	protected static $object = false;

	/**
	 * The Main object can be created/obtained via this
	 * method - this prevents unnecessary work in rebuilding the object and
	 * querying to construct a list of categories, etc.
	 *
	 * @return Alarm
	 */
	public static function instance() {
		$class = __CLASS__;
		if ( false === self::$object ) {
			self::$object = new $class();
		}

		return self::$object;
	}

	public function __construct() {
		if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
			add_action( 'admin_notices', array( $this, 'fail_msg' ) );
		}
		add_action( 'init', array( $this, 'add_Alarm' ) );
		add_filter( 'tribe_ical_feed_item', array( $this, 'ical_add_alarm' ), 10, 2 );
	}

	public function fail_msg() {
		?>
		<div class="error notice is-dismissible">
			<p>
				<?php printf( __( 'To begin using The Events Calendar PRO Alarm, please install the latest version of %sThe Events Calendar PRO%s', 'the-events-calendar-pro-alarm' ),
					'<a href="https://theeventscalendar.com/product/wordpress-events-calendar-pro/?source=tri.be">',
					'</a>'
				);
				?>
			</p>
		</div>
		<?php
	}

	public function add_Alarm() {
		$intervals = array( '15', '30', '60' );
		$intervals = implode( "\r\n", $intervals );
		$this->add_custom_field( 'Alarm', 'dropdown', $intervals );
	}

	public function add_custom_field( $label, $type = 'text', $default = '' ) {
		if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
			$custom_fields = tribe_get_option( 'custom-fields' );
			$field_exists  = false;

			// Check in case the "new" custom field is already present
			foreach ( $custom_fields as $field ) {
				if ( $field['label'] === $label ) {
					$field_exists = true;
				}
			}

			// If it is not, add it
			if ( false === $field_exists ) {
				$index = count( $custom_fields ) + 1;

				$custom_fields[] = array(
					'name'   => "_ecp_custom_$index",
					'label'  => $label,
					'type'   => $type,
					'values' => $default
				);

				tribe_update_option( 'custom-fields', $custom_fields );
			}
		}
	}

	public static function ical_add_alarm( $item, $eventPost ) {
		$alarm = tribe_get_custom_field( 'Alarm', $eventPost->ID );
		if ( ! empty( $alarm ) && is_numeric( $alarm ) ) {
			$item[] = 'BEGIN:VALARM';
			$item[] = 'TRIGGER:-PT' . $alarm . "M";
			$item[] = 'END:VALARM';
		}

		return $item;
	}

}
