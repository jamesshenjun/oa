<?php

namespace System\Form;


use Zend\Form\Form;
use Zend\Form\Element;

class UserForm extends Form
{
	public function __construct($name = null)
	{
		// we want to ignore the name passed
		parent::__construct ('Pageform');
		
		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'pageForm required-validate');
		$this->setAttribute('method', 'post');
		$this->setAttribute('onsubmit', 'return validateCallback(this)');
		
		$userId   = new Element\Hidden('userId');
		$this->add($userId);
		
		$username = new Element\Text('username');
		$username->setAttribute('maxlength', '150');
		$username->setAttribute('size', '28');
		$username->setAttribute('class', 'required textInput');
		$this->add($username);
		
		$realname = new Element\Text('realname');
		$realname->setAttribute('maxlength', '150');
		$realname->setAttribute('size', '28');
		$realname->setAttribute('class', 'required textInput');
		$this->add($realname);
		
		$password = new Element\Password('password');
		$password->setAttribute('maxlength', '150');
		$password->setAttribute('size', '28');
		$password->setAttribute('class', 'required textInput');
		$this->add($password);
		
		$confirmPassword = new Element\Password('confirmPassword');
		$confirmPassword->setAttribute('maxlength', '150');
		$confirmPassword->setAttribute('size', '28');
		$confirmPassword->setAttribute('class', 'required textInput');
		$this->add($confirmPassword);
		
		
		$roleId = new Element\MultiCheckbox('roleIds');
		$roleId->setLabel('角色选择');
		$this->add($roleId);
		
		$department_id = new Element\Select('department_id');
		$department_id->setAttribute('id', 'department_id');
		$department_id->setAttribute('class', 'required textInput');
		$department_id->setAttribute('target', 'unlimitedCombox');
		$this->add($department_id);
		
		
	    $id_card_number = new Element\Text('id_card_number');
	    $id_card_number->setAttribute('maxlength', '150');
	    $id_card_number->setAttribute('size', '28');
	    $this->add($id_card_number);
	    
	    $address = new Element\Text('address');
	    $address->setAttribute('maxlength', '150');
	    $address->setAttribute('size', '28');
	    $this->add($address);
	    
	    $telephone = new Element\Text('telephone');
	    $telephone->setAttribute('maxlength', '150');
	    $telephone->setAttribute('size', '28');
	    $telephone->setAttribute('class', 'phone');
	    $this->add($telephone);
	   
	    
	    $email = new Element\Text('email');
	    $email->setAttribute('maxlength', '150');
	    $email->setAttribute('size', '28');
	    $email->setAttribute('class', 'email');
	    $this->add($email);
	    
	    
	    $sumbit = new Element\Button('submit');
	    $sumbit->setAttribute('type', 'submit');
	    $sumbit->setLabel('提交');
	    $this->add($sumbit);
	    
	    $close = new Element\Button('close');
	    $close->setAttribute('type', 'button');
	    $close->setAttribute('class', 'close');
	    $close->setLabel('关闭');
	    $this->add($close);
		
		
	}

}
