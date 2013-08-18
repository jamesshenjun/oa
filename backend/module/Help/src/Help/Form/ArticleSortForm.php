<?php

namespace Help\Form;

use Zend\Form\Form;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Button;
use Zend\Form\Element\MultiCheckbox;

class ArticleSortForm extends Form
{
	public function __construct($name = null)
	{
		// we want to ignore the name passed
		parent::__construct ();
		
		//设置表单的一些属性
		$this->setAttribute('method','post');
		$this->setAttribute('class', 'pageForm required-validate');
		$this->setAttribute('method','post');
		$this->setAttribute('onsubmit', 'return validateCallback(this)');
		
		
		//设置文章分类的id
		$id = new Hidden();
		$id->setName('id');
		$this->add($id);
		
		
		//设置表单中文章分类的父级分类,这个不传回来，只为前台显示使用
		$parentName = new Text();
		$parentName->setName('article.parent_name');
		$parentName->setAttribute('disabled','disabled');
		$parentName->setAttribute('class', 'required');
		$this->add($parentName);
		
		$parentId = new Hidden();
		$parentId->setName('article.parent_id');
		$this->add($parentId);
		
		
		//设置表单中文章分类的名称
		$name = new Text();
		$name->setName('name');
		$name->setAttribute('class', 'required');
		$this->add($name);
		
		//设置表单中文章分类的描述
		$description = new Textarea('description');
		$description->setAttribute('style','width:80%');
		$description->setAttribute('rows','8');
		$this->add($description);
		
		
		//设置表单中文章分类的负责人的id   
		$principalUserId = new Hidden();
		$principalUserId->setName('principal.user_id');
		$this->add($principalUserId);
		
		//设置表单中文章分类的负责人的用户姓名,这个不传回来，只为前台显示使用
		$principalName = new Text('principal.user_name');
		$principalName->setAttribute('disabled','disabled');
		$principalName->setAttribute('class','required textInput');
		$this->add($principalName);
		
		
		
		$sumbit = new Button('submit');
	    $sumbit->setAttribute('type', 'submit');
	    $sumbit->setLabel('提交');
	    $this->add($sumbit);
	    
	    $close = new  Button('reset');
	    $close->setAttribute('type', 'reset');
	    $close->setAttribute('class','reset');
	    $close->setLabel('重置');
	    $this->add($close);
		
	}

}
