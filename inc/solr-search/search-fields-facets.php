<?php

namespace SolrPlugin;


class SearchFieldsFacets {
	
	/**
	 * SearchFields constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		
		add_filter('solr_search_select',array($this,'search_select_facets'),10,3);
		
	}
	
	/**
	 * search for facets
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_facets($select,$search_args, $config){
		$facet_set = $select->getFacetSet();

//		$facet_set->createFacetQuery('sm_category')->setQuery('Uncategorized');
		
		if(!empty($search_args['facets']) && $search_args['facets'] ) {
			foreach ($search_args['facets'] as $facet => $values) {
				// TODO: handle multiple facet values query = facet:value AND facet:value
				$parts = array();
				foreach ($values as $value){
					$parts[] = "{$facet}:{$value}";
				}
				$select->addFilterQuery(array('key' => $facet, 'query' => implode(" AND ", $parts)));
			}
		}
		
		if ( isset($config['facets']) ) {
			$facetSet = $select->getFacetSet();
			
			// TODO: show all facets for displaying in frontend
			
			// content type facet
			if (isset($config['facets']['ss_type'])) {
				$facet = $config['facets']['ss_type'];
				// type facet
				$facetSet->createFacetField($facet['title'])
				         ->setField($facet['field'])
				         ->setMinCount(1);
			}
			
			
			if (isset($config['facets']['sm_category'])) {
				$facet = $config['facets']['sm_category'];
				// type facet
				$facetSet->createFacetField($facet['title'])
				         ->setField($facet['field'])
				         ->setMinCount(1);
			}
			
			// year facet
			if (isset($config['facets']['ds_published'])) {
				$facet = $config['facets']['ds_published'];
				// the date facet
				// from epoch until now
				$facetSet->createFacetRange($facet['title'])
				         ->setField($facet['field'])
				         ->setStart(
					         '1970-01-01T00:00:00Z')
				         ->setEnd(
					         str_replace('+00:00', 'Z', date('c')))
				         ->setGap('+1YEAR');
			}
		}
		return $select;
	}
	
}