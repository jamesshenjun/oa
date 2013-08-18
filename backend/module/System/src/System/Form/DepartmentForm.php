<?php

namespace System\Form;

use Zend\Form\Element\Hidden;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\MultiCheckbox;

class DepartmentForm extends Form
{
	public function __construct($name = null)
	{
		// we want to ignore the name passed
		parent::__construct ();
		
		//设置表单的一些属性
		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'pageForm required-validate');
		$this->setAttribute('method', 'post');
		$this->setAttribute('onsubmit', 'return validateCallback(this)');
		
		
		//设置部门ID，用于编辑
		$id = new Hidden('id');
		$this->add($id);
		
		
		//设置表单中 部门的名称
		$name = new Text();
		$name->setName('name');
		$name->setAttribute('class', 'required textInput');
		$this->add($name);
		
		
		//设置表单中 部门的上级部门
		
		$parentName = new Text();
		$parentName->setName('org.parent_name');
		$parentName->setAttribute('disabled', 'disabled');
		$parentName->setAttribute('class', 'required textInput');
		$this->add($parentName);
		
		$parentId = new Hidden('org.parent_id');
		$parentId->setAttribute('id', 'parent_id');
		$this->add($parentId);
		
		
		
		
		//设置表单中 部门的描述
		$description = new Textarea('description');
		$this->add($description);
		
		//设置表单中 部门的报告类型
		$reportType = new Select();
		$reportType->setName('report_type');
		$reportType->setValueOptions(array(
											'0'=>'仅需要提交日报',
											'1'=>'仅需要提交周报',
											'2'=>'周报和日报都需要提交'
										  )
									);
		
		$this->add($reportType);
		
		
		//设置表单中 部门所使用的日报模板类型
		$dailyReportTemplateId = new Select();
		$dailyReportTemplateId->setName('daily_report_template_id');
		
		$this->add($dailyReportTemplateId);
		
		
		//设置表单中 部门所使用的周报模板类型
		$weeklyReportTemplateId = new Select();
		$weeklyReportTemplateId->setName('weekly_report_template_id');
		
		//$weeklyReportTemplateId->setValueOptions($options)
		$this->add($weeklyReportTemplateId);
		
		
		//设置表单中 部门的领导人的名称
		
		$leaderUserId = new Hidden();
		$leaderUserId->setName('leader.user_id');
		$this->add($leaderUserId);
		
		$leaderName = new Text('leader.user_name');
		$leaderName->setAttribute('disabled', 'disabled');
		$leaderName->setAttribute('class', 'required textInput');
		$this->add($leaderName);
		
		
		
		$sumbit = new Element\Button('submit');
	    $sumbit->setAttribute('type', 'submit');
	    $sumbit->setLabel('提交');
	    $this->add($sumbit);
	    
	    $close = new Element\Button('reset');
	    $close->setAttribute('type', 'reset');
	    $close->setAttribute('class', 'reset');
	    $close->setLabel('重置');
	    $this->add($close);
	    
		
	}

}
