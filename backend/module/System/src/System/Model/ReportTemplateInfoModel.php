<?php

namespace System\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class ReportTemplateInfoModel extends BaseModel {
	
	protected $table = 'oa_report_template_info';
	
	public function __construct(Adapter $adapter) {
		$this->adapter = $adapter;
		$this->initialize ();
	}
	
	/**
	 * 返回可以使用的周报模板列表
	 */
	
    public function getWeeklyTemplateInfoList(){
    	
    	$select = $this->getSql()->select();
    	
    	$select->columns(array('id','name'));
    	
    	$where = new Where();
    	
    	$where->equalTo('status','Y');
    	
    	$where->equalTo('type','w');
    	
    	$select->where($where);
    	
    	$weeklyTemplateInfoList = $this->selectWith($select)->toArray();
    	
    	return $weeklyTemplateInfoList;
    
    }
    
    /**
     * 返回可以使用的日报模板列表
     */
    
    public function getDailyTemplateInfoList(){
    	 
    	$select = $this->getSql()->select();
    	 
    	$select->columns(array('id','name'));
    	 
    	$where = new Where();
    	 
    	$where->equalTo('status','Y');
    	 
    	$where->equalTo('type','d');
    	 
    	$select->where($where);
    	 
    	$dailyTemplateInfoList = $this->selectWith($select)->toArray();
    	 
    	return $dailyTemplateInfoList;
    	
    
    }
	
	

	
}