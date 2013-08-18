function paginatorEnv() {
	
	// navTab
	$("a[target=paginator]").each(function(){
		
		$(this).click(function(event){
			event.preventDefault();
			var $this = $(this);
			var data = $this.attr("data") || '';
			var url = $this.attr("href") || "#";
			ajaxCall(data, url);

		});
	});
	
	function ajaxCall(data, url)
	{
		
		$.ajax({
					type:"POST",
					url :url,
					data:data,
				    success:function( res ){
				    	
				    	$(".content-right").html(res);
				    	
		                }
		});
		
	}
	
	
}


function searchEnv(){
	
	// navTab
		
	$("input[type=submit]").click(function(event){
			event.preventDefault();
			var keywords = $("input[name=keywords]").val() || '';
			var url = $("form[id=search]").attr('action');
			ajaxCall2(keywords, url);

		});

	
	function ajaxCall2(keywords, url)
	{
		
		$.ajax({
					type:"POST",
					url :url,
					data:'keywords='+keywords,
				    success:function( res ){
				    	
				    	$(".content-right").html(res);
				    	
		                }
		});
		
	}
	
	
}