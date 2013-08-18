(function($){

$.fn.StrectchMenu = function(parameter) {

	
	var MenuSelector = $(this);
	
	
	//以下部分是使用的$.extend合并参数的方法，如果设置了参数就把设置的参数与默认的参数进行附加，如果没有设置参数，全部参数就只有原来设定的参数
	parameter = $.extend({
						top     : '100',//初始化一个top的数值
						left    : '0',  //初始化一个left的数值
						width   : '800',//初始化一个width的数值
						height  : '640',//初始化一个height的数值
						src		:  ''
						}, parameter || {});
/*
 * parameter || {} 这种写法就非常简洁，如果传进来的参数不为空的话，就进行附加操作，
 * 如果传进来的参数为空的话，那么就把一个空集附加到默认的参数
 * 这是一种使用逻辑操作符的代替一系列if语句的判断的方式
 * 
*/
	
	MenuSelector.find(".AccordionHeader").click(function(){
		
		var AccordionHeaderSelector = $(this);
		//当前点击的头部
		
		
		if(AccordionHeaderSelector.hasClass("compressed")){
			
			AccordionHeaderSelector.siblings(".AccordionHeader").each(function(){
				
				$(this).removeClass("expanded");//移除扩展的样式
				$(this).addClass("compressed");//添加收缩的样式
				
				var AccordionContentSelector = $(this).next(".AccordionContent");
				
				var AttrDisplay = AccordionContentSelector.css("display");
				
				if(AttrDisplay=='block'){
					$(this).next(".AccordionContent").slideToggle(250);
				}
				
			});
			
			AccordionHeaderSelector.addClass("expanded");//添加伸展的样式
			AccordionHeaderSelector.removeClass("compressed");//移除压缩的样式
			AccordionHeaderSelector.next(".AccordionContent").slideToggle(250);
			
		}
		
	});
	
	
    MenuSelector.find(".AccordionContent ul li a").click(function(){
		
    	var SeekTime = $(this).attr("data");
    	
    	var player = document.getElementById("RecordVideo").PlayToSeek(SeekTime);
    	
    });
	
    
    
};//这是是function dialog(parameter){}结尾的花括号

})(jQuery);