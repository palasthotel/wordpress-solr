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
	 * @var \SolrPlugins
	 */
	public $plugin;

	/**
	 * Comments constructor.
	 * @param \SolrPlugin $plugin
	 */
	public function __construct(\SolrPlugin $plugin){
		$this->plugin = $plugin;
	}

	/**
	 * get modified
	 * @return array
	 */
	public function getModified( $number = 10) {
		// TODO: get new comments and index them
	}
}