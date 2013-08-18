<?php

namespace Help\Form;
use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;


class ArticleListForm extends Form
{
	public function __construct($name = null)
	{
		parent::__construct();
		
		$this->setAttribute('method', 'post');
		$this->setAttribute('class','pageForm required-validate');  
		$this->setAttribute('id','pagerForm');
		$this->setAttribute('onsubmit','return navTabSearch(this)');
		
		
		//当前页面号码的隐藏域
		$CurrentPageNumber = new Hidden();
		$CurrentPageNumber->setName('pageNum');
		$CurrentPageNumber->setValue(1);
		
		//每页的数据行数
		$PageRowCount = new Hidden();
		$PageRowCount->setName('numPerPage');
		$PageRowCount->setValue(20);
		
		
		//文章ID输入文本框
		$articleId = new Text();
		$articleId->setName('id');
		
		
		//关键词输入文本框
		$keyword = new Text();
		$keyword->setName('keyword');
		
		
		//关键词输入文本框
		$author = new Text();
		$author->setName('author');
		
		//文章分类列表
		$ArticleSortId = new Select();
		$ArticleSortId->setName('article_sort_id');
		
		
		$this->add($CurrentPageNumber);
		$this->add($PageRowCount);
		$this->add($articleId);
		$this->add($keyword);
		$this->add($author);
		$this->add($ArticleSortId);
		
	}//function __construct() end
	
	
}
