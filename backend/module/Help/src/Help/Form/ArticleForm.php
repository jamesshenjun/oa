<?php

namespace Help\Form;

use Zend\Form\Element\Textarea;
use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;


class ArticleForm extends Form
{
	public function __construct($name = null)
	{
		parent::__construct('articleAddForm');
		
		$this->setAttribute('method'	,'post');
		$this->setAttribute('class'		,'pageForm required-validate');  
		$this->setAttribute('onsubmit'	,'return validateCallback(this)');
		
		$id = new Hidden();
		$id->setName('id');
		
		
		$title =  new Text();
		$title->setName('title');
		$title->setAttribute('size',60);
		
		//文章的副标题
		$subTitle = new Text();
		$subTitle->setName('sub_title');
		$subTitle->setAttribute('size',60);
		
		//文章的分类
		//设置表单中文章表单中的文章分类,这个不传回来，只为前台显示使用
		$articleSortName = new Text();
		$articleSortName->setName('article.article_sort_name');
		$articleSortName->setAttribute('disabled','disabled');
		$articleSortName->setAttribute('class', 'required');
		$this->add($articleSortName);
		
		$articleSortId = new Hidden();
		$articleSortId->setName('article.article_sort_id');
		$this->add($articleSortId);
		
		
		//文章的关键字
		$keyword = new Text();
		$keyword->setName('keyword');
		
		//文章的关键字
		$content = new Textarea();
		$content->setName('content');
		$content->setAttribute('class','editor');
		$content->setAttribute('style','width:100%;');
		$content->setAttribute('cols',140);
		$content->setAttribute('rows',30);
		$content->setAttribute('tools', 'Source,Preview,Fullscreen,SelectAll,|,'
				                       .'Cut,Copy,Paste,Pastetext,|,'
									   .'Hr,Blocktag,Fontface,FontSize,FontColor,BackColor,Bold,Italic,Underline,Strikethrough,|,'
									   .'Align,Indent,Outdent,Removeformat,|,'
				                       .'Img,Unlink,Link,Emot,Table');
				                         
										
										
							
		
		$this->add($id);
		$this->add($title);
		$this->add($subTitle);
		$this->add($articleSortId);
		$this->add($keyword);
		$this->add($content);
		
	}//function __construct() end
	
	
}
