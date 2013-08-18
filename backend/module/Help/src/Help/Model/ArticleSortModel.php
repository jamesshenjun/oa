<?php

namespace Help\Model;

use Zend\Db\Sql\Update;

use Zend\Console\Prompt\Select;

use Zend\Db\Sql\Expression;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class ArticleSortModel extends BaseModel
{
    protected $table = 'oa_help_article_sort';
    
    const INSERT_NODE     = 'insert';
    const DELETE_NODE     = 'delete';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    
    
    /**
     * @param int $id
     * @param array $data
     */
    
    public function updateArticleSortById($id,$data){
    	
    	$update = $this->getSql()->update();
    	
    	$where = new Where();
    	
    	$where->equalTo('id', $id);
    	
    	$update->where($where);
    	
    	$update->set($data);
    	
    	$this->updateWith($update);
    	
    }
    
    
  	/**
     * 在文章分类表中插入新的节点
     * @param unknown_type $ArticleSort
     * @throws \Exception
     */
    public function insertArticleSort($ArticleSort){
    	
    	if(!$this->insert($ArticleSort)){
    		throw new \Exception('在文章分类中插入新节点失败');
    	}
    	
    }//function insertArticleSort() end
    
    
    
    /**
     * 根据文章的id得到文章的相关信息
     * 因为文章id是主键，
     * 如果传进来的参数是整数，那么返回一个一维数组
     * 如果传进来的参数是数组，那么返回一个二维数组
     *
     * @param int $ArticleId | array $ArticleId
     */
    
    public function getArticleSortById($ArticleSortId,$columns=null){
    
    	$select = $this->getSql()->select();
    
    	if($columns===null){
    
    		$select->columns(array('id','name','level','left_number','right_number'));
    
    	}
    	else{
    		$select->columns($columns);
    	}
    
    	
    	$where = new where();
    
    	if(is_numeric($ArticleSortId)){
    
    		$where->equalTo('id', $ArticleSortId);
    		
    		$select->where($where);
    
    		$result = $this->selectWith($select)->toArray();
    		
    		$articleSort = $result[0];
    		
    	   
    
    	}
    	else if(is_array($ArticleSortId)){
    
    		$where->in('id', $ArticleSortId);
    		
    		$select->where($where);
    
    		$articleSort = $this->selectWith($select)->toArray();
    		
    	}
    	
    	return $articleSort;
    	 
    }//function getArticleInfoListByArticleId() end
    
    
    public function deleteArticleSort($articleSortId){
    	
    	$delete = $this->getSql()->delete();
    	
    	$where = new Where();
    	
    	$where->equalTo('id', $articleSortId);
    	
    	$delete->where($where);
    	
    	$this->deleteWith($delete);
    	
    }//function deleteArticleSort() end
    
    
    /**
     * 取得文章分类的列表
     * @return array;
     */
    
    public function getArticleSortList(){
    	
    	$select = $this->getSql()->select();
    	
    	$select->order(array('left_number'=>'asc'));
    	
    	$articleSortList = $this->selectWith($select)->toArray();
    	
    	return $articleSortList;
    	
    }//function getArticleSortList() end
    
    
    /**
     * 通过传过来的文章分类的id的列表，返回一个文章分类id与对应的子孙分类的对应关系
     * 用于在文章分类列表中显示每个分类的文章数量做第一步
     * @param unknown_type $ArticleSortId
     * @return unknown
     */
    
    public function getChildrenByArticleSortId($ArticleSortId){
    //根据文章分类id得到子孙分类的列表

    	//根据文章分类id得到文章分类信息
    	$select = $this->getSql()->select();
    	
    	$where = new where();
    	
    	$where->equalTo('id',$ArticleSortId);
    	
    	$select->where($where);
    	
    	$select->columns(array('id','left_number','right_number'));
    	
    	$articleSortInfo = $this->selectWith($select)->toArray();
    	
    	//根据父分类的名称得到子分类的列表
    	$parentSortInfo  = array_pop($articleSortInfo);
    	
    	$select = $this->getSql()->select();
    	
    	$where  = new where();
    	
    	$where->greaterThan('left_number', $parentSortInfo['left_number']);
    	
    	$where->lessThan('right_number', $parentSortInfo['right_number']);
    	
    	$select->where($where);
    	
    	$select->columns(array('id','left_number','right_number'));
    	
    	$ArticleSortChildren = $this->selectWith($select)->toArray();
    	
    	return $ArticleSortChildren;
    	
    }//function getArticleSortInfoByArticleSortId() end
    
    
   
    
}