<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Help\Controller;


use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Application\Factory\ServiceLocatorFactory;
use Zend\Paginator\Paginator as Paginator;

use Zend\View\Helper\PaginationControl as PaginationControl;

class ArticleController extends BaseController
{
	protected $articleSortModel;
	protected $articleInfoModel;
	protected $articleContentModel;
	
	protected $userModel;
	
	
    function __construct(){
    	
    	$serviceManager = ServiceLocatorFactory::getInstance();
    	
    	$this->getDbModel($serviceManager,'Help','Model','ArticleInfoModel');
    	
    	$this->getDbModel($serviceManager,'Help','Model','ArticleContentModel');
    	
    	$this->getDbModel($serviceManager,'Help','Model','ArticleSortModel');
    	
    	$this->getDbModel($serviceManager,'Application','Model','UserModel');
    	
    }
	
	public function indexAction()
    {
    	
    	//取得左侧导航
    	$strectchMenuViewModel = $this->getStrectchMenu();
    	
    	$viewModel = new ViewModel();
    	
    	//新建视图对象
    	
    	//把头部的导航栏加载到布局模板中
    	$headerSearchViewModel =  new ViewModel();
    	$headerSearchViewModel->setTemplate('help/article/headerSearch');
    	$viewModel->addChild($headerSearchViewModel,'headerSearch');
    	
    	
    	//把伸缩菜单加载到布局模板中
    	$viewModel->addChild($strectchMenuViewModel,'strectchMenu');
    	
    	
    	//把文章列表加载到布局模板中
    	$articleListViewModel  =  $this->showLatestArticleAction();
    	$viewModel->addChild($articleListViewModel,'articleList');
    	
    	
    	
    	//重置页面的选中状态
    	$this->layout()->setVariable('select','question');
    	
    	return $viewModel;
    }
    
    
    /**
     * 文章首页最新更新内容
     */
    public function showLatestArticleAction()
    {
    	 
    	//取首页数据，默认为最新更新的前10条
    	$paginator = $this->articleInfoModel->getArticleInfoList();
    
    	$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
    
    	$config = $this->getServiceLocator()->get('config');
    	$paginator->setItemCountPerPage($config['percent_page_row_count']);
    	 
    	$articleListViewModel  =  new ViewModel();
    	$articleListViewModel->setTemplate('help/article/show-article-list');
    	$articleListViewModel->setVariable('paginator', $paginator);
    	$articleListViewModel->setVariable('title', '最新更新');
    	 
    	return $articleListViewModel;
    	 
    }
    /**
     * 文章内容页
     * @return \Zend\View\Model\ViewModel
     */
    
    public function showArticleContentAction()
    {
    	$id = $this->params('id');
    	
    	$articleContent = $this->articleContentModel->getRowById($id);
    	$articleContent = $articleContent['content']; 
    	
    	$articleInfo = $this->articleInfoModel->getRowById($id,array('id','title','sub_title','hits','user_id','add_time','update_time'));
    	
    	
    	$articleInfo = $this->formatArticleInfo($articleInfo);

    	$viewModel = new ViewModel();
    	
    	//把头部的导航栏加载到布局模板中
    	$headerSearchViewModel =  new ViewModel();
    	$headerSearchViewModel->setTemplate('help/article/headerSearch');
    	$viewModel->addChild($headerSearchViewModel,'headerSearch');
    	
    	//设置文章信息
    	$viewModel->setVariable('articleInfo', $articleInfo);
    	
    	//设置文章内容
    	$viewModel->setVariable('articleContent', $articleContent);
    	
    	//重置页面的选中状态
    	$this->layout()->setVariable('select','question');

    	return $viewModel;
    }
    
    /**
     * 文章列表页
     * @return \Zend\View\Model\ViewModel
     */
    
    
    public function showArticleListAction()
    {
    	$request = $this->getRequest();

    	if (!$request->isPost()){
    		die('传入数据错误');
    	}
    	
    	$articleSortId 	   = $request->getPost('sortId');
    	
    	
    	$paginator = $this->articleInfoModel->getArticleInfoListByArticleSortId($articleSortId);
    	$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
    	
    	$config = $this->getServiceLocator()->get('config');
    	$paginator->setItemCountPerPage($config['percent_page_row_count']);
    	
    	
    	$articleSortInfo = $this->articleSortModel->getRowById($articleSortId,array('name'));
    	
    	 
    	$viewModel = new ViewModel();
    	
    	$viewModel->setVariable('paginator', $paginator);
    	
    	$viewModel->setVariable('title', $articleSortInfo['name']);
    	
    	$viewModel->setVariable('data', array('flag'=>'sortId','data'=>'sortId='.$articleSortId));
    	
    	return $viewModel;
    
    }
    
    /**
     * 文章搜索结果页
     * @return \Zend\View\Model\ViewModel
     */
    public function showSearchListAction()
    {
    	$request = $this->getRequest();
    	if (!$request->isPost()){
    		die('传入数据错误');
    	}
    	
    	$dataPost = $request->getPost();
    	$keywords = $dataPost->keywords;
    	
    	
    	$paginator = $this->articleInfoModel->getArticleListByKeywords($keywords);
    	$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
    	$config = $this->getServiceLocator()->get('config');
    	$paginator->setItemCountPerPage($config['percent_page_row_count']);
    	
    	$viewModel = new ViewModel();
    	$viewModel->setTemplate('help/article/show-article-list');

    		
    	$viewModel->setVariable('paginator', $paginator);
    	$viewModel->setVariable('title', $keywords.'的搜索结果');
    	$viewModel->setVariable('data', array('flag'=>'keywords','data'=>'keywords='.$keywords));
    	
    	return $viewModel;
    }
    
    
    private function getStrectchMenu(){
    	
    	$flag = 0;
    	$temp = array();
    	
    	$articleSortList = $this->articleSortModel->getArticleSortList();
    	foreach ($articleSortList as $articleSort){
    		
    		
    		if ($articleSort['level'] == 1){
    			
    			continue;
    			
    		}elseif($articleSort['level'] == 2){
    			$flag++;
    			$temp[$flag] = $articleSort;
    			
    			
    		}elseif ($articleSort['level'] == 3){
    			if (!isset($temp[$flag]['child'])){
    				$temp[$flag]['child'] = array();
    			}
    			array_push($temp[$flag]['child'], $articleSort);
    		}
    	}
    	
    	$strectchMenuViewModel = new ViewModel();
    	$articleSortList = $temp;
    	$strectchMenuViewModel->setVariable('articleSortList', $articleSortList);
    	$strectchMenuViewModel->setTemplate('help/article/strectchMenu');
    	
    	
    	return $strectchMenuViewModel;
    	
    	
    }//function getStrectchMenu() end
        
    
    private function formatArticleInfo($articleInfo){
    	
    	$userInfo = $this->userModel->getRowById($articleInfo['user_id'], array('id','realname'));
    	$articleInfo['user_name'] = $userInfo['realname'];
    	
    	return $articleInfo;
    }
    
    
    
}
