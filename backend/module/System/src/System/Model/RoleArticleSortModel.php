<?php

namespace System\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;

class RoleArticleSortModel extends BaseModel
{
    protected $table = 'oa_role_article_sort';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * 根据角色ID返回分配给该角色的文章分类ID数组
     */
    public function getArticleSortIdArrByRoleId($roleId)
    {
    	$select = $this->getSql()->select();
    	$select->where(array("role_id"=>$roleId));

    	$result = $this->selectWith($select)->toArray();
    	$articleSortIdArr = array();
    	
    	if($result){
    		foreach($result as $key=>$value){
    			$articleSortIdArr[] = $value["article_sort_id"];
    		}
    		$articleSortIdArr = array_unique($articleSortIdArr);
    	}
    	return $articleSortIdArr;
    }
    
    /**
     * 删除角色文章分类
     */
    public function deleteArticleSortByRoleId($roleId)
    {
    	$this->delete(array("role_id"=>$roleId));
    }
    
    /**
     * 批量插入角色的文章分类
     */
    public function saveArticleSort($data)
    {
    	$roleId = $data["role_id"];
    	$articleSortData = $data["articleSortData"];
    	
    	foreach($articleSortData as $key=>$articleSortId){
    	    $this->insert(array("role_id"=>$roleId,"article_sort_id"=>$articleSortId));
    	}
    }
}