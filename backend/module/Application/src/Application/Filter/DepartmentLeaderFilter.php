<?php
namespace Application\Filter;

use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Filter\StripTags;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;

class DepartmentLeaderFilter implements InputFilterAwareInterface
{
	
	
	
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		
	}
	public function getInputFilter()
	{
		$StripTags = new StripTags();
		
		$leader = new Input();
		$leader->setName('user_id');
		$leader->getFilterChain()->attach($StripTags);
		$leader->setAllowEmpty(true);
		
		$DepartmentLeaderFilter = new InputFilter();
		$DepartmentLeaderFilter->add($leader);
		
		return $DepartmentLeaderFilter;
		
	}
}