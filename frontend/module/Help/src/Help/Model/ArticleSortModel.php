<?php

namespace Help\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class ArticleSortModel extends BaseModel
{
    protected $table = 'oa_help_article_sort';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * 取得文章分类的列表
     * @return array;
     */
    
    public function getArticleSortList(){
    	
    	$select = $this->getSql()->select();
//     	$select->where(array('left_number >1'));
    	$select->order('left_number','asc');
    	
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
    //根据文章分类id得到子分类的列表

    	//根据文章分类id得到文章分类信息
    	$select = $this->getSql()->select();
    	
    	$where = new where();
    	
    	$where->equalTo('id',$ArticleSortId);
    	
    	$select->where($where);
    	
    	$select->columns(array('id','left_number','right_number'));
    	
    	$articleSortInfo = $this->selectWith($select)->toArray();
    	
    	
    	//根据父分类的名称得到子分类的列表
    	$parentSortInfo  = $articleSortInfo[0];
    	
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