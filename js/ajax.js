(function($, Solr){
	
	Solr.suggest = function(s){
		
		return $.ajax({
			url: Solr.endpoints.suggests+s,
			method: "GET",
		});
	}
	
	
})(jQuery, Solr);