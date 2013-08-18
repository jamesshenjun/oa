<?php

namespace Help\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\View\Model\ViewModel;
use Zend\Form\Element\Radio;




use Application\Controller\BaseController;
use Application\Factory\ServiceLocatorFactory;
use Application\Filter\HelpArticleFilter;//引入文章过滤器
use Application\Filter\HelpArticleSortFilter;

use Help\Form\ArticleListForm;//引入文章列表的表单
use Help\Form\ArticleForm;//引入文章的表单
use Help\Model\ArticleInfo;//引入文章信息操作的模型
use  Zend\Paginator\Paginator;

class ArticleController extends BaseController
{
    protected $articleInfoModel;
    protected $articleContentModel;
    protected $articleSortModel;
    protected $userModel;

	public function __construct(){
		
		$serviceManager = ServiceLocatorFactory::getInstance();
		$this->getDbModel ( $serviceManager, 'Help', 'Model', 'ArticleInfoModel' );
		$this->getDbModel ( $serviceManager, 'Help', 'Model', 'ArticleContentModel' );
		$this->getDbModel ( $serviceManager, 'Help', 'Model', 'ArticleSortModel' );
		$this->getDbModel ( $serviceManager, 'Help', 'Model', 'UserModel' );
	}//function __construct() end
	
	
	/**
	 * 初始化articleInfo数据表
	 */
	
	public function initAction(){
		
	     for($i=0;$i<10;$i++){
	     	
	     	$articleInfo['id']    = '';
	     	
	     	$articleInfo['user_id'] = rand(0,40);
	     	
	     	$articleInfo['article_sort_id'] = rand(13, 21);
	     	
	     	$articleInfo['title'] = '测试文章的测试标题'.$i;
	 
	     	$articleInfo['sub_title'] = '测试文章的副测试标题'.$i;
	     	
	     	$articleInfo['keyword'] = "关键字".rand(0,1000).","."关键字".rand(0,1000);
	     	
	     	$articleInfo['content'] = "文章内容".rand(0,1000);
	     	
	     	$articleInfo['add_time'] = date("Y-m-d H:i:s",time()-rand(0,1000)*3600);
	     	
	     	$articleInfo['update_time'] = date("Y-m-d H:i:s",time()-rand(0,1000)*3600);
	     	
	     	$articleInfo['hits'] = rand(0,30000);
	     	
	     	$articleInfo['status'] = 'Y';
	     	
	     	$this->articleInfoModel->insert($articleInfo);
	     	
	     	$articleContent['id'] = $this->articleInfoModel->lastInsertValue;
	     	
	     	$articleContent['content'] = $articleInfo['content'];
	     	
	     	$this->articleContentModel->insert($articleContent);
	     	
	     	
	     	
	     }
		
	     
	     die();
	}
	
	
	
	/**
	 * 显示文章列表
	 * @return \Zend\View\Model\ViewModel
	 */
	
	public function showArticleListAction()
	{
		$request = $this->getRequest ();
		
		if (!$request->isXmlHttpRequest()) {
			$this->returnMessage('300', '请勿非法操作');
		}
		
		//收集搜索条件
		$articleID = $request->getPost ('id', false);
		$keyword  = $request->getPost( 'keyword', false );
		$author    = $request->getPost('author', false);
		$article_sort_id = $request->getPost('article_sort_id', 0);
		$article_sort_value = $article_sort_id;
		
		if ($article_sort_id){
			
			$sort_id = $this->articleSortModel->getChildrenByArticleSortId($article_sort_id);
			
			if (sizeof($sort_id) > 0 ){
				
				$article_sort_id = array();
					
				foreach ($sort_id as $key=>$sort){
				
					$article_sort_id[$key] = $sort['id'];
				}
			}
			

			
		}
		$queryData =array(
				'id' =>$articleID,
				'keyword'=>$keyword,
				'author'=>$author,
				'article_sort_id'=>$article_sort_id
				);
		
		
		//翻页要用的参数收集
		$numPerPage = (int)$request->getPost ('numPerPage', 20);
		$pageNum    = (int)$request->getPost ('pageNum', 1);
		
		
		$paginatorAdapter  = $this->articleInfoModel->getArticleInfoList($queryData);
		
		$paginator = new Paginator();
		
		//翻页参数初始化
		$paginator->setItemCountPerPage($numPerPage);
		$paginator->setCurrentPageNumber($pageNum);
	
		
		foreach ( $paginator as $key=>$articleInfo ){
			
			$articleInfoList[$key] = $articleInfo; 
		}
		
		$articleInfoList = $this->formatArticleInfoList($articleInfoList);
		
		//查询分类选项
		$articleSortList = $this->articleSortModel->getArticleSortList();
		$article_sort_select = $this->formatArticleSortListForselect($articleSortList);
		
		$articleListForm = new ArticleListForm();
		$articleListForm->get('article_sort_id')->setValueOptions($article_sort_select);
		
		//带回值
		$articleListForm->get('numPerPage')->setValue($numPerPage);
		$articleListForm->get('pageNum')->setValue($pageNum);
		$articleListForm->get('id')->setValue($articleID);
		$articleListForm->get('keyword')->setValue($keyword);
		$articleListForm->get('author')->setValue($author);
		$articleListForm->get('article_sort_id')->setValue($article_sort_value);
		
		$viewModel = new ViewModel();
		
		$viewModel->setVariable('articleListForm', $articleListForm);
		$viewModel->setVariable('paginator', $paginator);
		$viewModel->setVariable('articleInfoList', $articleInfoList);
        
        return $viewModel;
	}
	
	
	
