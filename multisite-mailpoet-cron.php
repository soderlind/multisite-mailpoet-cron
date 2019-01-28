<?php
/**
 * Multisite Mailpoet Cron
 *
 * @package     Multisite Mailpoet Cron
 * @author      Per Soderlind
 * @copyright   2018 Per Soderlind
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Multisite Mailpoet Cron
 * Plugin URI: https://github.com/soderlind/multisite-mailpoet-cron
 * GitHub Plugin URI: https://github.com/soderlind/multisite-mailpoet-cron
 * Description: description
 * Network: true
 * Version:     0.0.4
 * Author:      Per Soderlind
 * Author URI:  https://soderlind.no
 * Text Domain: multisite-mailpoet-cron
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace Soderlind\Mailpoet\Multisite\Cron;

! defined( 'ABSPATH' ) and exit;

add_action( 'mssc_add_to_cron', __NAMESPACE__ . '\run_mailpoet_cron', 10, 2 );


function run_mailpoet_cron( $subsite_id, $subsite_url ) {
	$plugins = (array) get_blog_option( $subsite_id, 'active_plugins' );
	if ( false !== array_search( 'mailpoet/mailpoet.php', $plugins ) ) {

		$cron_url = $subsite_url . '/wp-content/plugins/mailpoet/mailpoet-cron.php';
		if ( is_url( $cron_url ) ) {
			write_log( 'MSSC Extended log: spawn cron url ' . $cron_url );
			wp_remote_post(
				$cron_url,
				array(
					'timeout'   => 0.01,
					'blocking'  => false,
					'sslverify' => false,
				)
			);

		} else {
			write_log( 'MSSC Extended log: ERROR: ' . $cron_url );
		}
	}
}


/**
 * Check if an item exists out there in the "ether".
 *
 * @link https://stackoverflow.com/a/13633911/1434155
 *
 * @param string $url - preferably a fully qualified URL
 * @return boolean - true if it is out there somewhere
 */
function is_url( $url ) {
	if ( ( $url == '' ) || ( $url == null ) ) {
		return false; }
	$response              = wp_remote_head( $url, array( 'timeout' => 5 ) );
	$accepted_status_codes = array( 200, 301, 302 );
	if ( ! is_wp_error( $response ) && in_array( wp_remote_retrieve_response_code( $response ), $accepted_status_codes ) ) {
		return true;
	}
	return false;
}


if ( ! function_exists( 'write_log' ) ) {
	/**
	* Utility function for logging arbitrary variables to the error log.
	*
	* Set the constant WP_DEBUG to true and the constant WP_DEBUG_LOG to true to log to wp-content/debug.log.
	* You can view the log in realtime in your terminal by executing `tail -f debug.log` and Ctrl+C to stop.
	*
	* @param mixed $log Whatever to log.
	*/
	function write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_scalar( $log ) ) {
				error_log( $log );
			} else {
				error_log( print_r( $log, true ) );
			}
		}
	}
}

// Check for minimum supported PHP version
// if(version_compare(phpversion(), '5.6.0', '<')) {
//   echo 'MailPoet requires PHP version 5.6 or newer (version 7.2 recommended).';
//   exit(1);
// }
// if(strpos(@ini_get('disable_functions'), 'set_time_limit') === false) {
//   set_time_limit(0);
// }
// $data = \MailPoet\Cron\CronHelper::createDaemon(null);
// $trigger = new \MailPoet\Cron\Daemon();
// $trigger->run($data);
