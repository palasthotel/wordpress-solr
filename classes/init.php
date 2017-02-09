<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 08.02.17
 * Time: 19:13
 */

namespace SolrPlugin;


class Init {
	
	/**
	 * Init constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct($plugin) {
		$this->plugin = $plugin;
		
		if($this->plugin->is_enabled()){
			/**
			 * if enabled overwrite default search
			 * and do the search if needed
			 */
			add_action('init', array($this, 'do_search'));
			add_filter('posts_request', array($this, 'disable_search_query'), 10, 2);
			add_action('pre_get_posts', array($this, 'backend_search_query'), 99 );
		}
	}
	
	/**
	 * do the search in init action
	 */
	function do_search(){
		if($this->plugin->request->is_search()){
			$this->plugin->request->get_search_results();
		}
	}
	
	/**
	 * disable_search_query
	 * @param array $request
	 * @param $query \WP_Query
	 * @return array|bool
	 */
	function disable_search_query($request, $query){
		/**
		 * if it is the main query and its a search and not in backend
		 */
		if($query->is_main_query() &&
		   $this->plugin->request->is_search() &&
		   !is_admin() ){
			return false;
		}
		return $request;
	}
	
	/**
	 * @param \WP_Query $query
	 */
	function backend_search_query($query){
		global $pagenow;
		if ( is_admin() && $query->is_main_query() ) {
			if ($query->is_search) {
				
				if($pagenow == 'edit.php'){
					
					
					
					$this->is_disabled_backend = true;

//					$my_backend_search = $this->get_search_results(array());

//					$ids = array();
//					foreach ($my_backend_search as $document){
//						$ids[] = $document->item_id;
//					}

//					$query->query_vars = array(
//						'post__in' => $ids,
//						'orderby' => 'post__in',
//					);
				}
			}
		}
		
	}
	
}