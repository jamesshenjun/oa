<?php
namespace Help\Controller;

use Application\Controller\BaseController;
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\ViewModel;

use Application\Factory\ServiceLocatorFactory;

use Help\Model\ArticleSortModel;
use Help\Form\ArticleSortListForm;
use Help\Form\ArticleSortForm;
use Application\Filter\HelpArticleSortFilter;

class ArticleSortController extends BaseController
{
	protected $articleSortModel;
	protected $articleInfoModel;
	
	public function __construct(){
	
		$serviceManager = ServiceLocatorFactory::getInstance();
	
	    $this->getDbModel($serviceManager,'Help','Model', 'ArticleSortModel');
		
	    $this->getDbModel($serviceManager,'Help','Model', 'ArticleInfoModel');
	
	
	}//function __construct() end
	
	
	public function showParentArticleSortListAction(){
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法操作');
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
		
		$articleSortChildren = $this->articleSortModel->getChildrenByArticleSortId($articleSortId);
		
		$articleSortChildrenIdList = array();
		
		foreach($articleSortChildren as $child){
			array_push($articleSortChildrenIdList,$child['id']);
		}
		array_push($articleSortChildrenIdList,$articleSortId);
		
		$articleSortList = $this->articleSortModel->getArticleSortList();
		
		$articleSortList = $this->FormatArticleSortList($articleSortList);
		
	    $viewModel = new viewModel();
		
		$viewModel->setVariable('articleSortList', $articleSortList);
		
		$viewModel->setVariable('articleSortChildrenIdList', $articleSortChildrenIdList);
		
		return  $viewModel;
		
	}
	
	
	public function showArticleSortAddAction(){
		
		$request = $this->getRequest();
		
		$articleSortAddForm = new ArticleSortForm();
		
		$viewModel = new ViewModel();

	    $viewModel->setVariable('articleSortAddForm', $articleSortAddForm);
	    
	    return $viewModel;
		
	}//function showArticleSortAddAction() end
	
	public function checkArticleSortAddAction(){
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法操作');
		}
		
		$postData = $request->getPost();
		$postData['parent_id'] = $postData['article_parent_id'];
		unset($postData['article_parent_id']);
		
		
		$helpArticleSortFilterObject = new HelpArticleSortFilter();
		$helpArticleSortFilter = $helpArticleSortFilterObject->getInputFilter();
		
		$helpArticleSortFilter->setValidationGroup(array('parent_id','name','description'));
		
		$helpArticleSortFilter->setData($postData);
		
		if(!$helpArticleSortFilter->isValid()){
			
			$errorMessages = $helpArticleSortFilter->getMessages();
			
			foreach($errorMessages as $errorMessage){
				$this->returnMessage('300', array_pop($errorMessage));
			}
			
		}
		
		$articleSort = $helpArticleSortFilter->getValues();
		//得到经过过滤和验证的分类信息
		
		//第一步：查询新添加的文章分类的父分类的文章数量，用来判断父分类是不是叶子节点分类
		$articleInfoList = $this->articleInfoModel->getArticleInfoListByArticleSortId($articleSort['parent_id']);
		
		if(sizeof($articleInfoList)){
			
			$WarningInfo  = '你所选择文章分类下还有文章存在，不能在此分类下添加子分类！<br/>';
			$WarningInfo .= '如果您一定在该分类添加子分类，请按照以下步骤操作：<br/><br/>';
			$WarningInfo .= '1.进入文章管理，选中该分类下所有文章<br/><br/>';
			$WarningInfo .= '2.将选中的文章移动到其他的文章分类下<br/><br/>';
			$WarningInfo .= '3.重新进行添加子分类操作<br/>';
			
			$this->returnMessage('300', $WarningInfo);
		}

		$dbConnection = $this->getServiceLocator()->get('Zend\Db\Adapter\Connection');
			
		$dbConnection->beginTransaction();
			
