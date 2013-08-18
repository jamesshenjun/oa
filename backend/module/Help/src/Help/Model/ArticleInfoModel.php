<?php
namespace Help\Model;
use Zend\Db\Sql\Where;
use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use  Zend\Paginator\Adapter\DbSelect;
use  Zend\Paginator\Paginator;
use Application\Filter\HelpArticleFilter;
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
    
    public function getArticleInfoByArticleId($ArticleId,$columns=null){
    	 
    	$select = $this->getSql()->select();
    	 
    	if($columns===null){
    	 
    		$select->columns(array('id','title','level','left_number','right_number'));
    	 
    	}
    	else{
    		$select->columns($columns);
    	}
    	 
    	 
    	$where = new where();
    	 
    	if(is_integer($ArticleId)){
    
    		$where->equalTo('id', $ArticleId);
    		$select->where($where);
    
    		$result = $this->selectWith($where)->toArray();
    
    		
    
    	}
    	else if(is_array($ArticleId)){
    
    		$where->in('id', $ArticleId);
    		$select->where($where);
    
    		$result = $this->selectWith($where)->toArray();
    	}
    	
    }//function getArticleInfoListByArticleId() end
    
    /**
     * 通过文章分类id，查询该分类下的文章列表
     * @param int $ArticleSortId
     */
    
    public function getArticleInfoListByArticleSortId($ArticleSortId){
    	
    	$select = $this->getSql()->select();
    	
    	$where = new Where();
    	
    	$where->equalTo('article_sort_id', $ArticleSortId);
    	
    	$select->where($where);
    	
    	$select->columns(array('id','title'));
    	
    	$ArticleInfoList = $this->selectWith($select)->toArray();
    	
        return $ArticleInfoList;
        
    }//function getArticleInfoListByArticleSortId() end
    
    
    
    /**
     *  用来在文章列表展示页面取得文章列表
     */
    public function getArticleInfoList($queryData){
    	
    	$select = $this->getSql()->select();
    	
    	
    	$where = new Where();
    	
    	if (is_numeric($queryData['id'])){
    		
    		$where->equalTo('id', $queryData['id']);
    	}
    	
    	if ($queryData['keyword']){
    		
    		$where2 = new Where();
    		$where2->like('title', '%'.$queryData['keyword'].'%');
    		$where2->or;
    		$where2->like('content', '%'.$queryData['keyword'].'%');
    		$where->addPredicate($where2);
    	}
    	
    	if($queryData['author']){
    		
    		$where->like('author', '%'.$queryData['author'].'%');
    	}
    	
    	if ($queryData['article_sort_id']){
    		
    		if (is_array($queryData['article_sort_id'])){
    			$where->in('article_sort_id', $queryData['article_sort_id']);
    		}else{
    			$where->equalTo('article_sort_id', $queryData['article_sort_id']);
    		}
    		
    	}
    	
    	$select->where($where);
    	
    	$select->columns(array('id','title','hits','user_id','article_sort_id','add_time','status'));

    	
    	$paginatorAdapter =  new DbSelect(
    			$select,
    			$this->adapter
//     			$resultSetPrototype
    	);
    	$paginator =  new Paginator($paginatorAdapter);
    	
    	return $paginator;
    	
//     	return $this->selectWith($select)->toArray();
    	 
    	
    	
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
    
    
    
}