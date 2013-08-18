/*
 *作者:hengleike
 *提示:jquery插件编写新手，多多见谅，有问题的话，请直接跟我说，请您不吝指教
 *说明：代码风格非常简单，为了方便任何有jquery基础的人都能轻易看懂，注释非常多，多到比代码还多，真的很多
 *附加：写这个代码的目的就为了简单易懂，不追求编程技巧，不追求效率,不追求代码简略
*/
(function($){

$.fn.dialog = function(parameter) {
/*
 *$.fn.dialog是使用prototype对jquery的命名空间附加的方法
 *功能：这里就是简单的打开一个模态对话框的插件
 *思路：1.当绑定的对象的点击发生的时候,在当前页面中对于body使用append的方法附加撑满整个屏幕且id为dialog_mask的div用来做遮罩层
 *    2.同时再在body里面使用append的方法附加一个id为dialog_container的容器放置实际加载的页面内容，一些属性可以用外部调用的时候指定
 *    3.并且在dialog_container中添加一个iframe的src的属性值来加载页面用来加载其他的页面
 *    4.并且在dialog_container中添加一个closebutton来关闭遮罩层和对话框
*/	
	
	var DialogPointer = $(this);
	
	
	//以下部分是使用的$.extend合并参数的方法，如果设置了参数就把设置的参数与默认的参数进行附加，如果没有设置参数，全部参数就只有原来设定的参数
	parameter = $.extend({
						top     : '100',//初始化一个top的数值
						left    : '0',  //初始化一个left的数值
						width   : '800',//初始化一个width的数值
						height  : '640',//初始化一个height的数值
						auth_src: false,
						src     : false
						
						}, parameter || {});
/*
 * parameter || {} 这种写法就非常简洁，如果传进来的参数不为空的话，就进行附加操作，
 * 如果传进来的参数为空的话，那么就把一个空集附加到默认的参数
 * 这是一种使用逻辑操作符的代替一系列if语句的判断的方式
 * 
*/
	
	
	DialogPointer.live('click',function(){
    	
		var this_pointer = $(this);
		
		CheckPermission(parameter,this_pointer);
		
		//然后绑定关闭按钮的事件，点击关闭按钮之后，使用remove方法移除遮罩层    对话框
    	$("#dialog_closebutton").die().live('click',function(){
    		
    		$("#dialog_mask").hide().remove();
    		$("#dialog_container").hide().remove();
    		
    	});
    	
    });//关闭结束
	
	
    function CheckPermission(parameter,this_pointer){
    	
		if(parameter['auth_src']==false){
		//就是不需要验证的对话框直接打开
			
			OpenDialog(parameter,this_pointer);
			
		}
		else{
			
			var PostData = "";//没有任何可以提交的
			var PostURL  = parameter['auth_src'];
			AjaxGetJsonData(PostData,PostURL,function(msg){
				
				var status = msg.status;
				var info   = msg.info;
				if(status){
					OpenDialog(parameter,this_pointer);
				}
				else{
					ShowErrorMessage(info);
				}
				
				
			});
		}
	
    }
    
    
    function OpenDialog(parameter,this_pointer){
		
		//定义对话框的遮罩层的初始化的样式，就是当点击的时候往页面中插入一个遮罩层
    	var mask_style_string = 'position:absolute;'//绝对定位
    	                      + 'background-color:#000000;'//背景颜色#e3e3e3
    	                      + 'width:100%;'//指定宽度
    	                      + 'height:'+ $("body").height()+"px;"//如果指定高度为100%，那么只会遮罩住第一屏
    	                      + 'left:0px;'//遮罩层左边距
    	                      + 'top:0px;'//遮罩层右边距
    	                      + 'Z-index:99;'//定义绝对定位的叠放次序
    	                      + 'display:block;'//初始化为可见
    	                      + 'opacity:0.5;'//透明效果相关，兼容不同浏览器
    	                      + 'filter: alpha(opacity=50);'//透明效果相关，兼容不同浏览器
    	                      + '-moz-opacity: 0.5';//透明效果相关，兼容不同浏览器
    	 
    	//组合字符串,把样式和结构组合在一起
    	var mask_string = "<div id='dialog_mask' style='"+mask_style_string+"'></div>";
    	
    	//向body中添加遮罩层
    	$("body").prepend(mask_string);//往页面中的body标签添加遮罩层
    	
    	
    	var ScrollTop = parseInt($(document).scrollTop()+150);
    	//alert(ScrollTop);
    	
    	//定义对话框的里面实际显示内容的样式
    	var dialog_style_left = parseInt(($("body").width()-parameter['width'])/2) + "px";
    	var dialog_style_string = 'position:absolute;'//绝对定位
                                + 'width:' +parameter['width']+"px;"//对话框的宽度
                                + 'height:'+parameter['height']+"px;"//对话框的高度
                                + 'left:'  +dialog_style_left+";"//iframe左边距
      	                        + 'top:'+ScrollTop+"px;"//iframe上边距
      	                        + 'display:block;'//初始化为可见
    	                        + 'Z-index:1000;';//定义绝对定位的叠放次序
    	
    	//定义关闭对话框的按钮的样式
    	var dialog_closebutton_style = 'position:absolute;'//绝对定位
    		                         + 'background:url(/teach/Frontend/Public/theme/common/image/DialogCloseButtonBg.png);'
    		                         + 'width:28px;'
    		                         + 'height:28px;'
    		                         + 'top:5px;'//上边距
    		                         + 'right:5px;'//项目中的关闭按钮是靠右边的，如果将来靠左边了就改成left，总不可能居中吧
    		                         + 'display:block;'//初始化为可见
    		                         + 'color:#ff0000;'//初期为了醒目的问题
    		                         + 'cursor:pointer;';//鼠标移动上去的效果
    	
    	//组合实际上添加到页面的对话框的字符串，对话框dialog、关闭按钮closebutton、内联框架iframe
    	//然后定义一个iframe的样式，本次项目的都是采用iframe的方式引入其他的页面
    	
    	//alert(this_pointer.attr("dialog_src"));
    	
    	if(parameter['src']==''){
    		var iframe_src = this_pointer.attr("dialog_src");
    	}
    	else{
    		var iframe_src = parameter['src'];
    	}
    	
    	
    	
    	var dialog_string = "<div id='dialog_container' style='"+dialog_style_string+"'>"
    	                  + "<span id='dialog_closebutton' style='"+dialog_closebutton_style+"' >&nbsp;</span>"
    	                  + "<iframe frameborder='0' scrolling='no' width='100%' height='100%' src='"+iframe_src+"'></iframe>"
    	                  + "</div>";
    	
    	$("body").prepend(dialog_string);//在页面中添加对话框
		
		
	}//function OpenDialog() end
	
	
	
    
};//这是是function dialog(parameter){}结尾的花括号

})(jQuery);