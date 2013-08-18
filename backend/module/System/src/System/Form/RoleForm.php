<?php

namespace System\Form;


use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Element\Text;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Button;
use Zend\Form\Element\Textarea;

class RoleForm extends Form
{
	public function __construct($name = null)
	{
		// we want to ignore the name passed
		parent::__construct ('RoleForm');
		
		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'pageForm required-validate');
		$this->setAttribute('method','post');
		$this->setAttribute('onsubmit','return validateCallback(this)');
		
		
		//设置表单中文章分类的父级分类,这个不传回来，只为前台显示使用
		$parentName = new Text();
		$parentName->setName('role.parent_name');
		$parentName->setAttribute('disabled','disabled');
		$parentName->setAttribute('class', 'required');
		$this->add($parentName);
		
		$parentId = new Hidden();
		$parentId->setName('role.parent_id');
		$this->add($parentId);
		
		
		//设置角色的id
		$id   = new Hidden('id');
		$this->add($id);
		
		//设置角色的名称
		$name = new Text('name');
		$name->setAttribute('maxlength', '150');
		$name->setAttribute('size', '28');
		$name->setAttribute('class', 'required textInput');
		$this->add($name);
		
		//设置角色的描述
		$description = new Textarea('description');
		$description->setAttribute('style','width:80%');
		$description->setAttribute('rows','8');
		$this->add($description);
		
		
		$sumbit = new Submit();
		$sumbit->setName('submit');
		$sumbit->setValue('提交');
	    $this->add($sumbit);
	    
	    $close = new Button();
	    $close->setName('close');
	    $close->setLabel('关闭');
	    
	    $close->setAttribute('class', 'close');
	    $this->add($close);
	}

}
