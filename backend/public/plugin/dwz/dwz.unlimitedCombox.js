/*专门针对于无限分类的组合下拉框，初始化的时候只有一个下拉框，然后根据前面一个下拉框的孩子节点的数量进行相应的操作*/
(function($){
	
		$.fn.unlimitedCombox = function(){
		    	
			InitUnlimitedCombox($(this));
			
				
			function InitUnlimitedCombox(jUnlimitedCombox){
				
				jUnlimitedCombox.change(function(){
					
					var UnlimitedCombox = $(this);
					
					UnlimitedCombox.nextAll('select').remove();//清除掉之后的所有下拉列表
					
					var	SelectedValue	= UnlimitedCombox.attr('value');
					
					var post_url			= UnlimitedCombox.attr('post_url');
					
					var NameValue		= UnlimitedCombox.attr('name');
					
					var PostData   = {'id':SelectedValue}
				    
					if(SelectedValue=='0'){//如果是未选中的状态，那么直接返回，不再做任何的操作
				    	return false;
				    }
					
					$.post(post_url,PostData,AppendChildCombox,"json");
					
					function AppendChildCombox(msg){
						
						var statusCode = msg.statusCode;
						var message    = msg.message;
						
						if(statusCode==200){
							var SelectString  = '<select name="'+NameValue+'" ';
							   	SelectString += ' target=unlimitedCombox';
							   	SelectString += ' post_url="'+post_url+'">';
							   	SelectString += "<option value='0'>请选择</option>";	
							
							$.each(message,function(key,value){
								   
							   		SelectString +="<option value='"+value.id+"'>";
							   		SelectString += value.name;
							   		SelectString +="</option>";
								   
							});
							SelectString +="</select>";
							
							UnlimitedCombox.after(SelectString); 
							
							var ChildUnlimitedCombox = UnlimitedCombox.next('select');
							InitUnlimitedCombox(ChildUnlimitedCombox);
							
							}//if end
						   else{
							   UnlimitedCombox.nextAll('select').remove();//清除掉之后的所有下拉框
						   }
					}//function AppendChildSelect end
					
				});//change function end
				
		};//$.fn.unlimitedCombox function end
			
	}//function unlimitedCombox() end
})(jQuery)