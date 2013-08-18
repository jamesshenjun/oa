(function($){
	
	$.fn.wallpaper = function(parameter){
		
		
		
//以下部分是使用的$.extend合并参数的方法，如果设置了参数就把设置的参数与默认的参数进行附加，如果没有设置参数，全部参数就只有原来设定的参数
		parameter = $.extend({
			                picture_count:4,
							big_picture_width  :480,
							big_picture_height :300,
							small_picture_width:120,
							small_picture_height:90,
							change_interval:7000,
							change_cost:500
							}, parameter || {});
/*
* parameter || {} 这种写法就非常简洁，如果传进来的参数不为空的话，就进行附加操作，
* 如果传进来的参数为空的话，那么就把一个空集附加到默认的参数
* 这是一种使用逻辑操作符的代替一系列if语句的判断的方式
*      
*/       
		init(parameter);
		
		var WallpaperPointer = $(this);
		
		/*把大图片总共的宽度计算出来*/
		var big_picture_total_width = parseInt(parameter['picture_count']*parameter['big_picture_width']+100)+"px";
		WallpaperPointer.find(".big_picture_display_container").css("width",big_picture_total_width);
		
		/*把小图片的最后一个margin-right的数值给取消*/
		WallpaperPointer.find(".small_picture_each_container:last").css("margin-right","0px");
/*
 * 这是针对与大图上的上一张图片的绑定按钮
*/
		WallpaperPointer.find(".big_picture_prev_button").live('click',function(){
			
			var last_big_picture_pointer  = $(".big_picture_display_container").find("img:last");
			//首先找到大图列表中的最后一张图
			
			last_big_picture_pointer.css("width","0px");
			//为了保证动画效果，就先把它的宽度设置为0,在队列末尾设置缩小在队列头打开
			var source = last_big_picture_pointer.attr("source");//得到source属性
			last_big_picture_pointer.attr("src",source);//改变src属性，实现惰性加载
			
			last_big_picture_pointer.remove().prependTo(".big_picture_display_container");
			//从队尾移除，加到队首
			
			var first_big_picture_pointer = $(".big_picture_display_container").find("img:first");
			first_big_picture_pointer.animate({width:parameter['big_picture_width']},parameter['change_cost']);
			
			
		});
		
		WallpaperPointer.find(".big_picture_display_container").find("img").live('click',function(){
			
			  var url = $(this).attr("url");
			  window.open(url);
			
		});
		
		
/*
* 这是针对与大图上的下一张图片的绑定按钮
*/		WallpaperPointer.find(".big_picture_next_button").live('click',function(){
	       
	       var second_big_picture_pointer = $(".big_picture_display_container").find("img:eq(1)");
	       //当前显示的图片是第一张图片，移动是把第二张图片移动到第一张的位置
	       var source = second_big_picture_pointer.attr("source");//得到source属性
	       second_big_picture_pointer.attr("src",source);//改变src属性，实现惰性加载
	       
	       second_big_picture_pointer.css("width",parameter['big_picture_width']+"px");
	       
	       var first_big_picture_pointer = $(".big_picture_display_container").find("img:first");
	       
	       first_big_picture_pointer.animate({width:"0px"},parameter['change_cost'],function(){
	    	   
	    	   first_big_picture_pointer.remove().appendTo(".big_picture_display_container");
	    	   
	       });
	      
	       var last_big_picture_pointer  = $(".big_picture_display_container").find("img:last");
	       last_big_picture_pointer.css("width",parameter['big_picture_width']);
         
        });
		
		/*点击小图的图片，切换到大图*/
		WallpaperPointer.find(".small_picture_each_container").live('click',function(){
			
			var big_picture_path = $(this).find("img").attr("src").replace("_120x90","_535x320");
			
			var big_picture_pointer = WallpaperPointer.find(".big_picture_display_container img[source='" + big_picture_path + "']");
			big_picture_pointer.attr("src",big_picture_path);//把大图的src数值设定
			big_picture_pointer.css("width",parameter['big_picture_width']+"px");//把大图的宽度设置为撑开的宽度
			
			
			var prev_big_picture_count = parseInt(big_picture_pointer.prevAll().length - 1);
		    //先取得前面有多少个节点，prevAll取的顺序是从右向左，所以跟通常的从左到右的顺序相反
			//alert(prev_big_picture_Length);
			
			if(prev_big_picture_count>=0){
			
			 big_picture_pointer.prevAll(":lt("+prev_big_picture_count+")").css("width","0px");
			
			 $(".big_picture_display_container").find("img:eq(0)").animate({width:0},parameter['change_cost'],function(){
				var prevList = big_picture_pointer.prevAll().remove().appendTo(".big_picture_display_container");
			 });
		   }// if end
			
		});
		
		function AutoChange(pointer){
			
			pointer.find(".big_picture_next_button").trigger('click');
			
		}
		
		function init(parameter){
			
			 var fisrt_big_picture_pointer =  $(".big_picture_display_container").find("img:first");
			 var source = fisrt_big_picture_pointer.attr("source");
			 fisrt_big_picture_pointer.attr("src",source);
			 
			 
			 setInterval(function(){
				 
				 AutoChange(WallpaperPointer);
				 
			 },parameter['change_interval']); 
			 
			 
		}//function init end
		
	};//function wallpaper() end
	
	
	
	
	
	
	
	
})(jQuery);