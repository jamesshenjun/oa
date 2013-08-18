<?php

namespace Help\Model;

use Application\Filter\HelpArticleFilter;

use Zend\I18n\Validator\Alnum;

use Zend\Db\Sql\Select;

use Zend\Db\Sql\Where;
use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use  Zend\Paginator\Adapter\DbSelect;
use  Zend\Paginator\Paginator;
use Zend\Db\ResultSet\ResultSet;



class ArticleInfoModel extends BaseModel
{
    protected $table = 'oa_help_article_info';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    
    /**
     * 根据文章的id得到文章的相关信息
     * 因为文章id是主键，
     * 如果传进来的参数是整数，那么返回一个一维数组
     * 如果传进来的参数是数组，那么返回一个二维数组
     * 
     * @param int $ArticleId | array $ArticleId
     */
    
   /**
     * 通过文章分类id，查询该分类及子孙分类下的文章列表
     * 如果传进来的参数是整数还是数组，都返回一个二维数组，因为$ArticleSortId不是主键
     * @param $ArticleSortId $ArticleSortId
     */
    
    public function getArticleInfoListByArticleSortId($ArticleSortId,$columns=null){
    	
    	$select = $this->getSql()->select();
    	
    	$where = new Where();
    	
    	if(is_numeric($ArticleSortId)){
    		$where->equalTo('article_sort_id', $ArticleSortId);
    	}
    	else{
    		$where->in('article_sort_id', $ArticleSortId);
    	}
    	
    	$select->where($where);
    	
    	$select->columns(array('id','title','hits','add_time','user_id'));
    	
    	$resultSetPrototype =  new ResultSet();
    	$resultSetPrototype->setArrayObjectPrototype(new HelpArticleFilter());
    	
    	
    	
    	
    	$paginatorAdapter =  new DbSelect(
    			$select,
    			$this->adapter,
    			$resultSetPrototype
    	);
    	$paginator =  new Paginator($paginatorAdapter);
    	 
    	return $paginator;
        
    }//function getArticleInfoListByArticleSortId() end
    
    
    
    /**
     *  用来在文章列表展示页面取得文章列表
     */
    public function getArticleInfoList(){
    	
    	$select = $this->getSql()->select();
    	
    	$select->columns(array('id','title','hits','user_id','article_sort_id','hits','add_time'));
    	
    	$select->order('add_time Desc');
    	
    	$resultSetPrototype =  new ResultSet();
    	$resultSetPrototype->setArrayObjectPrototype(new HelpArticleFilter());
    	$paginatorAdapter =  new DbSelect(
    			$select,
    			$this->adapter,
    			$resultSetPrototype
    	);
    	$paginator =  new Paginator($paginatorAdapter);
    	
    	
    	return $paginator;
    	 
    	
    	
    }//function getArticleInfoList() end
    
    
    /**
     * 发表文章到数据库
     * @param array $ArticleInfo
     * @throws \Exception
     */
    
    public function articleInfoAdd( $ArticleInfo ){
    	
    	if(!$this->insert($ArticleInfo)){
    		
    		throw new \Exception ( "插入文章信息表时出现异常数据，请联系网站管理员！" );
    	  	
    	}
    	
    }//function articleInfoAdd() end
    
    
    public function getArticleSortArticleCount($ArticleSortId){
    	
    	$select = $this->getSql()->select();
    	
    	$selectExpression = new Expression();
    	$selectExpression->setExpression("count(`id`)");
    	
    	$select->columns(array('article_sort_id','count'=>$selectExpression));
    	
    	$where =  new Where();
    	
    	$where->in('article_sort_id',$ArticleSortId);
    	
    	$select->where($where);
    	
    	$select->group(array('article_sort_id'));
    	
    	
    	$ArticleSortArticleCount = $this->selectWith($select)->toArray();
    	
    	return $ArticleSortArticleCount;
    	
    }//function getArticleSortArticleCount() end
    
    
    /**
     * @param string 
     * @return array $articleList
     */
    public function getArticleListByKeywords($keywords)
    {
    	
    	$select = $this->getSql()->select();
    	$where = new Where();
    	
    	$keywords = ltrim($keywords);
    	$keywords = rtrim($keywords);
    	
    	if (stripos($keywords,' ')){
    		
    		$whereAdd = new Where();
    		$keywordsArr = explode(' ',$keywords);
    		foreach ($keywordsArr as $words){
    			$whereAdd->like('title', '%'.$words.'%');
    			$whereAdd->or;
    			$whereAdd->like('content', '%'.$words.'%');
    			$whereAdd->or;
    			$whereAdd->like('keyweord', '%'.$keywords.'%');
    		}
			$where->addPredicate($whereAdd);
    	}else{
    		$where->like('title', '%'.$keywords.'%');
    		$where->or;
    		$where->like('content', '%'.$keywords.'%');
    		$where->or;
    		$where->like('keyword', '%'.$keywords.'%');
    	}

    	$select->where($where);
    	
//     	@print_r($select->getSqlString());
    	
    	
    	$resultSetPrototype =  new ResultSet();
    	$resultSetPrototype->setArrayObjectPrototype(new HelpArticleFilter());
    	$paginatorAdapter =  new DbSelect(
    			$select,
    			$this->adapter,
    			$resultSetPrototype
    	);
    	$paginator =  new Paginator($paginatorAdapter);
    	 
    	 
    	return $paginator;
    	
    	
    }
    
    
    
    
}