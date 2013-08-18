<?php
namespace Application\Model;

use Zend\Cache\Storage\ExceptionEvent;

use Zend\Db\TableGateway\TableGateway;

use Zend\Db\Sql\Where;

use Zend\Db\Sql\Expression;

class BaseModel extends TableGateway
{
	
	const INSERT_NODE   = 'insert';
	const DELETE_NODE   = 'delete';
    
	/**
	 * 根据id查询到数据库的行数
	 * @param int $id || array $id
	 * @param array $columns
	 * @param object $where
	 * @param array $order
	 * @return array || throw Exception
	 * @todo 添加对于调用这个方法的验证，检验存在不存在这个字段 并且检查id是不是主键
	 * @todo 添加对于选取字段的验证，选取的字段是不是都在这个数据表中
	 */
	
	final public function getRowById($id,$columns = null,$externalWhere = null,$order = null){
		
		$select = $this->getSql()->select();
		
		$where  = new where();
		
		if($columns!==null){
		//如果字段被设置了的话，就加载字段	
			$select->columns($columns);
		}
		
		if($externalWhere!=null){
		//如果条件被设置了的话，就把外置的条件添加到当前的查询条件中
			$where->addPredicate($externalWhere);
		}
		
		if($order!=null){
		//如果排序被设置了的话，就加载排序
			$select->order($order);
		}
		
		//根据id的类型来判断查询的选择的条件，是选用equalTo还是in
		if(is_numeric($id)){
			
			$where->equalTo('id', $id);
			
			$select->where($where);
			
			$result = $this->selectWith($select)->toArray();
			
			if($result){
				$row = $result[0];
			}
			else{
				$row = array();
			}
		}
		else if(is_array($id)){
			
			$where->in('id', $id);
			
			$select->where($where);
				
			$row = $this->selectWith($select)->toArray();
			
		}
		else{
			throw new \Exception('id参数类型不正确，请不要尝试非法输入');
		}
		
		
		return $row;
		
	}//function getRowById end
	
	
	/**
	 * 根据id删除数据库记录
	 * @param int $id || array $id
	 * @throws Exception
	 * @todo 添加对于调用这个方法的验证，检验存在不存在这个字段 并且检查id是不是主键
	 */
	final public function deleteRowById($id){
		
		$delete = $this->getSql()->delete();
		
		$where = new where();
		
		if(is_numeric($id)){
			$where->equalTo('id', $id);
		}
		else if(is_array($id)){
			$where->in('id', $id);
		}
		else{
			throw new \Exception('id参数类型不正确，请不要尝试非法输入');
		}
		
		$delete->where($where);
		
		$deleteState = $this->deleteWith($delete);
		
		if($deleteState===false){
			throw new \Exception('删除数据失败，请联系网站管理员');
		}
		
	}//function deleteRowById() end
	
	
	/**
	 * 向数据表中批量的添加数据
	 * @param array $data
	 * @throws \exception
	 * @todo 对于传入的数据做校验
	 * @todo 抛出的异常要包括数据表的表名   和 错误的数据所对应的主键，便于查找错误
	 */
	
