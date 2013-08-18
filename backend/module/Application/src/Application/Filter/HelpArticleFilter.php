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



class HelpArticleFilter implements InputFilterAwareInterface
{

	
	// 与数据库中表字段对应
	public $id;
	
	
	public $title;
	
	
	public $hits;
	
	public $add_time;
	
	
	public $user_id;
	
	public $article_sort_id;
	
	public $user_name;
	
	//对form提交来的数据进行验证
	protected $inputFilter;
	
	public function exchangeArray ($data)
	{
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->title = (isset($data['title'])) ? $data['title'] : null;
		$this->hits = (isset($data['hits'])) ? $data['hits'] : null;
		$this->add_time = (isset($data['add_time'])) ? $data['add_time'] : null;
		$this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
	}

	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception("Not used");
	}

	public function getInputFilter()
	{
		
		//----------------------------第一步：新建对于文章id的过滤和验证----------------------------//
		$Digits = new Digits();
		$Digits->setMessage('文章id必须是数字');
		
		//新建文章分类是否为空的检测
		$DigitsNoEmpty  = new NotEmpty();
		$DigitsNoEmpty->setType(NotEmpty::INTEGER);
		$DigitsNoEmpty->setMessage("文章id不能为空",'isEmpty');
		
		$id = new Input();
		$id->setName('id');
		$id->getValidatorChain()->addValidator($DigitsNoEmpty);
		$id->getValidatorChain()->addValidator($Digits);
		
		
		
		
		
		//----------------------------第一步：新建对于文章标题的过滤和验证----------------------------//
		//新建过滤条件
		$stripTags = new StripTags();
		
		//新建字符串长度的检测
		$stringLength = new StringLength();
		$stringLength->setMax('50');
		$stringLength->setMin('10');
		$stringLength->setMessage("你输入的文章标题小于%min% 个字符",'stringLengthTooShort');
		$stringLength->setMessage("你输入的文章标题大于与 %max% 个字符",'stringLengthTooLong');
		
		//新建字符串是否为空的检测
		$stringNoEmpty  = new NotEmpty();
		$stringNoEmpty->setType(NotEmpty::STRING);
		$stringNoEmpty->setMessage("文章标题不能为空",'isEmpty');
		
		
		//新建表单输入对象
		$title = new Input();
		$title->setName('title');
		$title->getFilterChain()->attach($stripTags);
		$title->getValidatorChain()->addValidator($stringLength);
		$title->getValidatorChain()->addValidator($stringNoEmpty);
		
		
		//----------------------------第二步：新建对于文章副标题的过滤和验证----------------------------//
		//新建过滤条件
		$stripTags = new StripTags();
		
		//新建字符串长度的检测
		$stringLength = new StringLength();
		$stringLength->setMax('50');
		$stringLength->setMin('10');
		$stringLength->setMessage("你输入的文章副标题小于%min% 个字符",'stringLengthTooShort');
		$stringLength->setMessage("你输入的文章副标题大于与 %max% 个字符",'stringLengthTooLong');
		
		
		//新建字符串是否为空的检测
		$stringNoEmpty  = new NotEmpty();
		$stringNoEmpty->setType(NotEmpty::STRING);
		$stringNoEmpty->setMessage("文章副标题不能为空",'isEmpty');
		
		//新建表单输入对象
		$subTitle = new Input();
		$subTitle->setName('sub_title');
		$subTitle->getFilterChain()->attach($stripTags);
		$subTitle->getValidatorChain()->addValidator($stringLength);
		$subTitle->getValidatorChain()->addValidator($stringNoEmpty);
		
		
		//----------------------------第三步：新建对于文章分类的过滤和验证----------------------------//
		
		$Digits = new Digits();
		$Digits->setMessage('文章分类id必须是数字');
		
		//新建文章分类是否为空的检测
		$DigitsNoEmpty  = new NotEmpty();
		$DigitsNoEmpty->setType(NotEmpty::INTEGER);
		$DigitsNoEmpty->setMessage("文章标题不能为空",'isEmpty');
		
		$articleSortId = new Input();
		$articleSortId->setName('article_sort_id');
		$articleSortId->getValidatorChain()->addValidator($Digits);
		$articleSortId->getValidatorChain()->addValidator($DigitsNoEmpty);
		
		
		//----------------------------第四步：新建对于文章关键字的过滤与验证----------------------------//
		//新建过滤条件
		$stripTags = new StripTags();
		
		//新建字符串长度的检测
		$stringLength = new StringLength();
		$stringLength->setMax('50');
		$stringLength->setMin('5');
		$stringLength->setMessage("你输入的文章关键字小于%min% 个字符",'stringLengthTooShort');
		$stringLength->setMessage("你输入的文章关键字大于与 %max% 个字符",'stringLengthTooLong');
		
		
		//新建字符串是否为空的检测
		$stringNoEmpty  = new NotEmpty();
		$stringNoEmpty->setType(NotEmpty::STRING);
		$stringNoEmpty->setMessage("文章关键字不能为空",'isEmpty');
		
		//新建表单输入对象
		$keyword = new Input();
		$keyword->setName('keyword');
		$keyword->getFilterChain()->attach($stripTags);
		$keyword->getValidatorChain()->addValidator($stringLength);
		$keyword->getValidatorChain()->addValidator($stringNoEmpty);
		
		//----------------------------第五步：新建对于文章内容的过滤与验证----------------------------//
		$stripTags = new StripTags();
		
		//新建字符串长度的检测
		$stringLength = new StringLength();
		$stringLength->setMax('10000');
		$stringLength->setMin('100');
		$stringLength->setMessage("你输入的文章内容小于%min% 个字符",'stringLengthTooShort');
		$stringLength->setMessage("你输入的文章内容大于与 %max% 个字符",'stringLengthTooLong');
		
		//新建字符串是否为空的检测
		$stringNoEmpty  = new NotEmpty();
		$stringNoEmpty->setType(NotEmpty::STRING);
		$stringNoEmpty->setMessage("文章内容不能为空",'isEmpty');
		
		
		$content = new Input();
		$content->setName('content');
		$content->getFilterChain()->attach($stripTags);
		$content->getValidatorChain()->addValidator($stringLength);
		$content->getValidatorChain()->addValidator($stringNoEmpty);
		
		
		$ArticleFilter = new InputFilter();
		$ArticleFilter->add($id);
		$ArticleFilter->add($title);
		$ArticleFilter->add($subTitle);
		$ArticleFilter->add($articleSortId);
		$ArticleFilter->add($keyword);
		$ArticleFilter->add($content);
		
		
		return $ArticleFilter;
	}
}
