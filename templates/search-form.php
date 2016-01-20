<?php
/**
 * Search form template
 * @var SolrPlugin\SearchPage $this
 * @var $form
 * @var array $search_args
 * @var null|\Solarium\QueryType\Select\Result\Result $search_results
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo home_url('/') ?>">
	<div>
		<label> <span class="screen-reader-text">Search for:</span> <input
			  type="search" class="search-field"
			  placeholder="<?php echo __('Search …') ?>"
			  value="<?php echo $search_args['s']; ?>" name="s"
			  title="Search for:" />
		</label>
		<input type="submit" class="search-submit" value="Search" />
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




