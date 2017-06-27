<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 27.06.17
 * Time: 08:35
 */

namespace SolrPlugin;
use function SolrPlugin\Flags\tablename;

/**
 * Class Update
 * @package SolrPlugin
 */
class Update {

	const VERSION = 1;

	/**
	 * Update constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct(Plugin $plugin) {
		add_action('plugins_loaded', array($this, "check_and_run"));
	}

	function has_version(){
		return (get_site_option(Plugin::OPTION_DATA_VERSION) !== false);
	}

	function get_version(){
		return get_site_option(Plugin::OPTION_DATA_VERSION, 0);
	}

	function set_version($version){
		return update_site_option(Plugin::OPTION_DATA_VERSION, $version);
	}

	/**
	 * check for updates and run them if needed
	 */
	function check_and_run(){
		$current_version = $this->get_version();
		// skip if latest version
		if ( $current_version == self::VERSION ) {
			return;
		}

		for ( $i = $current_version + 1; $i <= self::VERSION; $i ++ ) {
			$method = "update_{$i}";
			if ( method_exists( $this, $method ) ) {
				$this->$method();
				$this->set_version( $i );
			}
		}
	}

	function update_1(){

		Flags\install();
		global $wpdb;
		$query = "
		INSERT INTO ".tablename()." (item_id, type, flag) 
		SELECT post_id as item_id, 'post' as type, %s as flag FROM {$wpdb->postmeta} 
		WHERE meta_key = %s
		";
		$wpdb->query($wpdb->prepare($query, SOLR_FLAG_INDEXED, "solr_indexed"));
		$wpdb->query($wpdb->prepare($query, SOLR_FLAG_IGNORED, "solr_ignored"));
		$wpdb->query($wpdb->prepare($query, SOLR_FLAG_ERRORED,"solr_error"));

	}

}