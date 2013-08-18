<?php
namespace Application\Filter;

use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Filter\StripTags;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;

class DepartmentFilter implements InputFilterAwareInterface
{
	
	
	
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		
	}
	public function getInputFilter()
	{
		$StripTags = new StripTags();
		$Notzero = new NotEmpty();
		$Notzero->setType(NotEmpty::ZERO);
		$Notzero->setMessage('请选择上级部门');
		
		
		$NotEmpty = new NotEmpty();
		$NotEmpty->setType(NotEmpty::STRING);
		$NotEmpty->setMessage('请输入部门名称');
		
		$StringLength = new StringLength();
		$StringLength->setMax('50');
		$StringLength->setMin('3');
		$StringLength->setMessage("你输入的部门名称小于%min% 个字符",'stringLengthTooShort');
		$StringLength->setMessage("你输入的部门名称大于与 %max% 个字符",'stringLengthTooLong');
		
		
		
		$notNUll = new NotEmpty();
		$notNUll->setType(NotEmpty::NULL);
		
		$id	= new Input();
		$id->setName('id');
		$id->setAllowEmpty(true);
		$id->getFilterChain()->attach($StripTags);
		
		$report_type = new Input();
		$report_type->setName('report_type');
		$report_type->getFilterChain()->attach($StripTags);
		$report_type->getValidatorChain()->addValidator($notNUll);
		
		$daily_report_template_id = new Input();
		$daily_report_template_id->setName('daily_report_template_id');
		$daily_report_template_id->getFilterChain()->attach($StripTags);
		
		$weekly_report_template_id = new Input();
		$weekly_report_template_id->setName('weekly_report_template_id');
		$weekly_report_template_id->getFilterChain()->attach($StripTags);
		
		$departmentName = new Input();
		$departmentName->setName('name');
		$departmentName->getFilterChain()->attach($StripTags);
		$departmentName->getValidatorChain()->addValidator($NotEmpty);
		$departmentName->getValidatorChain()->addValidator($StringLength);
		
		
		$parent = new Input();
		$parent->setName('parent_id');
		$parent->getValidatorChain()->addValidator($notNUll);
		
		

		$departmentdescription= new Input();
		$departmentdescription->setName('description');
		$departmentdescription->setAllowEmpty(true);
		$departmentdescription->getFilterChain()->attach($StripTags);
		
		
		$DepartmentFilter = new InputFilter();
		$DepartmentFilter->add($id);
		$DepartmentFilter->add($departmentName);
		$DepartmentFilter->add($departmentdescription);
		$DepartmentFilter->add($parent);
		$DepartmentFilter->add($report_type);
		$DepartmentFilter->add($daily_report_template_id);
		$DepartmentFilter->add($weekly_report_template_id);
		
		return $DepartmentFilter;
		
	}
}