<?php
namespace Application\Filter;


use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;

class HelpArticleFilter implements InputFilterAwareInterface
{
	
	// 与数据库中表字段对应
	public $id;
	
	
	public $title;
	
	
	public $hits;
	
	public $add_time;
	
	
	public $user_id;
	
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
		
	}
	public function getInputFilter()
	{
		
		
	}
}