	/**
	 * 显示在文章添加和文章编辑界面的查找带回中显示
	 * @return \Zend\View\Model\ViewModel
	 */
	
	public function showArticleSortListAction(){
		
		//第一部分：对收集上来的数据进行过滤
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			//die('请不要尝试非法操作');
		}
		
		$queryData = $request->getQuery();
		
		if(isset($queryData['id'])){
				
			$helpArticleSortFilterObject = new HelpArticleSortFilter();
				
			$helpArticleSortFilter = $helpArticleSortFilterObject->getInputFilter();
				
			$helpArticleSortFilter->setData($queryData);
				
			$helpArticleSortFilter->setValidationGroup(array('id'));
				
			if(!$helpArticleSortFilter->isValid()){
					
				$errorMessages = $helpArticleSortFilter->getMessages();
					
				foreach($errorMessages as $errorMessage){
					$this->returnMessage('300', array_pop($errorMessage));
				}
					
			}
		
		}//if end
		
		$articleSortId = $queryData['id'];
		//根据这个接受的文章id查询子孙分类的列表
		
		$articleSortList = $this->articleSortModel->getArticleSortList();
		
		$articleSortList = $this->formatArticleSortList($articleSortList);
		
		//print_r($articleSortList);
		
		$viewModel = new viewModel();
		
