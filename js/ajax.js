"use strict";

(function($, Solr){
	
	/**
	 * suggest search words
	 * @param s
	 */
	Solr.suggest = function(s){
		return $.ajax({
			url: Solr.endpoints.suggest+s,
			method: "GET",
		});
	};
	
	/**
	 * search for contents
	 * @param s
	 */
	Solr.search = function(s){
		return $.ajax({
			url: Solr.endpoints.search+s,
			method: "GET",
		});
	}
	
	
})(jQuery, Solr);