function ajaxGetJsonData(PostData,PostURL,CallBack,dataType,ErrorMsg){
    	
    	$.ajax({
    		type:'POST',
    		async:false,
    		url:PostURL,
    		data:PostData,
    		dataType:dataType||"json",
    		cache:false,
    		success:CallBack,
    		error:ErrorMsg||{}
    	});
    
}//function ajaxGetJsonData() end

function showErrorMessage(msg){
	alert(msg);
	
}//function showErrorMessage() end