		$viewModel->setVariable('articleSortList', $articleSortList);
		
		
		return  $viewModel;
	}//function showArticleSortListAction() end
	
	
	
	/**
	 * 显示文章添加界面
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showArticleAddAction()
	{

		$articleAddForm =  new ArticleForm();
		
		//这是对于文章内容的一些选项的设定：上传的接口与一些限制
		//这部分的内容是因为在form表单中调不出来形成路由的url对象
		$content = $articleAddForm->get('content');
		
		//设置附件的上传路径与后缀名限制
		$attachmentUploadUrl = $this->url()->fromRoute('application',array('controller'=>'upload','action'=>'attachment'));
		
		$articleAddForm->get('content')->setAttribute('upLinkUrl',$attachmentUploadUrl);
		
		$articleAddForm->get('content')->setAttribute('upLinkExt',"zip,rar,txt");
		
		
		//设置图片的上传路径与后缀名限制
		$imageUploadUrl = $this->url()->fromRoute('application',array('controller'=>'upload','action'=>'image'));
		
		$articleAddForm->get('content')->setAttribute('upImgUrl',$imageUploadUrl);
		
		$articleAddForm->get('content')->setAttribute('upImgExt',"jpg,jpeg,gif,png");
		
		
		$viewModel = new ViewModel();
		
		$viewModel->setVariable('articleAddForm', $articleAddForm);
		
        return  $viewModel;
	}
	
	/**
	 * 显示文章编辑的页面
	 * @return \Zend\View\Model\ViewModel
	 */
	
	public function showArticleEditAction(){
		
		$request = $this->getRequest();
		//得到request对象
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法的访问方式');
		}
		
		$queryData = $request->getQuery();
		
		//第一步：得到文章过滤器
		$helpArticleFilterObject = new HelpArticleFilter();
		 
		$helpArticleFilter = $helpArticleFilterObject->getInputFilter();

		$helpArticleFilter->setValidationGroup(array('id'));
		
		$helpArticleFilter->setData($queryData);
		 
		//第二步：判断过滤是否有效
		if(!$helpArticleFilter->isValid()){
		
			$errorMessages = $helpArticleFilter->getMessages();
		
			foreach($errorMessages as $message){
				$this->returnMessage(300, array_pop($message));
			}
		
		}
		
		
		//第三步：得到经过了过滤和验证的数据
		$articleInfoPostData = $helpArticleFilter->getValues();
		
		$articleInfoId = $articleInfoPostData['id'];
		
		$articleInfo = $this->articleInfoModel->getRowById($articleInfoId);
		//得到文章信息的数据
		
		$acticleContent = $this->articleContentModel->getRowById($articleInfoId);
		//得到文章内容的数据
		
		//print_r($articleInfo);
		
		//第四部：对于文章编辑中信息进行填充
		$articleEditForm =  new ArticleForm();
		
		//设置文章的id
		$articleEditForm->get('id')->setValue($articleInfo['id']);
		
		//设置文章的标题
		$articleEditForm->get('title')->setValue($articleInfo['title']);
		
		//设置文章的副标题
		$articleEditForm->get('sub_title')->setValue($articleInfo['sub_title']);
		
		//设置文章的分类
		$articleEditForm->get('article.article_sort_id')->setValue($articleInfo['article_sort_id']);
		
		$articleSort = $this->articleSortModel->getRowById($articleInfo['article_sort_id'],array('name'));
		
		$articleEditForm->get('article.article_sort_name')->setValue($articleSort['name']);
		
		//设置文章的关键词
		$articleEditForm->get('keyword')->setValue($articleInfo['keyword']);
		
		//设置文章内容，这个要显示没有被压缩过的内容，从文章内容表取值
		$articleEditForm->get('content')->setValue($acticleContent['content']);
		
		//设置附件的上传路径与后缀名限制
		$attachmentUploadUrl = $this->url()->fromRoute('application',array('controller'=>'upload','action'=>'attachment'));
		
		$articleEditForm->get('content')->setAttribute('upLinkUrl',$attachmentUploadUrl);
		
		$articleEditForm->get('content')->setAttribute('upLinkExt',"zip,rar,txt");
		
		
		//设置图片的上传路径与后缀名限制
		$imageUploadUrl = $this->url()->fromRoute('application',array('controller'=>'upload','action'=>'image'));
		
		$articleEditForm->get('content')->setAttribute('upImgUrl',$imageUploadUrl);
		
		$articleEditForm->get('content')->setAttribute('upImgExt',"jpg,jpeg,gif,png");
		
	
		$viewModel = new ViewModel();
		
		$viewModel->setVariable('articleEditForm', $articleEditForm);
		
		return  $viewModel;
		
	}//function showArticleEditAction() end
	
	
	
	/**
	 * 添加文章时的提交检测，并保存数据库
	 */
	public function checkArticleAddAction(){
	
	    $request = $this->getRequest();
	    //得到request对象
	
 	    if(!$request->isXmlHttpRequest()){
 	    	die('请不要尝试非法的访问方式');
	    }
	    	
	    $postData = $request->getPost();
	    //添加文章提交的数据
	    
	    //对于数据进行键值的转换
	    $postData['article_sort_id'] = $postData['article_article_sort_id'];
	    unset($postData['article_article_sort_id']);
	    
	    
	    //得到文章过滤器
	    $articleFilterObject = new HelpArticleFilter();
	    
	    $articleFilter = $articleFilterObject->getInputFilter();
	    
	    $articleFilter->setData($postData);
	    $articleFilter->setValidationGroup(array('title','sub_title','keyword','content','article_sort_id'));
	    
	    //判断过滤器是否有效
	    if(!$articleFilter->isValid()){
	    	
	       $errorMessages = $articleFilter->getMessages();
	       
	       foreach($errorMessages as $message){
	       	  $this->returnMessage(300, array_pop($message));
	       }
	       
	    }
	    //如果数据经过了过滤和验证的话，那么就对于数据进行二次处理
	    
	    $userInfo = $this->getLoginUser();
	    
	    //组织插入文章信息数据表的数据
	    $articleInfo = $articleFilter->getValues();
	    $articleInfo['id'] 			= '';
	    $articleInfo['user_id'] 	= $userInfo->id;
	    $articleInfo['hits']   		= 0;
	    $articleInfo['add_time']    = date("Y-m-d H:i:s");
	    $articleInfo['update_time'] = date("Y-m-d H:i:s");
	    $articleInfo['status']   	= 'Y';
	    
	    
	    // 此处开启数据事务
	    $dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
	    $dbConnection->beginTransaction();
	    
	    try{
	    	
	    	$this->articleInfoModel->articleInfoAdd ( $articleInfo );
	    	
	    	$articleInfoId = $this->articleInfoModel->lastInsertValue;
	    	//得到插入之后的文章信息表的id
	    
	    	//组织插入到文章内容表的数据
	    	$articleContent = array();
	    	$articleContent['id']      = $articleInfoId;
	    	$articleContent['content'] = $articleFilter->getRawValue('content');
	    	
	    	$this->articleContentModel->articleContentAdd($articleContent);
	    	
	    }
	    catch (\Exception $e ) {
	   
	    	$dbConnection->rollback ();
	    	$this->returnMessage ( 300, $e->getMessage () );
	    }
	    
	    
	    $dbConnection->commit ();
	    $this->returnMessage ('200','恭喜您，发表文章成功');
	    
		
	}//function checkArticleAdd() end
	
	/**
	 * 编辑文章时的提交检测，并保存数据库
	 */
	public function checkArticleEditAction(){
	
		$request = $this->getRequest();
		//得到request对象
	
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法的访问方式');
		}
	
		$postData = $request->getPost();
		//添加文章提交的数据
		 
		//对于数据进行键值的转换
		$postData['article_sort_id'] = $postData['article_article_sort_id'];
		unset($postData['article_article_sort_id']);
		 
		 
		//得到文章过滤器
		$articleFilterObject = new HelpArticleFilter();
		 
		$articleFilter = $articleFilterObject->getInputFilter();
		 
		$articleFilter->setData($postData);
		 
		//判断过滤器是否有效
		if(!$articleFilter->isValid()){
	
			$errorMessages = $articleFilter->getMessages();
	
			foreach($errorMessages as $message){
				$this->returnMessage(300, array_pop($message));
			}
	
		}
		//如果数据经过了过滤和验证的话，那么就对于数据进行二次处理
		 
		//组织更新到文章信息数据表的数据
		$articleInfo = $articleFilter->getValues();
		$articleInfo['update_time'] = date("Y-m-d H:i:s");
		
		//组织更新到文章内容数据表的数据
		$articleContent = array();
		$articleContent['content'] = $articleFilter->getRawValue('content');
		
		 
		 
		// 此处开启数据事务
		$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
		$dbConnection->beginTransaction ();
		 
		try{
	
			$this->articleInfoModel->updateRowById ($articleInfo['id'],$articleInfo);
	
			//组织插入到文章内容表的数据
			$this->articleContentModel->updateRowById($articleInfo['id'],$articleContent);
	
		}
		catch (\Exception $e ) {
	
			$dbConnection->rollback ();
			$this->returnMessage ( 300, $e->getMessage () );
		}
		 
		 
		$dbConnection->commit ();
		$this->returnMessage ('200','恭喜您，编辑文章成功');
		 
	
	}//function checkArticleAdd() end
	
	public function checkArticleDeleteAction(){
		
		$request = $this->getRequest();
		//得到request对象
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法的访问方式');
		}
		
		$id = $request->getQuery('id');
		
		
		// 此处开启数据事务
		$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
		$dbConnection->beginTransaction ();
			
		try{
			
			//删除文章信息表的数据
			$this->articleInfoModel->deleteRowById($id);
		
			//删除文章内容表的数据
			$this->articleContentModel->deleteRowById($id);
		
		}
		catch (\Exception $e ) {
		
			$dbConnection->rollback ();
			$this->returnMessage ( 300, $e->getMessage () );
		}
			
			
		$dbConnection->commit ();
		$this->returnMessage ('200','恭喜您，删除文章成功');
		
		
		
	}
	
		
	
	
	
	private function formatArticleSortList($articleSortList){
	
		if(sizeof($articleSortList)==0) return array();
	
		$articleSortIdList = array();
	
		foreach($articleSortList as $key=>$articleSort){
	
			//首先收集文章分类id的列表，用来取得每个分类下的文章数量
			array_push($articleSortIdList,$articleSort['id']);
				
			if($articleSort['status']=='Y'){
	
				$articleSortList[$key]['status'] = '已启用';
	
			}
			else if($articleSort['status']=='N'){
	
				$articleSortList[$key]['status'] = '已禁用';
	
			}
	
			$articleSortList[$key]['children_count'] = ($articleSort['right_number'] - $articleSort['left_number']-1)/2;
	
			$articleSortList[$key]['margin_left'] = 20 * $articleSort['level'];
			
			
		}//foreach end
	
		$ArticleSortArticleCount = $this->articleInfoModel->getArticleSortArticleCount($articleSortIdList);
		//得到分类id与分类下的文章数量的对应关系，根节点和中间节点的文章数量为0，下面需要进行手动的加和
	
		$TempArticleSortArticleCount = array();
		//声明一个临时变量，键值是分类的id，数值是文章总数
	
		foreach($ArticleSortArticleCount as $element){
				
			$TempArticleSortArticleCount[$element['article_sort_id']]  = $element['count'];
				
		}
	
		$ArticleSortArticleCount = $TempArticleSortArticleCount;
	
		foreach($articleSortIdList as $articleSortId){
			//循环文章分类id列表，得到每一个分类的id
				
			$articleSortChildren = $this->articleSortModel->getChildrenByArticleSortId($articleSortId);
			//得到每一个分类的子分类列表，每一个分类信息都是一个数组，包括id、左值、右值
				
			foreach($articleSortChildren as $child){
				//循环子分类列表
	
				if($child['right_number']-$child['left_number']==1){
					//只有叶子节点才有数值，才需要想祖先节点添加文章数量
					if (empty($ArticleSortArticleCount[$articleSortId])){
						$ArticleSortArticleCount[$articleSortId] = 0;
					}
					if (empty($ArticleSortArticleCount[$child['id']])){
						$ArticleSortArticleCount[$child['id']] = 0;
					}
					$ArticleSortArticleCount[$articleSortId] += $ArticleSortArticleCount[$child['id']];
				}
			}
				
		}//foreach end
	
	
		foreach($articleSortList as $key=>$articleSort){
				
			if(isset($ArticleSortArticleCount[$articleSort['id']])){
				$articleSortList[$key]['article_count'] = $ArticleSortArticleCount[$articleSort['id']];
			}
			else{
				$articleSortList[$key]['article_count'] = 0;
			}
				
		}
	
		return $articleSortList;
	}//function FormatDepartmentList() end
	
	private function formatArticleInfoList($articleInfoList){
		
		if(sizeof($articleInfoList)==0){
			return array();
		}
		
		$articleSortIdList = array();
		//收集文章分类的分类id集合
		
		$userIdList = array();
		//收集文章作者的用户id的集合
		
		foreach($articleInfoList as $key=>$articleInfo){
			
			//首先收集文章分类id的列表，用来取得每个分类下的文章数量
			array_push($articleSortIdList,$articleInfo->article_sort_id);
			
			array_push($userIdList,$articleInfo->user_id);
			
		}//foreach end
		
		
		//重置文章分类的分类名称
		$tempArticleSortInfoList = $this->articleSortModel->getRowById($articleSortIdList);
		
		$articleSortInfoList = array();
		
		foreach($tempArticleSortInfoList as $key=>$articleSortInfo){
			$articleSortInfoList[$articleSortInfo['id']] = $articleSortInfo;
		}//foreach end
		
		//重置用户的用户名称
		$tempUserInfoList = $this->userModel->getRowById($userIdList);
		
		$userInfoList = array();
		
		foreach($tempUserInfoList as $key=>$userInfo){
			$userInfoList[$userInfo['id']] = $userInfo;
		}//foreach end
		
		
		foreach($articleInfoList as $key=>$articleInfo){
			
			if(isset($userInfoList[$articleInfo['user_id']])){
				$articleInfoList[$key]['realname']  = $userInfoList[$articleInfo['user_id']]['realname'];
			}
			else{
				$articleInfoList[$key]['realname']  = '&nbsp;';
			}
			
			if (isset($articleSortInfoList[$articleInfo['article_sort_id']]['name'])){
				$articleInfoList[$key]['article_sort_name'] = $articleSortInfoList[$articleInfo['article_sort_id']]['name'];
			}else{
				$articleInfoList[$key]['article_sort_name'] = '&nbsp;';
			}
			
			
		}
		
		
		return $articleInfoList;
		
	}//function formatArticleList() end
	
	private function formatArticleSortListForselect($articleSortList)
	{
		$List[0] = '请选择';
		foreach ($articleSortList as $articleSort)
		{
			$sort = '';
			while (--$articleSort['level']){
				$sort .= "　　";
			}
			if ($articleSort['left_number'] != $articleSort['right_number'] - 1 ){
				$sort .= $articleSort ['name'];
			}else{
				$sort .= $articleSort ['name'];
			}
			
			
			$List[$articleSort['id']] = $sort;
		}
		return $List;
	}
	
	
			
   
}//class ArticleController() end
