<?php
/**
 * @var  $this \SolrPlugin\Settings
 */
?>
<div class="wrap">
	<h3>Posts with error while indexing</h3>
	<?php
	// get all error posts
	
	$query = new \WP_Query(
		array(
			'post_type' => 'any',
			'orderby' => array('modified', 'date', 'ID'),
			'order' => 'DESC',
			'posts_per_page' => 10,
			'ignore_sticky_posts' => TRUE,
			'meta_query' => array(
				array(
					'key' => $this->plugin->posts->meta_error,
					'compare' => 'EXISTS',
				),
			)
		)
	);
	while($query->have_posts()) {
		$query->the_post();
		echo "<p><a href='".get_the_permalink()."'>".get_the_title()."</a></p>";
	}
	wp_reset_postdata();

	global $wpdb;

	$indexed = $wpdb->get_var(
	  $wpdb->prepare(
		"SELECT sum(meta_value) FROM ".$wpdb->postmeta." WHERE meta_key = %s",
		$this->plugin->posts->meta_indexed
	  )
	);
	var_dump($indexed);


	$ignored = $wpdb->get_var(
	  $wpdb->prepare(
		"SELECT sum(meta_value) FROM ".$wpdb->postmeta." WHERE meta_key = %s",
		$this->plugin->posts->meta_ignored
	  )
	);
	var_dump($ignored);
	?>
</div>