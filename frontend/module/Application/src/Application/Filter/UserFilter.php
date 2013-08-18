<?php
namespace Application\Filter;

use Zend\Validator\Regex;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Db\RecordExists;
use Zend\Validator\Db\NoRecordExists;
use Zend\Validator\EmailAddress;
use Zend\Filter\StripTags;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\Db\Adapter\Adapter;

use Zend\ServiceManager\ServiceLocatorInterface;

class UserFilter implements InputFilterAwareInterface
{
	protected $Adapter;
	
	function __construct(Adapter $Adapter = NULL){
		
		$this->Adapter = $Adapter;
		
	}
	
	public function exchangeArray(){
		
		
		
	}
	
	
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception('这个方法没有被使用');
		
	}
	
	public function setInputFilterType(){
		
		$this->inputFilterType = 'USER_LOGIN';
		
	}
	
	public function getInputFilterType(){
	
		$this->inputFilterType = 'USER_LOGIN';
	
		return $this->inputFilterType;
		
	}
	
	
	public function getInputFilter()
	{
	    
		$StripTags = new StripTags();
		
		$StringLength = new StringLength();
		$StringLength->setMax('16');
		$StringLength->setMin('4');
		
		$notzero = new NotEmpty();
		$notzero->setType("zero");
		$notEmpty = new NotEmpty();
		
		$notEmpty->setType("string");
		

		$userId = new Input();
		$userId->setName('userId');
		$userId->setAllowEmpty(true);
		$userId->getFilterChain()->attach($StripTags);
		

		
		$usernameEmpty = clone $notEmpty;
		$usernameEmpty->setMessage("用户名不能为空",'isEmpty');
		$usernameStringLength = clone $StringLength;
		$usernameStringLength->setMessage("你输入的用户名小于%min% 个字符",'stringLengthTooShort');
		$usernameStringLength->setMessage("你输入的用户名大于与 %max% 个字符",'stringLengthTooLong');
		
		$username = new Input();
		$username->setName('username');
		$username->getFilterChain()->attach($StripTags);
		$username->getValidatorChain()->addValidator($usernameEmpty);
		$username->getValidatorChain()->addValidator($usernameStringLength);
		
		if( $this->Adapter ){
			$userIsExist = new \Zend\Validator\Db\NoRecordExists(array(
					'field'=>'username',
					'table'=>'oa_user',
					'adapter'=>$this->Adapter,
			));
			$userIsExist->setMessage('该用户名存在！');
			
			if($this->getInputFilterType()!='USER_LOGIN'){
				$username->getValidatorChain()->addValidator($userIsExist);
			}else{
				
			}
		}
		
		$realnameEmpty = clone $notEmpty;
		$realnameEmpty->setMessage("真实姓名不能为空",'isEmpty');
		$realnameStringLength = clone $StringLength;
		$realnameStringLength->setMessage("你输入的真实姓名小于%min% 个字符",'stringLengthTooShort');
		$realnameStringLength->setMessage("你输入的真实姓名大于与 %max% 个字符",'stringLengthTooLong');
		$realname = new Input();
		$realname->setName('realname');
		$realname->getFilterChain()->attach($StripTags);
		$realname->getValidatorChain()->addValidator($realnameEmpty);
		$realname->getValidatorChain()->addValidator($realnameStringLength);
		
		
		$passwordEmpty = clone $notEmpty;
		$passwordEmpty->setMessage("密码不能为空",'isEmpty');
		$passwordStringLength = clone $StringLength;
		$passwordStringLength->setMin(6);
		$passwordStringLength->setMessage("密码不能小于%min% 个字符",'stringLengthTooShort');
		$passwordStringLength->setMessage("密码不能大于与 %max% 个字符",'stringLengthTooLong');
		$password = new Input();
		$password->setName('password');
		$password->getValidatorChain()->addValidator($passwordEmpty);
		$password->getValidatorChain()->addValidator($passwordStringLength);
		

		$confirmPassword = new Input();
		$confirmPassword->setName('confirmPassword');
		$confirmPassword->getValidatorChain()->addValidator($passwordEmpty);
		$confirmPassword->getValidatorChain()->addValidator($passwordStringLength);
		
		
		$department_idEmpty = clone $notzero;
		$department_idEmpty->setMessage("请选择部门",'isEmpty');
		$department_id = new Input();
		$department_id->setName('department_id');
		$department_id->getValidatorChain()->addValidator($department_idEmpty);
		

		
		$Digits = new Digits();
		$Digits->setMessage('身份证号码必须是数字');
		$id_card_number = new Input();
		$id_card_number->setAllowEmpty(true);
		$id_card_number->setName('id_card_number');
		$id_card_number->getValidatorChain()->addValidator($Digits);


		$addressStringLength = clone $StringLength;
		$addressStringLength->setMax(50);
		$addressStringLength->setMessage("地址不能小于%min% 个字符",'stringLengthTooShort');
		$addressStringLength->setMessage("地址不能大于与 %max% 个字符",'stringLengthTooLong');
		$address = new Input();
		$address->setAllowEmpty(true);
		$address->setName('address');
		$address->getValidatorChain()->addValidator($addressStringLength);

		
		$telephoneRegex = new Regex(array('pattern' => '/\d+/'));
		$telephoneRegex->setMessage('你输入的电话号码不符合规范');
		$telephone = new Input();
		$telephone->setAllowEmpty(true);
		$telephone->setName('telephone');
		$telephone->getValidatorChain()->addValidator($telephoneRegex);

		
		
		$emailcheck = new EmailAddress();
		$emailcheck->setMessage('你输入的邮件不符合规范');
		$email = new Input();
		$email->setAllowEmpty(true);
		$email->setName('email');
		$email->getValidatorChain()->addValidator($emailcheck);

		
		
		$UserFilter = new InputFilter();
		$UserFilter->add($userId);
		$UserFilter->add($username);
		$UserFilter->add($realname);
		$UserFilter->add($password);
		$UserFilter->add($confirmPassword);
		$UserFilter->add($realname);
		$UserFilter->add($department_id);
		$UserFilter->add($id_card_number);
		$UserFilter->add($address);
		$UserFilter->add($telephone);
		$UserFilter->add($email);
		
		return $UserFilter;
		
	}
}