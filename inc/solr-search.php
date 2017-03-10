<?php

namespace SolrPlugin;


class SolrSearch {
	
	/**
	 * @var \Solarium\QueryType\Select\Result\Result $search_results
	 */
	private $search_results;
	
	/**
	 * SolrSearch constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		
		$this->number_of_documents = -1;
		
		require_once "solr-search/search-fields.php";
		$this->search_fields = new SearchFields($plugin);
		
	}
	
	/**
	 * get solarium instance
	 * @return \Solarium\Client
	 */
	private function client(){
		return Solarium::instance($this->plugin);
	}
	
	/**
	 * Runs the search.
	 *
	 * @param array|null $search_args
	 *
	 * @return array|\Solarium\QueryType\Select\Result\Result
	 */
	public function execute(array $search_args = null) {
		
		/**
		 * no search args return empty result
		 */
		if (empty($search_args)) {
			return array();
		}
		
		$config = $this->plugin->config->get_solr_config();
		
		/**
		 * create new select query
		 * @var \Solarium\QueryType\Select\Query\Query $select
		 */
		$select = $this->client()->createSelect();
		
		/**
		 * build select with filters
		 */
		$select = apply_filters(Plugin::FILTER_SOLR_SELECT, $select, $search_args, $config );
		
		/**
		 * set result fields
		 */
		$select->setFields($config['result_fields']);
		
		$this->search_results = $this->client()->select($select);
		
		return $this->search_results;
	}
	
	
}