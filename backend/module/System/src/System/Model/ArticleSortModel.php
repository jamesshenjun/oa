<?php

namespace System\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;

class ArticleSortModel extends BaseModel
{
    protected $table = 'oa_help_article_sort';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * 得到所有的文章分类
     */
    public function getAllArticleSort()
    {
    	$select = $this->getSql()->select();
    	$select->order('left_number ASC');
    	
    	$result = $this->selectWith($select)->toArray();	
    	return $result;
    }
    
    /**
     * 根据文章分类ID数组查询父ID
     * @param array $articleSortId
     * @return array
     */
    public function getParentIdByArticleSortId(array $articleSortId)
    {
    	$select = $this->getSql()->select();
    	$select->columns(array('parent_id'));
    	
    	$select->where(array("id"=>$articleSortId));
    	$select->where(array("level > 0"));
    	
    	$result = $this->selectWith($select)->toArray();
    	$articleSortIdArrOfParent = array();
    	 
    	foreach($result as $key=>$value){
    		if($value["parent_id"]){
    			$articleSortIdArrOfParent[] = $value["parent_id"];
    		}
    		$articleSortIdArrOfParent = array_unique($articleSortIdArrOfParent);
    	}
    	
    	return $articleSortIdArrOfParent;
    }
    
    /**
     * 根据文章分类ID返回数组
     * @param int|array $articleSortId
     * @return array
     */    
    public function getArticleSortById($articleSortId)
    {
    	$select = $this->getSql()->select();
    	
    	$select->where(array("id"=>$articleSortId));
    	$result = $this->selectWith($select)->toArray();

    	if($result){
    		$result = is_array($articleSortId)?$result:$result[0];
    	}
    	
    	return $result;
    }

}