<?php
/**
 * @var  $this \SolrPlugin\Settings
 * @var \wpdb
 */
$base_url =  admin_url('options-general.php?page=solr&tab='.$current);

function get_solr_crons(){
	$wp_crons = _get_cron_array();
	$solr_crons = array();
	foreach($wp_crons as $wp_cron_arr){
		foreach($wp_cron_arr as $key => $wp_cron){
			if(strpos($key, "solr") === false) continue;
			if(!key_exists($key, $solr_crons)) $solr_crons[$key] = array();
			$solr_crons[$key][] = array_values($wp_cron)[0]['schedule'];
		}
	}
	return $solr_crons;
}

$solr_crons = get_solr_crons();

if( !empty($_GET["action"]) ){

	switch($_GET["action"]){
		case "delete":
			$this->plugin->schedule->unregister_all();
			break;
		case "delete_single":
			if(!empty($_GET["schedule_event"])){
				$this->plugin->schedule->unregister_event(sanitize_text_field($_GET["schedule_event"]));
			}
			break;
		case "save":
			$this->plugin->schedule->register();
			break;
	}
	$solr_crons = get_solr_crons();
}

?>
<div class="wrap">
	<p>
		<?php
		if(count($solr_crons) == 0){
			?><a class="button-primary" href="<?php echo $base_url.'&action=save'; ?>">Schedule solr events</a><?php
		} else{
			?><a class="button-primary solr-delete" href="<?php echo $base_url.'&action=delete'; ?>">Unschedule all solr events</a><?php
		}
		?>
	</p>

	<h2 class="title">Sheduled solr events</h2>
	<?php
	if(count($solr_crons) > 0){
		?>
		<table class="form-table">
			<?php
			foreach($solr_crons as $key => $solr_cron){
				?>
				<tr>
					<th><?php echo $key; ?></th>
					<td>
						<p><?php echo implode(" + ", $solr_cron);	?>
							<a class="button-primary solr-delete" href="<?php echo $base_url.'&action=delete_single&schedule_event='.$key; ?>">Unschedule event</a>
						</p>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php
	}
	?>

	<p class="description">Scheduled events are cron like operations that
		execute by an specific interval and only if a user visits the website. For most sites that's ok.
		But it can worsen page speed for users who are executing the event. Additionally you can't index
	a very lot of posts to solr at a time because you are restricted to apache timeout limit for the operation.
	<br>So for hugh sites with at lot of traffic and content counts of serveral 10.000 posts for the index we recommand
	to unschedule solr events and use the cron.php of the plugin with a real cronjob.</p>

</div>

