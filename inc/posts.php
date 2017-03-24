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
		$this->reset_indexed( $post_id );
		$this->reset_ignored( $post_id );
		$this->reset_error( $post_id );
		
		$this->plugin->solr_index->updatePost(array($post));
	}
	
	/**
	 * @param $post_id
	 */
	public function delete_post( $post_id ) {
		$post = get_post( $post_id );
		$this->reset_ignored( $post_id );
		$this->reset_indexed( $post_id );
		$this->reset_error( $post_id );
		
		// TODO: update post status to trash or delete it
		
		$this->plugin->solr_index->deletePost( array( $post ) );
	}
	
	/**
	 * set post as indexed by solr
	 *
	 * @param $post_id
	 */
	public function set_indexed( $post_id ) {
		update_post_meta( $post_id, Plugin::POST_META_INDEXED, TRUE );
	}
	
	/**
	 * reset post is index by solr
	 *
	 * @param $post_id
	 */
	public function reset_indexed( $post_id ) {
		delete_post_meta( $post_id, Plugin::POST_META_INDEXED );
	}
	
	/**
	 * set post ignored by solr
	 *
	 * @param $post_id
	 */
	public function set_ignored( $post_id ) {
		update_post_meta( $post_id, Plugin::POST_META_IGNORED, TRUE );
	}
	
	/**
	 * reset post ignored by solr
	 *
	 * @param $post_id
	 */
	public function reset_ignored( $post_id ) {
		delete_post_meta( $post_id, Plugin::POST_META_IGNORED );
	}
	
	/**
	 * set post error by solr
	 *
	 * @param $post_id
	 */
	public function set_error( $post_id ) {
		update_post_meta( $post_id, Plugin::POST_META_ERROR, TRUE );
	}
	
	/**
	 * reset post error by solr
	 *
	 * @param $post_id
	 */
	public function reset_error( $post_id ) {
		delete_post_meta( $post_id, Plugin::POST_META_ERROR );
	}
	
	/**
	 * reset ignored and indexed meta for all posts
	 */
	public function reset_meta() {
		/**
		 * @var \wpdb
		 */
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => Plugin::POST_META_INDEXED ), array( '%s' ) );
		$wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => Plugin::POST_META_IGNORED ), array( '%s' ) );
		$wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => Plugin::POST_META_ERROR ), array( '%s' ) );
	}
	
	/**
	 * get modified posts for solr indexing
	 *
	 * @param $number
	 *
	 * @return array
	 */
	public function getModified( $number ) {
		return get_posts(array(
			'post_type'           => 'any',
			'post_status'         => array( 'publish' ),
			'orderby'             => array( 'modified', 'date', 'ID' ),
			'order'               => 'DESC',
			'posts_per_page'      => $number,
			'ignore_sticky_posts' => TRUE,
			'meta_query'          => array(
				'relation' => 'AND',
				array(
					'key'     => Plugin::POST_META_INDEXED,
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => Plugin::POST_META_IGNORED,
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => Plugin::POST_META_ERROR,
					'compare' => 'NOT EXISTS',
				),
			),
		));
	}
	
}