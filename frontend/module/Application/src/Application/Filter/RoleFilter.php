<?php
namespace Application\Filter;

use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Filter\StripTags;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;

class RoleFilter implements InputFilterAwareInterface
{
	
	
	
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		
	}
	public function getInputFilter()
	{
		$StripTags = new StripTags();
		
		$StringLength = new StringLength();
		$StringLength->setMax('20');
		$StringLength->setMin('4');
		


		$rolename = new Input();
		$rolename->setName('name');
		$rolename->getFilterChain()->attach($StripTags);
		$rolename->getValidatorChain()->addValidator($StringLength);
		
		
		$roledescription = new Input();
		$roledescription->setName('description');
		$roledescription->getFilterChain()->attach($StripTags);
		
		
		$RoleFilter = new InputFilter();
		$RoleFilter->add($rolename);
		$RoleFilter->add($roledescription);
		
		return $RoleFilter;
		
	}
}