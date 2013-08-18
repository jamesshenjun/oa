<?php

namespace Report\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class ReportContentModel extends BaseModel
{
    protected $table = 'oa_report_content';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * 根据报告id获取报告内容
     * @param int $rid
     * @return array $ReportContent
     */
    public function getReportContent($id)
    {
    	$select = $this->getSql()->select();
    	$where = new Where();
    	$where->equalTo('id', $id);
    	$select->where($where);
    	$result = $this->selectWith($select)->toArray();
    	return $result;
    }
   
    
/**
     * 保存报告内容
     * @param array $TemplateData
     */
    public function saveReportContent($ReportData)
    {
    	if (sizeof ( $ReportData ) < 1) {
    		throw new \Exception ( "没有数据！" );
    	}
    	foreach ($ReportData as $data){
    		if (! $this->insert ( $data )) {
    			throw new \Exception ( "保存报告内容出错！" );
    		}
    	}

    }

    /**
     * 删除报告
     * @param int $rid
     */
    public function deleteReportContentById($id)
    {
    	if (!is_numeric($id)){
    		throw new \Exception ( "传入 ID 出错！" );
    	}
    	if (!$this->delete(array("id"=>$id))){
    		throw new \Exception ( "删除报告出错！" );
    	}
    }
    
    /**
     * 通过关键字搜索报告
     */
    public function getRepoartByKeywords($keywords)
    {
    	$select = $this->getSql()->select();
    	$where = new Where();
    	$where->like('content', '%'.$keywords.'%');
    	$select->where($where);
    	$select->columns(array('id'));
    	$result = $this->selectWith($select)->toArray();
    	$rid = array();
    	foreach ($result as $key=>$value)
    	{
    		$rid[$key] = $value['id'];
    	}
    	
    	return (sizeof($rid) > 0)?$rid:null;
    }
    
    
    
    
}