	final public function insertAll($data){
		
		foreach($data as $set){
			
			$insertState = $this->insert($set);
			
			if($insertState==0){
				throw new \exception('向数据表中添加数据失败');
			}
		}//foreach end
		
	}//function insertAll() end
	
	
	/**
	 * 根据id更新数据库记录
	 * @param int $id || array $id
	 * @throws Exception
	 * @todo 添加对于调用这个方法的验证，检验存在不存在这个字段 并且检查id是不是主键
	 */
	final public function updateRowById($id,$data){
	
		$update = $this->getSql()->update();
	
		$where = new where();
	
		if(is_numeric($id)){
			$where->equalTo('id', $id);
		}
		else if(is_array($id)){
			$where->in('id', $id);
		}
		else{
			throw new \Exception('id参数类型不正确，请不要尝试非法输入');
		}
		
		if(!is_array($data)){
			throw new \Exception('更新数据类型不正确，请不要尝试非法输入');
		}
		
	
		$update->where($where);
		
		$update->set($data);
	
		$updateState = $this->updateWith($update);
	
		if($updateState===false){
			throw new \Exception('更新数据失败，请联系网站管理员');
		}
	
	}//function deleteRowById() end
	
	
	final public function rebuildStructureTree($id,$left_number){
	
		$right_number = $left_number  + 1;
		//右节点等于左节点加1，在不考虑子节点的情况下
	
		$select = $this->getSql()->select();
	
		$where = new Where();
	
		$where->equalTo('parent_id',$id);
		//这里只查一层节点，只得到子孙的列表
	
		$select->where($where);
	
		$ChildrenList = $this->selectWith($select);
	
		foreach($ChildrenList as $Children){
			 
			$right_number = $this->rebuildStructureTree($Children['id'],$right_number);
			 
		}//foreach end
	
		//更新数据
		$update = $this->getSql()->update();
	
		$where = new where();
	
		$where->equalTo('id',$id);
	
		$update->where($where);
	
		$update->set(array('left_number'=>$left_number,'right_number'=>$right_number));
	
		$this->updateWith($update);
	
		$right_number = $right_number + 1;
	
		return $right_number;
	
	}//function rebuildStructureTree() end
	
	
	/**
     * 根据新插入的节点左值 更新左右值树的所有符合条件的节点的左值和右值
     * 根据删除的节点左值 更新左右值树的所有符合条件的节点的左值和右值
     * @param int $LeftNumber
     * @param string $updateType
     */
	final public function updateLeftNumberAndRightNumber($left_number,$updateType){
		
		//第一步：设置左值的更新对象
		$leftNumberUpdate = $this->getSql()->update();
		
		//第二步：新建相关的表达式
		$leftNumberExpression = new Expression();
		
		if($updateType==self::INSERT_NODE){
			$leftNumberExpression->setExpression('`left_number`+2');
		}
		else if($updateType==self::DELETE_NODE){
			$leftNumberExpression->setExpression('`left_number`- 2');
		}
		
		//第三步：设置更新的数据
		$leftNumberUpdate->set(array('left_number'=>$leftNumberExpression));
		
		//第四步：设置更新的条件
		$leftNumberWhere = new Where();
		$leftNumberWhere->greaterThan('left_number',$left_number);
		
		$leftNumberUpdate->where($leftNumberWhere);
		
		$leftNumberUpdateState = $this->updateWith($leftNumberUpdate);
		
		if($leftNumberUpdateState===false){
			throw new \Exception('更新数据表的左值失败');
		}
		
		
		//第五步：设置右值的更新对象
		$rightNumberUpdate = $this->getSql()->update();
		
		//第六步：新建相关的表达式
		$rightNumberExpression = new Expression();
		
		if($updateType==self::INSERT_NODE){
			$rightNumberExpression->setExpression('`right_number`+2');
		}
		else if($updateType==self::DELETE_NODE){
			$rightNumberExpression->setExpression('`right_number`- 2');
		}
		
		//第七步：设置更新的数据
		$rightNumberUpdate->set(array('right_number'=>$rightNumberExpression));
		
		//第八步：设置更新的条件，注意这里要添加一个等于好
		$rightNumberWhere = new Where();
		$rightNumberWhere->greaterThanOrEqualTo('right_number',$left_number);
		
		$rightNumberUpdate->where($rightNumberWhere);
		
		$rightNumberUpdateState = $this->updateWith($rightNumberUpdate);
		
		if($rightNumberUpdateState===false){
			throw new \Exception('更新数据表的右值失败');
		}
		
	}//function updateLeftNumberAndRightNumber() end
	
	
	/**
	 * @param int $id
	 * @param boolean $includeSelf
	 * @param int $level;
	 * @return array;
	 * @todo 对于数据表中是否有左右值和层级关系的检查
	 */
	
	final public function getAncestorById($id,$columns=null,$level=null,$includeSelf = true){
		
		if(!is_numeric($id)){
			throw new \Exception('传入的参数类型错误，不是整型数据');
		}
		
		$row = $this->getRowById($id);
		//得到自身的相关信息
		
		if(sizeof($row)==0){
			return array();
		}
		
		$select = $this->getSql()->select();
		
		if($columns!==null){
			$select->columns($columns);
		}
		
		$where = new where();
		
		if($includeSelf===true){
			
			$where->lessThanOrEqualTo('left_number',$row['left_number']);
			
			$where->greaterThanOrEqualTo('right_number', $row['right_number']);
			
		}
		else if($includeSelf===false){
			
			$where->lessThan('left_number',$row['left_number']);
				
			$where->greaterThan('right_number', $row['right_number']);
			
		}
		
		if($level!==null){
			
			$where->greaterThanOrEqualTo('level',2);
			
		}
		
		$select->where($where);
		
		$select->order(array('left_number'=>'ASC'));
		
		$result = $this->selectWith($select)->toArray();
		
		return $result;
		
	}//function getAncestorById() end
	
	
	/**
	 * 根据id得到子孙节点的列表
	 * @param int $id
	 * @param array $columns
	 * @return array;
	 * @todo 对于数据表中是否有左右值和层级关系的检查
	 */
	
	final public function getDescendantById($id,$columns=null,$includeSelf = true){
		
		if(!is_numeric($id)){
			throw new \Exception('传入的参数类型错误，不是整型数据');
		}
		
		$row = $this->getRowById($id);
		//得到自身的相关信息
		
		if(sizeof($row)==0){
			return array();
		}
		
		$select = $this->getSql()->select();
		
		if($columns!==null){
			$select->columns($columns);
		}
		
		
		$where = new where();
		
		if($includeSelf===true){
			
			$where->greaterThanOrEqualTo('left_number',$row['left_number']);
			
			$where->lessThanOrEqualTo('right_number', $row['right_number']);
			
		}
		else if($includeSelf===false){
			
			$where->greaterThan('left_number',$row['left_number']);
				
			$where->lessThan('right_number', $row['right_number']);
			
		}
		
		$select->where($where);
		
		$result = $this->selectWith($select)->toArray();
		
		return $result;
		
	}//function getDescendantById() end
    
    
    
    
    
}//class BaseModel() end