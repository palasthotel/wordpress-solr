<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 23.11.15
 * Time: 14:17
 */

namespace SolrPlugin;

class Comments
{
	/**
	 * Comments constructor.
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ){
		$this->plugin = $plugin;
	}
	
	/**
	 * get modified
	 *
	 * @param int $number
	 *
	 * @return array
	 */
	public function getModified( $number = 10) {
		// TODO: get new comments and index them
	}
}