<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 18.01.16
 * Time: 18:10
 */

namespace SolrPlugin;


class SearchPage {

	function __construct() {
	}

	/**
	 * get id of default search page
	 * @return int
	 */
	function get_search_page_id() {
		$page = get_page_by_title('Search Results');
		return $page->ID;
	}

	/**
	 * Returns the search arguments as an associative array or FALSE if there was no
	 * search.
	 *
	 * @return array
	 */
	function get_search_args() {
		$args = array();
		if (isset($_GET['query'])) {
			$args['text'] = $_GET['query'];
		} else {
			return FALSE;
		}

		// sanitize page param
		if (isset($_GET['page_num'])) {
			$args['page'] = (int) $_GET['page_num'];

			if ($args['page'] < 1) {
				$args['page'] = 1;
			}
		} else {
			$args['page'] = 1;
		}

		$facet_args = array();
		foreach ($_GET as $key => $value) {
			if (strpos($key, 'facet-') === 0) {
				$facet_args[substr($key, 6)] = $value === 'on';
			}
		}

		$args['facets'] = $facet_args;

		return $args;
	}
}