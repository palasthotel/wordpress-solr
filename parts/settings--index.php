<?php
$base_url =  admin_url('options-general.php?page=solr&tab='.$current);

if (isset($_GET['action'])) {
	if ($_GET['action'] === 'rebuild') {
		$phsolr->resetPostIndex();
		$phsolr->resetCommentIndex();
		echo '<p>Index rebuild initialized.</p>';
	} else if ($_GET['action'] == 'optimize') {
		$phsolr->optimizeIndex();
		echo '<p>Index optimized</p>';
	} else if ($_GET['action'] == 'update') {
		$phsolr->updatePostIndex();
		$phsolr->updateCommentIndex();
		echo '<p>Index updated</p>';
	} else if ($_GET['action'] === 'delete') {
		$phsolr->deleteIndex();
		echo '<p>Index deleted</p>';
	} else {
		echo '<p>Unknown action "'.$_GET['action'].'"</p>';
	}
}


?>

<div class="wrap">
	<h3>Advanced index operations</h3>
	<ul>
		<li><a href="<?php echo $base_url.'&action=update'; ?>"><?php esc_attr_e('Update index'); ?></a></li>
		<li><a href="<?php echo $base_url.'&action=rebuild'; ?>"><?php esc_attr_e('Rebuild index'); ?></a></li>
		<li><a href="<?php echo $base_url.'&action=optimize'; ?>"><?php esc_attr_e('Optimize index'); ?></a></li>
		<li><a href="<?php echo $base_url.'&action=delete'; ?>"><?php esc_attr_e('Delete index'); ?></a></li>
	</ul>
</div>