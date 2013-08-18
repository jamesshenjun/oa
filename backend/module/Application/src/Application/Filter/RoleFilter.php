<?php
namespace Application\Filter;

use Zend\Validator\NotEmpty;
use Zend\Validator\Digits;
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
		throw new \Exception('这个方法没有被使用');
		
	}
	public function getInputFilter()
	{
		
		//----------------------------第一步：新建对于角色id的过滤和验证----------------------------//
		//新建文章分类是否为空的检测
		$DigitsNoEmpty  = new NotEmpty();
		$DigitsNoEmpty->setType(NotEmpty::STRING);
		$DigitsNoEmpty->setMessage("角色不能为空");
		
		//新建文章分类是否是数字的验证
		$Digits = new Digits();
		$Digits->setMessage('角色id必须是数字');
		
		$id = new Input();
		$id->setName('id');
		
		$id->getValidatorChain()->addValidator($DigitsNoEmpty);
		$id->getValidatorChain()->addValidator($Digits);
		
		
		//----------------------------第二步：新建对于文章父分类的过滤和验证----------------------------//
		$parentId = new Input();
		$parentId->setName('parent_id');
		
		//新建文章分类是否为空的检测
		$DigitsNoEmpty  = new NotEmpty();
		$DigitsNoEmpty->setType(NotEmpty::STRING);
		$DigitsNoEmpty->setMessage("角色父分类不能为空");
		
		//新建文章分类是否是数字的验证
		$Digits = new Digits();
		$Digits->setMessage('角色父分类id必须是数字');
		
		$parentId->getValidatorChain()->addValidator($Digits);
		$parentId->getValidatorChain()->addValidator($DigitsNoEmpty);
		
		
		//----------------------------第三步：新建对于文章分类名称的过滤和验证----------------------------//
		//新建过滤条件
		$stripTags = new StripTags();
		
		//新建字符串长度的检测
		$stringLength = new StringLength();
		$stringLength->setMax('12');
		$stringLength->setMin('4');
		$stringLength->setMessage("你输入的角色名称小于%min% 个字符",'stringLengthTooShort');
		$stringLength->setMessage("你输入的角色名称大于与 %max% 个字符",'stringLengthTooLong');
		
		//新建字符串是否为空的检测
		$stringNoEmpty  = new NotEmpty();
		$stringNoEmpty->setType(NotEmpty::STRING);
		$stringNoEmpty->setMessage("角色名称不能为空",'isEmpty');
		
		
		//新建表单输入对象
		$name = new Input();
		$name->setName('name');
		$name->getFilterChain()->attach($stripTags);
		$name->getValidatorChain()->addValidator($stringLength);
		$name->getValidatorChain()->addValidator($stringNoEmpty);
		
		//----------------------------第四步：新建对于分类描述的过滤与验证----------------------------//
		$stripTags = new StripTags();
		
		//新建字符串长度的检测
		$stringLength = new StringLength();
		$stringLength->setMax('200');
		$stringLength->setMin('10');
		$stringLength->setMessage("你输入的角色描述小于%min% 个字符",'stringLengthTooShort');
		$stringLength->setMessage("你输入的角色描述大于与 %max% 个字符",'stringLengthTooLong');
		
		//新建字符串是否为空的检测
		$stringNoEmpty  = new NotEmpty();
		$stringNoEmpty->setType(NotEmpty::STRING);
		$stringNoEmpty->setMessage("角色描述不能为空",'isEmpty');
		
		
		$description = new Input();
		$description->setName('description');
		$description->getFilterChain()->attach($stripTags);
		$description->getValidatorChain()->addValidator($stringLength);
		$description->getValidatorChain()->addValidator($stringNoEmpty);
		
		
		$ArticleFilter = new InputFilter();
		$ArticleFilter->add($id);
		$ArticleFilter->add($parentId);
		$ArticleFilter->add($name);
		$ArticleFilter->add($description);
		
		return $ArticleFilter;
		
	}//function getInputFilter() end
	
	
}//class RoleFilter() end