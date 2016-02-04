(function($){
	$(function(){
		$("#solr-delete").click(function(e){
			if(!confirm("All documents in Solr will be deleted"+
			" and all contents unmarked."+
			" This cannot be undone."+
			" Go on?")){
				e.preventDefault();
			}
		})
	})
})(jQuery);