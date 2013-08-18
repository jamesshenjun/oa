<?php
namespace Application\Plugin\Permission\Model;

use Application\Model\BaseModel;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Adapter;


class MenuModel extends BaseModel {
	
	protected $table = 'oa_menu';
	
	public function __construct(Adapter $adapter) {
		$this->adapter = $adapter;
		$this->initialize ();
	}
	
	public function getMenuItemList(){
		
		$select= $this->getSql()->select();
		
		$select->columns(array(	'id',
								'label'=>'title',
								'module_name',
								'controller_name',
								'action_name',
								'level',
								'parent_id')
						);
		
		$select->order(array('left_number'=>'desc'));
		
		return $this->selectWith($select)->toArray();
		
	}//function getNodeList() end
   
    
   
}