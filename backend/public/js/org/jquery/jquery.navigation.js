(function($){

$.fn.navigation = function(parameter) {
//以下部分是使用的$.extend合并参数的方法，如果设置了参数就把设置的参数与默认的参数进行附加，如果没有设置参数，全部参数就只有原来设定的参数
	
	 var NavigationSelector = $(this);
	 //赋值给选择器
	
	 //然后在导航栏的下面的绑定事件
	 NavigationSelector.find(".MenuItem").click(function(){
		 
		 var TargetURL = $(this).attr('url');
		 
		 if($(this).hasClass('Auth')){
			 
			 var AuthURL = NavigationSelector.attr('PostURL');
			
			 AjaxGetJsonData('',AuthURL,function(msg){
				 
				 var status = msg.status;
				 
				 if(status==1){
					 window.location.href = TargetURL; 
					 return false; 
				 }
				 else if(status==0){
					$("#headerLoginInfo .LoginText").trigger('click');
				 }
			});
			 
		 }//如果需要验证用户的状态
		 else{
			 window.location.href = TargetURL; 
			 return false;
		 }
		 
	 });
	 
};//这是是function dialog(parameter){}结尾的花括号

})(jQuery);