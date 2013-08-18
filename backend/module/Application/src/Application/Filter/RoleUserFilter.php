<?php
namespace Application\Filter;

use Zend\Validator\NotEmpty;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;

class RoleUserFilter implements InputFilterAwareInterface
{
	
	
	
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		
	}
	public function getInputFilter()
	{

		$roleIdEmpty = new NotEmpty();
		$roleIdEmpty->setType('null');
		$roleIdEmpty->setMessage("请选择角色",'isEmpty');
		
		
		$roleId = new Input();
		$roleId->setName('roleIds');
		$roleId->getValidatorChain()->addValidator($roleIdEmpty);
		
		
		$userId = new Input();
		$userId->setName('userId');
		
		$roleUserFilter = new InputFilter();
		$roleUserFilter->add($roleId);
		$roleUserFilter->add($userId);
		
		return $roleUserFilter;
		
	}
}