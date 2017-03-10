<?php

namespace SolrPlugin;


class SearchSpellcheck {
	/**
	 * SearchSpellcheck constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	public function __construct(Plugin $plugin ){
		$this->plugin = $plugin;
		
		/**
		 * spell checking display
		 */
		add_action(Plugin::ACTION_SEARCH_SPELLCHECK, array($this, 'action_spellcheck'));
	}
	
	/**
	 * render spellcheck display
	 * @param $solr_search_results
	 */
	function action_spellcheck(){
		
		$solr_search_results = $this->plugin->request->get_search_results();
		$solr_search_args = $this->plugin->request->get_search_args();
		$e = $this->plugin->request->search_error_object;
		
		include $this->plugin->render->get_template_path(Plugin::TEMPLATE_SPELLCHECK);
	}
}