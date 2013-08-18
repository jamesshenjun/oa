<?php

namespace Report\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class UserReportModel extends BaseModel
{
    protected $table = 'oa_user_report';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * 根据周数，得到这周交了周报的用户的ID
     * @param unknown_type $weekNum
     * @return array $ids
     */
    public function getReportedPersonByWeekNum($weekNum)
    {
    	
    	$select = $this->getSql()->select();
    	
    	$select->where(array('type'=>'w','week_number'=>$weekNum));
    	
    	$select->columns(array('user_id'));
    	
    	$res = $this->selectWith($select);
    	
    	$ids = array();
    	
    	foreach($res as $key=>$id){
    		
    		$ids[$key] = $id['user_id'];
    	}
    	
    	return $ids;
    	
    }
    
    /**
     * 用于添加周报时在user_report 表中插入记录
     * @param unknown_type $weekNum
     * @param unknown_type $userId
     * @throws \Exception
     */
    public function addWeeklyReport($weekNum ,$userId)
    {
    	if (!is_numeric($weekNum)  ){
    		throw new \Exception("传入周数错误");
    	}
    	if (!is_numeric($userId)  ){
    		throw new \Exception("传入用户ID错误！");
    	}
    	$Data = array(
    				'type'		  =>'w',
    				'week_number' =>$weekNum,
    				'user_id'	  =>$userId,
    			);
    	
    	if (!$this->insert($Data)){
    		throw new \Exception("插入用户周报记录错误！");
    	}
    }
    
    public function removeWeeklyReport($weekNum ,$userId)
    {
    	if (!is_numeric($weekNum)  ){
    		throw new \Exception("传入周数错误");
    	}
    	if (!is_numeric($userId)  ){
    		throw new \Exception("传入用户ID错误！");
    	}
    	
    	$where = array(
    			'type'		  =>'w',
    			'week_number' =>$weekNum,
    			'user_id'	  =>$userId,
    	);
    	 
    	if (!$this->delete($where)){
    		throw new \Exception("移除用户周报记录错误！");
    	}    	
    }
    
    
    /**
     * 检测用户本周是否交了周报了
     * @param $weekNum , $userId
     * @return bool
     */
    public function hasWeekReported($weekNum ,$userId)
    {
    	$select = $this->getSql()->select();
    	
    	
    	$select->columns(array('id'), false);
    	
    	$select->where(array('type'=>'w','user_id'=>$userId, 'week_number'=>$weekNum));
    	
    	$flag = $this->selectWith($select)->count();
    	
    	return $flag;
    	
    }    
    
    
   
}