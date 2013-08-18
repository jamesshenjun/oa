<?php

namespace System\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;

use Zend\Db\Sql\Where;

class NodeModel extends BaseModel
{
    protected $table = 'oa_node';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * 根据主方法的id列表 得到 辅助方法的id列表
     * 无论是参数是int还是array,都返回二维数组
     */
    
    public function getAssistantNodeList($id,$columns=null){
    	
    	$select = $this->getSql()->select();
    	
    	if($columns===null){
    		$columns = array('id');
    	}
    	
    	$where = new Where();
    	
    	if(is_numeric($id)){
    		$where->equalTo('master_id', $id);
    	}
    	else if(is_array($id)){
    		$where->in('master_id', $id);
    	}
    	 
    	$select->where($where);
    	//装载查询条件
    	
    	$nodeList = $this->selectWith($select)->toArray();
    	 
    	return $nodeList;
    	
    }//function getAssistantNodeIdList() end
    
    
    
    
    
    
    /**
     * 根据节点id的类型来选择查询条件in 还是  equal 还是没有id限制条件
     * 如果是参数是int,返回一维数组；如果参数是array,返回二维数组；如果没有id的限制条件，返回二维数组
     */
    public function getNodeById($id=null,$columns=null,$externalWhere=null){
    	
    	$select = $this->getSql()->select();
    	
    	if($columns===null){
    		$columns = array('id','name','title','description','left_number','right_number','level','parent_id','status');
    	}
    	
    	$select->columns($columns);
    	
    	$where = new Where();
    	
    	if($externalWhere!==null){
        //如果查询条件不为空，就添加这个查询条件
        	$where->addPredicate($externalWhere);
    		//装载查询条件
    	}
    	
    	if(is_numeric($id)){
    		$where->equalTo('id', $id);
    	}
    	else if(is_array($id)){
    		$where->in('id', $id);
    	}
    	
    	$select->where($where);
    	//装载查询条件
    	
    	$select->order(array('left_number'=>'asc'));
    	
    	$nodeList = $this->selectWith($select)->toArray();
    	
    	return $nodeList;
    	
   }//function getNodeList() end
   
   
    
    
    
    

    
    
}