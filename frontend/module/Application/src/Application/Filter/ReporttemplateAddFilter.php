<?php
namespace Application\Filter;

use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Filter\StripTags;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;

class ReporttemplateAddFilter implements InputFilterAwareInterface
{
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		
	}
	public function getInputFilter()
	{

		
		$StripTags = new StripTags();
		$StringLength = new StringLength();
		$StringLength->setMax('40');
		$StringLength->setMin('4');
		$StringLength->setMessage("你输入的模板名称过短",'stringLengthTooShort');
		$StringLength->setMessage("你输入的模板名称过长",'stringLengthTooLong');
		
		

		//模板名称的过滤和效验
		$templateName = new Input();
		$templateName->setName('name');
		$templateName->getFilterChain()->attach($StripTags);
		$templateName->getValidatorChain()->addValidator($StringLength);
		

		
		$ReporttemplateAddFilter = new InputFilter();
		$ReporttemplateAddFilter->add($templateName);
		
		return $ReporttemplateAddFilter;
		
	}
}