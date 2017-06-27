<?php

namespace SolrPlugin;


class Posts {
	/**
	 * Posts constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		
		add_action( 'save_post', array( $this, 'save_post' ), 99, 2 );
		add_action( 'delete_post', array( $this, 'delete_post' ) );
		add_action( 'wp_trash_post', array( $this, 'delete_post' ) );
	}
	
	/**
	 * delete indexes on changing
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 */
	public function save_post( $post_id, $post ) {
		// will run with next scheduled indexing
		if($post->post_status == 'publish'){
			Flags\set_modified($post_id, 'post');
		}
	}
	
	/**
	 * @param $post_id
	 */
	public function delete_post( $post_id ) {
		// will be deleted with next scheduled solr run
		$post = get_post($post_id);
		$this->plugin->solr_index->deletePost(array( $post ));
		Flags\delete(array( "item_id" => $post_id), array('%d'));
	}
	
	/**
	 * get modified posts for solr indexing
	 *
	 * @param $number
	 *
	 * @return array
	 */
	public function getModified( $number ) {

		global $wpdb;

		$post_types = apply_filters( Plugin::FILTER_SOLR_INDEX_POST_TYPES, array('post') );
		$where_post_types = array();
		foreach ($post_types as $post_type){
			$where_post_types[] = " p.post_type = '{$post_type}' ";
		}
		$where_post_types_string = implode(' OR ', $where_post_types);


		$flags_table = Flags\tablename();
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT p.ID FROM {$wpdb->posts} as p 
				LEFT JOIN {$flags_table} as f  on ( p.ID = f.item_id AND f.type = %s )  
				WHERE (f.flag IS NULL OR f.flag = %s) AND p.post_status = 'publish' AND ({$where_post_types_string})
				ORDER BY p.ID DESC LIMIT %d
				",
				"post",
				SOLR_FLAG_MODIFIED,
				$number
			)
		);

		if( !is_wp_error($ids) && is_array($ids) && count($ids) > 0){
			return get_posts(array(
				'post_type'           => $post_types,
				'post_status'         => array( 'publish' ),
				'posts_per_page'      => $number,
				'ignore_sticky_posts' => TRUE,
				'post__in' => $ids,
			));
		}

		return array();
	}
	
}