<?php

namespace System\Form;


use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Element\Text;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Button;
use Zend\Form\Element\Textarea;

class RolePermissionForm extends Form
{
	public function __construct($name = null)
	{
		// we want to ignore the name passed
		parent::__construct ('RolePermissionForm');
		
		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'pageForm required-validate');
		$this->setAttribute('method','post');
		$this->setAttribute('onsubmit','return validateCallback(this)');
		
		$id = new hidden();
		$id->setName('id');
		$this->add($id);
		
		
		
		$sumbit = new Submit();
		$sumbit->setName('submit');
		$sumbit->setValue('保存修改');
	    $this->add($sumbit);
	    
	    $close = new Button();
	    $close->setName('close');
	    $close->setLabel('关闭窗口');
	    
	    $close->setAttribute('class', 'close');
	    $this->add($close);
	}

}
