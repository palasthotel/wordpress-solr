(function($, Solr){
	
	Solr.suggest = function(s){
		$.get(Solr.endpoints.suggests+s).then(function(data){
			console.log(data);
		});
	}
	
	
})(jQuery, Solr);