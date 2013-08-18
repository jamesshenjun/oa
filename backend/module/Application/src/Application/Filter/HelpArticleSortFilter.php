<?php

namespace Application\Filter;

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;


use Zend\Validator\Regex;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Db\RecordExists;
use Zend\Validator\Db\NoRecordExists;
use Zend\Validator\EmailAddress;
use Zend\Filter\StripTags;
use Zend\Filter\Int;

class HelpArticleSortFilter implements InputFilterAwareInterface
{


	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception("Not used");
	}

	public function getInputFilter()
	{
		//----------------------------第一步：新建对于文章分类的过滤和验证----------------------------//
		//新建文章分类是否为空的检测
		$DigitsNoEmpty  = new NotEmpty();
		$DigitsNoEmpty->setType(NotEmpty::STRING);
		$DigitsNoEmpty->setMessage("文章分类不能为空");
		
		//新建文章分类是否是数字的验证
		$Digits = new Digits();
		$Digits->setMessage('文章分类id必须是数字');
		
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
		$DigitsNoEmpty->setMessage("文章父分类不能为空");
		
		//新建文章分类是否是数字的验证
		$Digits = new Digits();
		$Digits->setMessage('文章父分类id必须是数字');
		
		$parentId->getValidatorChain()->addValidator($Digits);
		$parentId->getValidatorChain()->addValidator($DigitsNoEmpty);
		
		
		//----------------------------第三步：新建对于文章分类名称的过滤和验证----------------------------//
		//新建过滤条件
		$stripTags = new StripTags();
		
		//新建字符串长度的检测
		$stringLength = new StringLength();
		$stringLength->setMax('50');
		$stringLength->setMin('4');
		$stringLength->setMessage("你输入的文章分类名称小于%min% 个字符",'stringLengthTooShort');
		$stringLength->setMessage("你输入的文章分类名称大于与 %max% 个字符",'stringLengthTooLong');
		
		//新建字符串是否为空的检测
		$stringNoEmpty  = new NotEmpty();
		$stringNoEmpty->setType(NotEmpty::STRING);
		$stringNoEmpty->setMessage("文章分类名称不能为空",'isEmpty');
		
		
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
		$stringLength->setMin('20');
		$stringLength->setMessage("你输入的文章分类描述小于%min% 个字符",'stringLengthTooShort');
		$stringLength->setMessage("你输入的文章分类描述大于与 %max% 个字符",'stringLengthTooLong');
		
		//新建字符串是否为空的检测
		$stringNoEmpty  = new NotEmpty();
		$stringNoEmpty->setType(NotEmpty::STRING);
		$stringNoEmpty->setMessage("文章分类描述不能为空",'isEmpty');
		
		
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
	}
}
