<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 23.11.15
 * Time: 14:17
 */

namespace SolrPlugin;


class Posts
{
	/**
	 * @var \SolrPlugins
	 */
	public $plugin;

	/**
	 * Posts constructor.
	 * @param \SolrPlugin $plugin
	 */
	public function __construct(\SolrPlugin $plugin){
		$this->plugin = $plugin;
		$this->meta_indexed = $this->plugin->prefix."indexed";
		$this->meta_ignored = $this->plugin->prefix."ignored";
		$this->meta_error = $this->plugin->prefix."error";
		add_action( 'save_post', array($this,'save_post') );
		add_action( 'delete_post', array($this, 'delete_post') );
		add_action( 'wp_trash_post', array($this, 'delete_post'));
	}

	/**
	 * delete indexes on changing
	 * @param $post_id
	 */
	public function save_post($post_id){
		$this->reset_indexed($post_id);
		$this->reset_ignored($post_id);
		$this->reset_error($post_id);
	}

	/**
	 * @param $post_id
	 */
	public function delete_post($post_id){
		$post = get_post($post_id);
		$this->reset_ignored($post_id);
		$this->reset_indexed($post_id);
		$this->reset_error($post_id);
		$this->plugin->get_solr()->deletePostIndex(array($post));
	}

	/**
	 * get modified posts for solr indexing
	 * @param $number
	 * @return array
	 */
	public function getModified($number) {
		$query = new \WP_Query(
			array(
				'post_type' => 'any',
				'post_status' => 'publish',
				'orderby' => array('modified', 'date', 'ID'),
				'order' => 'DESC',
				'posts_per_page' => $number,
				'ignore_sticky_posts' => TRUE,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => $this->meta_indexed,
						'compare' => 'NOT EXISTS',
					),
					array(
						'key' => $this->meta_ignored,
						'compare' => 'NOT EXISTS',
					),
				)
			)
		);
		$posts = array();
		if ($query->have_posts()) {
			foreach ($query->posts as $post) {
				/**
				 * @var $post \WP_Post
				 */
				$posts[] = $post;
			}
		}
		return $posts;
	}

	/**
	 * set post as indexed by solr
	 * @param $post_id
	 */
	public function set_indexed($post_id){
		update_post_meta($post_id, $this->meta_indexed, true);
	}

	/**
	 * reset post is index by solr
	 * @param $post_id
	 */
	public function reset_indexed($post_id){
		delete_post_meta($post_id, $this->meta_indexed);
	}

	/**
	 * set post ignored by solr
	 * @param $post_id
	 */
	public function set_ignored($post_id){
		update_post_meta($post_id, $this->meta_ignored, true);
	}

	/**
	 * reset post ignored by solr
	 * @param $post_id
	 */
	public function reset_ignored($post_id){
		delete_post_meta($post_id, $this->meta_ignored);
	}

	/**
	 * set post error by solr
	 * @param $post_id
	 */
	public function set_error($post_id){
		update_post_meta($post_id, $this->meta_error, true);
	}

	/**
	 * reset post error by solr
	 * @param $post_id
	 */
	public function reset_error($post_id){
		delete_post_meta($post_id, $this->meta_error);
	}

	/**
	 * reset ignored and indexed meta for all posts
	 */
	public function reset_meta(){
		/**
		 * @var \wpdb
		 */
		global $wpdb;
		$wpdb->delete($wpdb->prefix.'postmeta',array('meta_key'=>$this->meta_indexed),array('%s'));
		$wpdb->delete($wpdb->prefix.'postmeta',array('meta_key'=>$this->meta_ignored),array('%s'));
		$wpdb->delete($wpdb->prefix.'postmeta',array('meta_key'=>$this->meta_error),array('%s'));
	}
}