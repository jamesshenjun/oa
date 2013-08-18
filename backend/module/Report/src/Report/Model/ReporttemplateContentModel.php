<?php

namespace Report\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class ReportTemplateContentModel extends BaseModel
{
    protected $table = 'oa_report_template_content';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    /**
     * 根据模板id获取模板内容
     * @param int $tid
     * @return array $templateContent
     */
    public function getTemplateContent($id)
    {
    	$select = $this->getSql()->select();
    	$where = new Where();
    	$where->equalTo('id', $id);
    	$select->where($where);
    	
    	$result = $this->selectWith($select)->toArray();
    	return $result;
    }
    

    /**
     * 保存模板内容
     * @param array $TemplateData
     */
    public function saveTemplateContent($TemplateData)
    {
    	if (empty($TemplateData)){
    		throw new \Exception("没有数据！");
    	}
    	foreach ($TemplateData as $key=>$data){
    		if(!$this->insert($data)){
    			throw new \Exception("保存模板内容第".$key."条失败！");
    		}
    			
    	}
    	

    }
    /**
     * 
     * @param unknown_type $tid
     * @return number
     */
    public function deleteTemplateContnetById($id)
    {
    	if (empty($id)){
    		throw new \Exception("没有ID！");
    	}
    	if(!$this->delete(array("id"=>$id))){
    		throw new \Exception("删除模板内容失败！");
    	}
    }

}