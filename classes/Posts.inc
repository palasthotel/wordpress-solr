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
	public $config;
	public function __construct($config){
		$this->config = $config;
	}

	public function getModifiedPosts() {

		// find newer posts
		$query = new WP_Query(
			array(
				'post_type' => 'any',
				'post_status' => 'any',
				'orderby' => array('modified', 'date', 'ID'),
				'order' => 'ASC',
				'posts_per_page' => $this->config['posts_per_index_update'],
				'ignore_sticky_posts' => TRUE,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => $this->config['meta_key'],
						'compare' => 'NOT EXISTS',
					),
					array(
						'key' => $this->config['meta_key_ignore'],
						'compare' => 'NOT EXISTS',
					),
				)
			)
		);

		// when posts have been found
		if ($query->have_posts()) {

			// filter posts
			$posts = array();
			foreach ($query->posts as $post) {

				// function in config
				$post_filtered = true;
				$post_filtered = apply_filters('solr_post_filter',$post_filtered);

				if (phsolr_post_filter($post))
				{
					$posts[] = $post;
				} else {
					$this->set_ignored($post->ID);
				}
			}

			$count = get_option('phsolr_post_indexed_count',0);
			$count = $count+count($posts);
			update_option('phsolr_post_indexed_count', $count);

			return $posts;
		} else {
			return array();
		}
	}

	public function set_indexed($post_id){
		update_post_meta($post_id, $this->config['meta_key'], true);
	}
	public function set_ignored($post_id){
		update_post_meta($post_id, $this->config['meta_key_ignore'], true);
	}
	public function increase_index_count($add = 1){
		$count = get_option('phsolr_post_indexed_count',0);
		$count = $count+$add;
		update_option('phsolr_post_indexed_count', $count);
	}
}