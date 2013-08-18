<?php
namespace Application\Plugin\Permission\Model;

use Application\Model\BaseModel;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Adapter;


class NodeModel extends BaseModel {
	
	protected $table = 'oa_node';
	
	public function __construct(Adapter $adapter) {
		$this->adapter = $adapter;
		$this->initialize ();
	}
	
	public function getNodeList(){
		
		$select= $this->getSql()->select();
		
		$select->columns(array('id','label'=>'title','name','level','parent_id'));
		
		$select->order(array('left_number'=>'desc'));
		
		$where =  new Where();
		
		$where->equalTo('master_id',0);
		
		$select->where($where);
		
		return $this->selectWith($select)->toArray();
		
	}//function getNodeList() end
   
    
   
}