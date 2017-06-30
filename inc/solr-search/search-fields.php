<?php

namespace SolrPlugin;


class SearchFields {

	const PARAM_QUERY = "s";

	/**
	 * SearchFields constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		
		add_filter('solr_search_select',array($this,'search_select_query'),10,3);
		add_filter('solr_search_select',array($this,'search_select_order'),10,3);
		add_filter('solr_search_select',array($this,'search_select_page'),10,3);
		
		
		add_filter( 'solr_search_select', array( $this, 'search_select_boost' ), 10, 3 );
		add_filter( 'solr_search_select', array( $this, 'search_select_weight' ), 10, 3 );
		
		add_filter('solr_search_select',array($this,'search_select_posts'),10,3);
		
		add_filter('solr_search_select',array($this,'search_select_query_operator'),10,3);
		add_filter('solr_search_select',array($this,'search_select_highlight_fields'),10,3);
		
		require_once "search-fields-facets.php";
		$this->facets = new SearchFieldsFacets($plugin);
		
		require_once "search-fields-spellchecker.php";
		$this->spellchecker = new SearchFieldsSpellchecker($plugin);
		
	}
	
	
	
	/**
	 * search for query string
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_query($select,$search_args, $config){
		if(!empty($search_args) && !empty($search_args[self::PARAM_QUERY])){
			$query = $search_args[self::PARAM_QUERY];
			$select->setQuery($query);
		}
		return $select;
	}
	
	/** search order
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_order($select,$search_args, $config){
		$select->addSort('ds_published',$select::SORT_DESC);
		return $select;
	}
	
	/**
	 * search for published
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_state($select,$search_args, $config){
		$select->createFilterQuery('ss_status')->setQuery('ss_status:publish');
		return $select;
	}
	
	/**
	 * search on page num
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_page($select,$search_args, $config){
		if(!empty($search_args["paged"])){
			$select->setStart(($search_args['paged'] - 1) * $config['query_limit']);
			$select->setRows($config['query_limit']);
		}
		return $select;
	}
	
	/**
	 * search boost function
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_boost($select,$search_args, $config){
		
		// empty search args ends up in no results here
		if(empty($search_args[self::PARAM_QUERY])) return $select;
		
		if( !empty($config['boost_functions']) && count($config['boost_functions']) > 0 ) {
			$dismax = $select->getDisMax();
			$dismax->setBoostFunctions(implode(' ', $config['boost_functions']));
		}
		
		return $select;
	}
	
	/**
	 * search spellchecker
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_posts($select,$search_args, $config){
		$select->createFilterQuery('ss_type')->setQuery('ss_type:post');
		return $select;
	}
	
	/**
	 * search weight of fields
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_weight($select,$search_args,$config){
		
		// empty search args ends up in no results here
		if(empty($search_args[self::PARAM_QUERY])) return $select;
		
		// weight of fields
		$dismax = $select->getDisMax();
		
		// build the weight string
		$weightString = '';
		foreach ($config['search_fields'] as $field => $weight) {
			$weightString .= " $field^$weight";
		}
		$dismax->setQueryFields(substr($weightString, 1));
		return $select;
	}
	
	/**
	 * search query operator
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_query_operator($select,$search_args,$config){
		// set the default operator
		$select->setQueryDefaultOperator($config['default_query_operator']);
		return $select;
	}
	
	/**
	 * search query operator
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_highlight_fields($select,$search_args, $config){
		if(!empty($search_args) && !empty($search_args[self::PARAM_QUERY])){
			
			$select->getHighlighting()->setFields( $config['highlight_fields'] );
			$select->getHighlighting()->setSimplePrefix( '<b>' );
			$select->getHighlighting()->setSimplePostfix( '</b>' );
			$select->getHighlighting()->setHighlightMultiTerm( true );
		}
		return $select;
	}
	
}