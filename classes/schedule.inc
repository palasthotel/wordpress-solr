<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 06.02.16
 * Time: 16:37
 */

namespace SolrPlugin;


class Schedule {

	private $events = array(
		"solr_schedule_update_index",
	  	"solr_schedule_optimize",
	);

	/**
	 * Schedule constructor.
	 * @param \SolrPlugin $plugin
	 */
	function __construct($plugin) {
		$this->plugin = $plugin;
		add_action("solr_schedule_update_index", array($this, "update_index"));
		add_action("solr_schedule_optimize", array($this, "optimize_index"));
	}

	/**
	 * register all events to an hourly execution
	 */
	function register(){
		$this->unregister_all();
		foreach($this->events as $event){
			wp_schedule_event(time(), "hourly", $event);
		}
	}

	/**
	 * unregister all events
	 */
	function unregister_all(){
		foreach($this->events as $event){
			$this->unregister_event($event);
		}
	}

	/**
	 * unregister a single event
	 * @param $event
	 */
	function unregister_event($event){
		wp_clear_scheduled_hook($event);
	}

	/**
	 * do the indexing
	 */
	function update_index(){
		$this->plugin->index_posts(30);
	}

	/**
	 * do the index optimization
	 */
	function optimize_index(){
		$this->plugin->get_solr()->optimizeIndex();
	}
}