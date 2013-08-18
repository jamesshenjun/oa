<?php

namespace Report\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class ReportTemplateInfoModel extends BaseModel
{
    protected $table = 'oa_report_template_info';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    public function countTemplate($param)
    {
    	$select = $this->getSql()->select();
    	$where = new Where();
    	//初始化查询条件  
    	if (!empty($param['department_id'])){
    		$where->equalTo('department_id', $param['department_id']);
    	}
    	$where->equalTo('type', $param['type']);
    	$where->equalTo('status', "Y");
    	$select->where($where);
    	$count = $this->selectWith($select)->count();
    	 
    	return $count;
    	
    }
    public function getTemplateInfo($id)
    {
    	$select = $this->getSql()->select();
    	$where = new Where();
    	//初始化查询条件
    	$where->equalTo('id', $id);
    	$where->equalTo('status', "Y");
    	$select->where($where);
    	
    	$TemplateInfo = $this->selectWith($select)->toArray();
    	
    	return array_pop($TemplateInfo);
    }
    
    
    public function getTemplateInfoBydepartmentId($departmentId)
    {
    	if (!is_numeric($departmentId)){
    		return false;
    	}
    	
    	$select = $this->getSql()->select();
    	$where = new Where();
    	//初始化查询条件
    	$where->equalTo('department_id', $departmentId);
    	$where->equalTo('status', "Y");
    	$select->where($where);
    	$TemplateInfo = $this->selectWith($select)->toArray();
    	 
    	return $TemplateInfo[0];
    }
    
    
    
    
    
    public function getTemplateList($param)
    {
    	$select = $this->getSql()->select();
    	$where = new Where();
    	 
    	//初始化查询条件

    	$where->equalTo('type', $param['type']);
    	$where->equalTo('status', "Y");
    	$select->where($where);
    	 
    	if(!empty($param['offset'])){
    		$select = $select->offset($param['offset']);
    	}
    
    	if(!empty($param['numPerPage'])){
    		$select = $select->limit($param['numPerPage']);
    	}
    
    	$select->order('add_time DESC');
    	$result = $this->selectWith($select)->toArray();
    	 
    	return $result;
    }
    
    /**
     * 保存模板信息
     * @param array $TemplateData
     */
    public function saveTemplateInfo($TemplateData)
    {
        if ( sizeof($TemplateData) < 1 ){
    		throw new \Exception("没有数据！");
    	}
    	
    	if(!$this->insert($TemplateData)){
    		throw new \Exception("模板信息插入失败");
    	}

    }

    /**
     * 更新模板信息
     * @param array $TemplateData $where
     */
    public function updateTemplateInfo($TemplateData,$where)
    {
    	if (sizeof($TemplateData) < 1 ){
    		throw new \Exception("没有数据！");
    	}
    	 
    	if(!$this->update($TemplateData,$where)){
    		throw new \Exception("模板信息更新失败");
    	}
    	
    }

    /**
     * 删除模板
     * @param int $tid
     */
    public function deleteTemplateInfo($tid)
    {
    	return $this->update(array("status"=>"D"),array("id"=>$tid));
    }
}