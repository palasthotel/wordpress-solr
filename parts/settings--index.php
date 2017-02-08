<?php
/**
 * @var  $this \SolrPlugin\Settings
 * @var \wpdb
 */
global $wpdb;
$base_url =  admin_url('options-general.php?page=solr&tab='.$current);

?>
<div class="wrap">

	<p>
		<a class="button-primary" href="<?php echo $base_url.'&action=update'; ?>"><?php esc_attr_e('Update index'); ?></a>
		<a class="button-primary" href="<?php echo $base_url.'&action=optimize'; ?>"><?php esc_attr_e('Optimize index'); ?></a>
		<a id="solr-delete" class="button-primary solr-delete do-solr-delete" href="<?php echo $base_url.'&action=delete'; ?>"><?php esc_attr_e('Delete index'); ?></a>
	</p>
	<div class="solr-operation-result"><?php


	if (isset($_GET['action'])) {
		$solr = $this->plugin->get_solr();

		switch($_GET['action']){
			case 'update':
				$result = $this->plugin->index_posts(300);
				foreach ($result->posts as $post) {
					/**
					 * @var WP_Post $post
					 */
					print "<p>".$post->post_title."<p>";
				}
				echo '<p>Index updated '.count($result->posts).'</p>';
				break;
			case 'delete':
				$solr->deleteIndex();
				$this->plugin->posts->reset_meta();
				echo '<p>Index deleted</p>';
			case 'optimize':
				$result = $solr->optimizeIndex();
				echo '<p>Index optimized</p>';
				break;
			default:
				echo '<p>Unknown action "'.$_GET['action'].'"</p>';
		}
	}
	?></div>

	<h2 class="title">Index info</h2>

	<table class="form-table">
		<tr>
			<th>Last index run</th>
			<td>
				<p><?php echo $this->plugin->get_latest_run(); ?></p>
			</td>
		</tr>
		<tr>
			<th>Documents in Solr</th>
			<td>
				<p><?php echo $this->plugin->get_solr()->getNumberOfDocuments(); ?></p>
				<p class="description">Number of documents in solr</p>
			</td>
		</tr>
		<tr>
			<?php
			$indexed = $wpdb->get_var(
			  $wpdb->prepare(
				"SELECT sum(meta_value) FROM ".$wpdb->postmeta." WHERE meta_key = %s",
				$this->plugin->posts->meta_indexed
			  )
			);
			?>
			<th>Contents indexed</th>
			<td>
				<p><?php echo ($indexed>0)? $indexed: "0"; ?></p>
				<p class="description">Number of contents mark as indexed in wordpress</p>
			</td>
		</tr>
		<tr>
			<?php
			$ignored = $wpdb->get_var(
			  $wpdb->prepare(
				"SELECT sum(meta_value) FROM ".$wpdb->postmeta." WHERE meta_key = %s",
				$this->plugin->posts->meta_ignored
			  )
			);
			?>
			<th>Contents ignored</th>
			<td>
				<p><?php echo ($ignored>0)? $ignored: "0"; ?></p>
				<p class="description">Number of contents marked as ignored</p>
			</td>
		</tr>
		<tr>
			<?php
			$error = $wpdb->get_var(
			  $wpdb->prepare(
				"SELECT sum(meta_value) FROM ".$wpdb->postmeta." WHERE meta_key = %s",
				$this->plugin->posts->meta_error
			  )
			);
			?>
			<th>Contents with error</th>
			<td>
				<p><?php echo ($error>0)? $error: "0"; ?></p>
				<p class="descrition">Number of contents with error while indexing to solr</p>
			</td>
		</tr>
	</table>

	<?php
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
	if($query->have_posts()){
		?>
		<h3>Newest Posts with error</h3>
		<?php
	}
	while($query->have_posts()) {
		$query->the_post();
		echo "<p><a href='".get_the_permalink()."'>".get_the_title()."</a></p>";
	}
	wp_reset_postdata();
	?>
</div>