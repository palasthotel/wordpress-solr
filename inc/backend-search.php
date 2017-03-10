<?php

namespace SolrPlugin;

class BackendSearch {
	
	/**
	 * BackendSearch constructor.
	 *
	 * @param $plugin
	 */
	function __construct($plugin) {
		// TODO: not working yet
		add_action('pre_get_posts', array($this, 'backend_search_query'), 99 );
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