<?php

namespace SolrPlugin;


class SearchFieldsSpellchecker {
	/**
	 * SearchFieldsSpellchecker constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		add_filter('solr_search_select',array($this,'search_select_spellchecker'),10,3);
	}
	
	/**
	 * search spellchecker
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_spellchecker($select,$search_args, $config){
		if ( !empty($search_args['s']) &&
		     !empty($config['spellcheck']) &&
		     $config['spellcheck'] ) {
			$spellcheck = $select->getSpellcheck();
			$spellcheck->setQuery($search_args['s']);
			$spellcheck->setCount(10);
			// TODO: build spellcheck index elsewehere. makes search sooooo slow!
			$spellcheck->setBuild(FALSE);
			$spellcheck->setCollate(TRUE);
			$spellcheck->setExtendedResults(TRUE);
			$spellcheck->setCollateExtendedResults(TRUE);
		}
		return $select;
	}
}