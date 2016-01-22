<?php
/**
 * @var  $this \SolrPlugin\Settings
 */
?>
<div class="wrap">
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
					'key' => $this->plugin->posts->meta_indexed,
					'value' => 'EXISTS',
				),
			)
		)
	);

	while($query->have_posts()) {
		$query->the_post();
		echo "<p>".get_the_title()."</p>";
	}
	wp_reset_postdata();

	?>
</div>