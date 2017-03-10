<?php

namespace SolrPlugin;


class Solarium {
	private static $solarium = null;
	
	/**
	 * @param \SolrPlugin\Plugin $plugin
	 *
	 * @return \Solarium\Client
	 */
	public static function instance( Plugin $plugin){
		if ( Solarium::$solarium === NULL ) {
			
			/**
			 * get config
			 */
			$config = $plugin->config;
			
			/**
			 * init solarium configuration
			 */
			$endpoint = array(
				'host'     => $config->get_option( Plugin::OPTION_HOST ),
				'port'     => $config->get_option( Plugin::OPTION_PORT ),
				'path'     => $config->get_option( Plugin::OPTION_PATH ),
				'core'     => $config->get_option( Plugin::OPTION_CORE ),
				'username' => $config->get_option( Plugin::OPTION_USERNAME ),
				'password' => $config->get_option( Plugin::OPTION_PW ),
				'timeout'  => $config->get_option( Plugin::OPTION_TIMEOUT ),
			);
			
			/**
			 * solarium class loader
			 */
			require_once apply_filters( Plugin::FILTER_SOLARIUM_PATH, $plugin->dir . '/lib/autoload.php' );
			
			/**
			 * construct solarium
			 */
			Solarium::$solarium = new \Solarium\Client( array( 'endpoint' => array( $endpoint ) ) );
		}
		
		return Solarium::$solarium;
	}
}