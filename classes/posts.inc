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
		$this->meta_indexed = $this->plugin->prefix."_indexed";
		$this->meta_ignored = $this->plugin->prefix."_ignored";
	}

	/**
	 * get modified posts
	 * @return array
	 */
	public function getModifiedPosts( $number = 10) {
		// not manipulated posts
		$query = new \WP_Query(
			array(
				'post_type' => 'any',
				'post_status' => 'any',
				'orderby' => array('modified', 'date', 'ID'),
				'order' => 'ASC',
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
		// when posts have been found
		if ($query->have_posts()) {
			// filter posts
			$posts = array();
			foreach ($query->posts as $post) {

				// function in config
				$post_filtered = true;
				$post_filtered = apply_filters('solr_post_filter',$post_filtered);

				if ($post_filtered)
				{
					$posts[] = $post;
				}
			}
			return $posts;
		}
		return $posts;
	}

//	public function set_indexed($post_id){
//		update_post_meta($post_id, $this->config['meta_key'], true);
//	}
//	public function set_ignored($post_id){
//		update_post_meta($post_id, $this->config['meta_key_ignore'], true);
//	}
//	public function increase_index_count($add = 1){
//		$count = get_option('phsolr_post_indexed_count',0);
//		$count = $count+$add;
//		update_option('phsolr_post_indexed_count', $count);
//	}
}