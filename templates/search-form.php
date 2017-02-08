<?php
/**
 * Search form template
 * @var SolrPlugin\SearchPage $this
 * @var $form
 * @var array $solr_search_args
 * @var null|\Solarium\QueryType\Select\Result\Result $solr_search_results
 */
$query = (!empty($solr_search_args['s']))? $solr_search_args['s']: "";
?>
<form style="background-color: black" role="search" method="get" class="solr-search-form" action="<?php echo home_url('/') ?>">
	<div>
		<label> <span class="screen-reader-text"><?php echo __('Search for:'); ?></span> <input
			  type="search" class="search-field"
			  placeholder="<?php echo __('Search …'); ?>"
			  value="<?php echo $query; ?>" name="s"
			  title="<?php echo __('Search for:'); ?>" />
		</label>
		<input type="submit" class="search-submit" value="<?php echo __('Search'); ?>" />
	</div>

	<?php
if ($solr_search_results) {
	?>
	<div class="advanced-search-settings">
		<?php
		$facets = $solr_search_results->getFacetSet()->getFacets();
		foreach ($facets as $key => $facet) :
			?>
			<div class="facet-type">
				<span class="facet-type-id"><?php echo $key; ?></span>
				<?php
				foreach ($facet as $value => $count) :
					if ($count > 0) :
						if ($key === 'Date') {
							$value = date('Y', strtotime($value));
						}
						?>
						<input type="checkbox"
							   name="facet-<?php echo $key . '-' . $value ?>"
							   id="facet-<?php echo $key . '-' . $value ?>"/>
						<label
						  for="facet-<?php echo $key . '-' . $value ?>"><?php echo "$value ($count)" ?></label>
						<br/>

						<?php
					endif;
				endforeach;
				?>
			</div>
			<?php
		endforeach;


		?>
	</div>
	<?php
}
?>



</form>




