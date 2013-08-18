<?php

namespace Application\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;

class MenuModel extends AbstractTableGateway
{
    protected $table = 'oa_menu';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * 根据级别返回列数据
     */
    public function getMenuDataByLevel($level)
    {
    	$select = $this->getSql()->select();
    	$select->where(array("level"=>$level))->order('id ASC');
    	
    	$result = $this->selectWith($select)->toArray();
    	return $result;    	
    }
    
    /**
     * 得到菜单列表
     */
    public function getMenuList($param)
    {
    	$select = $this->getSql()->select();
    	$select->where(array('left_number > '.$param["left_number"]));
    	$select->where(array('right_number < '.$param["right_number"]));
    	$select->order('level ASC');
    	
    	$result = $this->selectWith($select)->toArray();
    	return $result;    	
    }
    
    
    /**
     * 得到菜单列表
     */
    public function getAllMenuList()
    {
    	$select = $this->getSql()->select();
    	$select->order('left_number ASC');
    	$result = $this->selectWith($select)->toArray();
    	return $result;
    }
    
    /**
     * 根据ID返回菜单数据
     * @param int $id
     */
    public function getGroupInfoByGroupId($id)
    {
    	$select = $this->getSql()->select();
    	
    	$select->where(array("id"=>$id))->limit(1);
    	 
    	$result = $this->selectWith($select)->toArray();
    	
    	return $result[0];    	
    }
}