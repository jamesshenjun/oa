(function($){
	
	  $.fn.extend({
		    
		   Report:function(){
			   
			   var ReportSelector = $(this);
			  
			   var type = ReportSelector.attr("type");
			   
			   if(type=='Preview'){
				   InitReportTable();
			   }
			   else if(type=='AddReport'){
				   InitReportTable();
//				   BindReportTableEditEvent();
				   BindReportTableSubmitEvent();
			   }
			   else if(type=='EditReport'){
				   InitReportTable();
//				   BindReportTableEditEvent();
				   BindReportTableSubmitEvent();
			   }
			   else if(type == 'EditTempalte'){
				   InitReportTable();
				   BindTemplateTableEditEvent();
				   BindTemplateTableSubmitEvent();
			   }
			   
			   function BindTemplateTableEditEvent(){
				  
				   
				   ReportSelector.find("tbody td div").die().live('click',function(){
					  
					   var DivPointer = $(this);
					   
					   var TdPointer = DivPointer.parent("td");
					   
					   var content   = DivPointer.text();
					   
					   var TextareaString = "<textarea class='EditContent'";
					       TextareaString+= " style='width:90%;resize:none'>";
					       TextareaString+= content;
					       TextareaString+= "</textarea>";
					   
					   ReportSelector.find('td').each(function(){
						  
						   var DivPointer = $(this);
						   
						   var TdPointer = DivPointer.parent("td");
						   
						   var TextareaPointer = $(this).find("textarea");
						   
						   var length    = TextareaPointer.length;
						   
						   if(length==0){
							   return true;
						   }
						   else if(length==1){
							   var content = TextareaPointer.attr('value');
							   var div = "<div>"+content+"</div>";
							   TdPointer.html(div);
						   }
						
						});
					       
					   TdPointer.html(TextareaString);
					   
					   TdPointer.find('textarea').focus();
				   });
				   
				   ReportSelector.find('td textarea').live('blur',function(e){
					  
                       var TextareaPointer = $(this);
					   
					   var TdPointer = TextareaPointer.parent("td");
					   
					   var content   = TextareaPointer.attr('value');
					   
					   var aaa ='';
					   
					   if(content.lenght <1)
					   {
						   aaa = "<div>&nbsp;&nbsp;</div>";
					   }
					   else
					   {
						   aaa = "<div>"+content+"</div>";
					   }
					   
					   TdPointer.html(aaa);
					
				   });
				   
				   
			   };//function EditReportTable() end
			   
               function BindReportTableSubmitEvent(){
				   
				   ReportSelector.find("tfoot").find(".ReportFormSubmitButton").click(function(){
					  
//					   var ReportId  = ReportSelector.attr("ReportId");
					   
					   var PostData = new Object();
//					   PostData['info']    = {"ReportId":ReportId};
					   PostData['content'] = {};
					   var title = ReportSelector.find("thead").find("td span[name=title]").text();
					   
					  
					   
					   var ReportId = ReportSelector.attr("ReportId");
					   var row_count = ReportSelector.attr("row_count");
					   var column_count = ReportSelector.attr("column_count");
					   
					   PostData['title'] = title;
					   PostData['ReportId'] = ReportId;
					   PostData['column_count'] = column_count;
					   PostData['row_count'] = row_count;
					   
					   ReportSelector.find("tbody td").each(function(key,value){
						  
						   var TdPointer =  $(this);
						   var content;
						   var address   =  TdPointer.attr('address');
						   var rowspan   =  TdPointer.attr('rowspan');
						   var colspan   =  TdPointer.attr('colspan');
						   var type      =  TdPointer.attr('type');
						   var sort      =  TdPointer.attr('sort');
						   var width     =  TdPointer.attr('width');
						   var height    =  TdPointer.attr('height');
						   var enedit    =  TdPointer.attr('enedit');
						   
						   if(TdPointer.find("textarea").length >0)
						  {
							   content = TdPointer.find("textarea").attr('value');
							   
						  }else if(TdPointer.find("select").val()){
							  
							   content =  TdPointer.find("select").val();
						  }else{
						   	   content =  TdPointer.text(); 
						  }
						   
						   PostData['content'][key] = {
//								                       'tid':ReportId,
								                       'address':address,
								                       'sort':sort,
								                       'rowspan':rowspan,
								                       'colspan':colspan,
								                       'content':$.trim(content),
								                       'type':type,
								                       'width':width,
								                       'height':height,
								                       'enedit':enedit
						                              };
						   
					   });
					  
					   var PostURL = ReportSelector.attr("PostURL");
					   
					    $.ajax({
				    		type: 'POST',    //统一定为POST方法
				    		url : PostURL,   //上传地址
				    		data: PostData,  //上传的查询参数
				    		dataType:"json", //数据返回的形式是json
				    		cache: false,    //关闭缓存
				    		success: function(res){
				    			switch(res.statusCode){
				    			case '200':{alertMsg.correct(res.message); navTab.closeCurrentTab();break;}
				    			case '300':{alertMsg.error(res.message); break;}
				    			}
				    		}
				    	});
					   
					   
				   });
				   
			   };
			   function BindTemplateTableSubmitEvent(){
				   
				   ReportSelector.find("tfoot").find(".ReportFormSubmitButton").click(function(){
					  
					   
					   var PostData = new Object();
//					   PostData['info']    = {"ReportId":ReportId};
					   PostData['content'] = {};
					   var TemplateId = ReportSelector.attr("TemplateId");

					   
					   PostData['TemplateId'] = TemplateId;

					   
					   ReportSelector.find("tbody td").each(function(key,value){
						  
						   var TdPointer =  $(this);
						   var address   =  TdPointer.attr('address');
						   var rowspan   =  TdPointer.attr('rowspan');
						   var colspan   =  TdPointer.attr('colspan');
						   var type      =  TdPointer.attr('type');
						   var sort      =  TdPointer.attr('sort');
						   var width     =  TdPointer.attr('width');
						   var height    =  TdPointer.attr('height');
						   var content   =  TdPointer.find('div').text();
						   
						   PostData['content'][key] = {
								                       'address':address,
								                       'sort':sort,
								                       'rowspan':rowspan,
								                       'colspan':colspan,
								                       'content':$.trim(content),
								                       'type':type,
								                       'width':width,
								                       'height':height
						                              };
						   
					   });
					  
					   var PostURL = ReportSelector.attr("PostURL");
					   
					    $.ajax({
				    		type: 'POST',    //统一定为POST方法
				    		url : PostURL,   //上传地址
				    		data: PostData,  //上传的查询参数
				    		dataType:"json", //数据返回的形式是json
				    		cache: false,    //关闭缓存
				    		success: function(res){
				    			switch(res.statusCode){
				    			case '300':{alertMsg.error(res.message); break;}
				    			case '200':{alertMsg.correct(res.message); navTab.closeCurrentTab();break;}
				    			}
				    		}
				    	});
					   
					   
				   });
				   
			   };			   
			   
			   
			   function InitReportTable(){
		       //初始化 报告预览表格的方法，向表格追加一些样式，当然这部分的工作也可以放给css去做
				   
				   
			     //1.初始化表格的样式
				 ReportSelector.css(
					                         {
					                          'font-size':'16px',
				                              'margin':'10px auto',
				                              'border-top':'1px solid #d0d0d0',
				                              'border-left':'1px solid #d0d0d0'
			                                 }
					                       );
				 
				 ReportSelector.find("td").css({
					 						  'font-size':'16px',
					                          'border-bottom':'1px solid #d0d0d0',
					                          'border-right':'1px solid #d0d0d0',
					                          'text-align':'left',
					                      	  'padding':'3px'
					                          });
				 ReportSelector.find("textarea").css({
					 'font-size':'16px',
                     'border-bottom':'1px solid #d0d0d0',
                     'border-right':'1px solid #d0d0d0',
                     'text-align':'left'
                     });
				 
				 
		       };//function InitReportPreviewTable end
		    
		   }//function Report() end
	  
	  
	 });//$.fn.extend  function end
	
	
	
})(jQuery)