		try{
			
			//第二步：查询父分类的信息，然后根据父分类的信息得到子分类的level、left_number、right_number
			$parentArticleInfo = $this->articleSortModel->getArticleSortById($articleSort['parent_id']);
			
			$articleSort['left_number']  = $parentArticleInfo['right_number'];
			
			$articleSort['right_number'] = $articleSort['left_number'] + 1;
			
			$articleSort['level'] = $parentArticleInfo['level'] + 1;
			
			
			//第三步：为新加入的节点申请空间，挪一个位置出来，更新左值和右值
			//更新的规则，就是把左值大于新节点的左值的节点的左值都加2且就是把左值大于新节点的左值的节点的左值都加2
			$this->articleSortModel->updateLeftNumberAndRightNumber($articleSort['left_number'],'insert');
			
			//第四步：申请空间之后，根据条件插入新节点
			
			$this->articleSortModel->insertArticleSort($articleSort);
			
		}
		catch(\Exception $e ){
			
			$dbConnection->rollback();
			$this->returnMessage ('300', $e->getMessage () );
			
		}
			
		$dbConnection->commit ();
		$this->returnMessage ( '200', '恭喜您，添加文章分类成功' );
		
		
	}//function checkArticleSortAddAction() end
	
	public function checkArticleSortDeleteAction(){
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法操作');
		}
		
		$queryData = $request->getQuery();
		
		
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
		
		$articleSortId = $queryData['id'];
		
		$articleSortColumns = array('id','left_number','right_number','constant');
		
		$articleSort = $this->articleSortModel->getArticleSortById($articleSortId,$articleSortColumns);
		
		//1.固定分类不能删除------------------------//
		if($articleSort['constant']=='Y'){
			$this->returnMessage(300,'该文章分类为固定视频分类，不能删除!');
		}
		
		//2.查询该分类有没有从属的文章------------------------//
		$articleInfo = $this->articleInfoModel->getArticleInfoListByArticleSortId($articleSortId);
		
		if(sizeof($articleInfo)>0){
			
			$warningInfo  = '文章分类下还有文章存在，不能删除！<br/>';
			$warningInfo .= '如果您一定删除该分类<br/>';
			$warningInfo .=	'请按照以下步骤操作：<br/>';
			$warningInfo .= '1.进入文章管理，选中该分类下所有文章<br/><br/>';
			$warningInfo .= '2.将选中的文章移动到其他的文章分类下<br/><br/>';
			$warningInfo .= '3.返回当前文章分类列表页面进行删除操作<br/>';
			
			$this->returnMessage(300,$warningInfo);
		}
		
		//3.查询该分类下有没有子分类-------------------------//
		$articleSortChildren = $this->articleSortModel->getChildrenByArticleSortId($articleSortId);
		
		if(sizeof($articleSortChildren)>0){
			$warningInfo  = '文章分类下还有子分类存在，不能删除！<br/>';
			$warningInfo .= '如果您一定删除该分类<br/>';
			$warningInfo .= '请按照以下步骤操作：<br/>';
			$warningInfo .= '1.逐一删除该分类下所有子孙分类<br/><br/>';
			$warningInfo .= '2.如果子孙分类下存在文章，请首先移动文章到其他文章分类下<br/><br/>';
			$warningInfo .= '3.返回当前文章分类列表页面进行删除操作<br/>';
			$this->returnMessage(300,$warningInfo);
		}
		
		//----------------------------开始数据库事务操作，保证数据的完整性-------------------------//
		$dbConnection = $this->getServiceLocator()->get('Zend\Db\Adapter\Connection');
			
		$dbConnection->beginTransaction();
		
		
		try{	
			
			//4.删除该视频分类--------------------------------//
			$this->articleSortModel->deleteArticleSort($articleSortId);
			
			//5.更新左右值的数据
			$this->articleSortModel->updateLeftNumberAndRightNumber($articleSort['left_number'] ,'delete');

		}
		catch(\Exception $e ){
				
			$dbConnection->rollback();
			$this->returnMessage ('300', $e->getMessage () );
				
		}
			
		$dbConnection->commit ();
		$this->returnMessage ( '200', '恭喜您，删除文章分类成功' );
		
		
	}//function checkArticleSortDeleteAction() end
	
	public function showArticleSortEditAction(){
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法操作');
		}
		
		$queryData = $request->getQuery();
		
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
		
		$articleSortId = $queryData['id'];
		
		$articleSortAddForm = new ArticleSortForm();
		
		//根据文章分类id查询文章分类的相关信息
		$articleSortColumns = array('id','parent_id','constant','name','description');
		
		$articleSort = $this->articleSortModel->getArticleSortById($articleSortId,$articleSortColumns);
		 
		$articleSortAddForm->get('id')->setValue($articleSort['id']);
		
		$articleSortAddForm->get('name')->setValue($articleSort['name']);
		
		$articleSortAddForm->get('description')->setValue($articleSort['description']);
		
		if($articleSort['constant']=='Y'){
		//如果是根节点的话，那么就要考虑把选择父分类的地方删除掉	
		
			$articleSortAddForm->remove('article.parent_id');
			
			$articleSortAddForm->remove('article.parent_name');
			
		}
		else{
		//如果不是根节点的话，那么就要考虑把选择父分类的地方填充数值
			
			//根据文章分类的parent_id查询文章分类的父分类的信息
			$parentArticleSortColumns = array('id','name');
			
			$parentArticleSort = $this->articleSortModel->getArticleSortById($articleSort['parent_id'],$parentArticleSortColumns);
			
			$articleSortAddForm->get('article.parent_id')->setValue($parentArticleSort['id']);
			
			$articleSortAddForm->get('article.parent_name')->setValue($parentArticleSort['name']);
			
		}
		
		
		$viewModel = new ViewModel();
		
		$viewModel->setVariable('articleSortAddForm', $articleSortAddForm);
		 
		return $viewModel;
		
	}//function showArticleSortAddAction() end
	
	
	public function checkArticleSortEditAction(){
	
	    $request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法操作');
		}
		
		$postData = $request->getPost();
		
		if(isset($postData['article_parent_id'])){
			$postData['parent_id'] =  $postData['article_parent_id'];
			unset($postData['article_parent_id']);
		}
		else{
			$postData['parent_id'] = 0;
		}
		
		$helpArticleSortFilterObject = new HelpArticleSortFilter();
		
		$helpArticleSortFilter = $helpArticleSortFilterObject->getInputFilter();
		
		$helpArticleSortFilter->setData($postData);
		
		$helpArticleSortFilter->setValidationGroup(array('id','name','description','parent_id'));
		
		
		if(!$helpArticleSortFilter->isValid()){
		
			$errorMessages = $helpArticleSortFilter->getMessages();
		
			foreach($errorMessages as $errorMessage){
				$this->returnMessage('300', array_pop($errorMessage));
			}
		
		}
		
	     
		$articleSort = $helpArticleSortFilter->getValues();
		
		
		$dbConnection = $this->getServiceLocator()->get('Zend\Db\Adapter\Connection');
			
		$dbConnection->beginTransaction();
		
		try{

			//第一步：查询父分类的信息，然后根据父分类的信息得到子分类的level
			$parentArticleInfo = $this->articleSortModel->getArticleSortById($articleSort['parent_id']);
				
			$articleSort['level'] = $parentArticleInfo['level'] + 1;
			
			//第二步：把收上来的数据更新到数据库	
			$this->articleSortModel->updateArticleSortById($articleSort['id'],$articleSort);
			
			//第三步：更新文章分类的左右值，就是移动分类
        	if($articleSort['parent_id']!=0){
        	//如果parent_id为0，那么就是根节点，那么就不要移动	
        		$this->articleSortModel->rebuildStructureTree(1,1);
        	}
		
		}
		catch(\Exception $e){
			
			$dbConnection->rollback();
			$this->returnMessage ('300', $e->getMessage () );
			
		}
		
		$dbConnection->commit ();
		$this->returnMessage ( '200', '恭喜您，编辑文章分类成功' );
		
	}//function checkArticleSortDeleteAction() end
	
	
	
	public function showArticleSortListAction()
	{
	
		$articleSortList = $this->articleSortModel->getArticleSortList();
		
		$articleSortList = $this->FormatArticleSortList($articleSortList);
		
		$articleSortForm = new ArticleSortListForm();
		
		$viewModel = new viewModel();
		
		$viewModel->setVariable('articleSortList', $articleSortList);
		
		$viewModel->setVariable('articleSortForm', $articleSortForm);
		
		return $viewModel;
	}//function showArticleSortListAction() end
	
	private function FormatArticleSortList($articleSortList){
	
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
	
	
    
}
