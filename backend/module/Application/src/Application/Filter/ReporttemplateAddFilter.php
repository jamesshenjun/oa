<?php
namespace Application\Filter;

use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Filter\StripTags;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Filter\File\Rename;

use Zend\InputFilter\FileInput;
use Zend\Validator\File\Extension as Exception;

class ReporttemplateAddFilter implements InputFilterAwareInterface
{
	
	protected $inputFilter;
	protected $renameFiler;
	
	
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception("Not used");
	}
	
	public function getInputFilter()
	{
		if (!$this->inputFilter) {
		
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
			
			
// 			$ext = new Exception();
// 			$ext->setExtension('rar');
			
// 			$file = new FileInput();
// 			$file->setName('template_excel');
			
// 			$file->getValidatorChain()->addValidator($ext);
			
	
			
			$ReporttemplateAddFilter = new InputFilter();
			$ReporttemplateAddFilter->add($templateName);
			
			return $ReporttemplateAddFilter;
			
		$this->inputFilter = $ReporttemplateAddFilter;
		}
		
		return $this->inputFilter;
	}
	
	
	public function getRenameFiler()
	{
		if(!$this->renameFiler){
				
			$renameFiler = new Rename('file');
				
	
			$this->renameFiler = $renameFiler;
				
		}
	
		return $this->renameFiler;
	}
	
	
	
	
	
}