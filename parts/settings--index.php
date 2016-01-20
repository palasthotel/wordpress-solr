<?php
/**
 * @var  $this \SolrPlugin\Settings
 */
?>
<div class="wrap">
	<h3>Advanced index operations</h3>
	<?php

	$base_url =  admin_url('options-general.php?page=solr&tab='.$current);

	if (isset($_GET['action'])) {
		$solr = $this->plugin->get_solr();

		switch($_GET['action']){
			case 'update':
				$result = $this->plugin->index_posts(2);
				echo '<p>Index updated '.count($result->posts).'</p>';
				break;
			case 'delete':
				$solr->deleteIndex();
				$this->plugin->posts->reset_meta();
				echo '<p>Index deleted</p>';
				break;
			case 'optimize':
				$result = $solr->optimizeIndex();
				echo '<p>Index optimized</p>';
				break;
			default:
				echo '<p>Unknown action "'.$_GET['action'].'"</p>';
		}
	}
	?>
	<ul>
		<li><a href="<?php echo $base_url.'&action=update'; ?>"><?php esc_attr_e('Update index'); ?></a></li>
		<li><a href="<?php echo $base_url.'&action=optimize'; ?>"><?php esc_attr_e('Optimize index'); ?></a></li>
		<li><a href="<?php echo $base_url.'&action=delete'; ?>"><?php esc_attr_e('Delete index'); ?></a></li>
	</ul>
</div>