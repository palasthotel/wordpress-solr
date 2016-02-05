(function($){
	$(function(){
		$("#solr-delete").click(function(e){
			var go_on = "yes";
			var text = "All documents in Solr will be deleted" +
				" and all contents unmarked." +
				" This cannot be undone." +
				" Go on with \""+go_on+"\" ";

			if (go_on != !prompt(text))
			{
				e.preventDefault();
			}
		})
	})
})(jQuery);