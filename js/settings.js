(function($){
	$(function(){
		$("#solr-delete").click(function(e){
			var go_on = "yes";
			var text = "All documents in Solr will be deleted" +
				" and all contents unmarked." +
				" This cannot be undone." +
				" Go on with \""+go_on+"\" ";
			var result =  prompt(text);
			console.log(result);
			if (go_on != result)
			{
				e.preventDefault();
			}
		})
	})
})(jQuery);