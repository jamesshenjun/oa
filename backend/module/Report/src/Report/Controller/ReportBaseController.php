<?php

namespace Report\Controller;


use Zend\Form\Element;
use Zend\Form\View\Helper\FormTextarea;
use Zend\Http\Header\From;
use Zend\Form\Form;
use Zend\Form\Element\Textarea;
use Application\Controller\BaseController;
use Zend\Authentication\AuthenticationService as AuthenticationService;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception;
use Report\Model\ReporttemplateInfoModel;
use Report\Model\ReporttemplateContentModel;
use Report\Model\ReportInfoModel;
use Report\Model\ReportContentModel;
use Report\Form\ReportSearchForm;
use Report\Form\PageForm;
use Report\Form\PerPageForm;
use Application\Factory\ServiceLocatorFactory;

use Zend\Mvc\MvcEvent;

class ReportBaseController extends BaseController{
	protected $userModel;
	protected $reportTemplateInfoModel;
	protected $reportTemplateContentModel;
	protected $departmentModel;
	protected $reportInfoModel;
	protected $reportContentModel;
	protected $departmentLeaderModel;
	protected $loginUser;
	protected $userReportModel;
	
	function __construct() {
		
		$serviceManager = ServiceLocatorFactory::getInstance ();
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'UserModel' );
		$this->getDbModel ( $serviceManager, 'Report', 'Model', 'ReportTemplateInfoModel' );
		$this->getDbModel ( $serviceManager, 'Report', 'Model', 'ReportTemplateContentModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'DepartmentModel' );
		$this->getDbModel ( $serviceManager, 'Report', 'Model', 'ReportInfoModel' );
		$this->getDbModel ( $serviceManager, 'Report', 'Model', 'ReportContentModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'DepartmentLeaderModel' );
		$this->getDbModel ( $serviceManager, 'Report', 'Model', 'UserReportModel' );
	}
	
	
	
	protected function getLeafdepartmentIds($Leafdepartments) {
		$LeafdepartmentIds = array ();
		foreach ( $Leafdepartments  as $key => $Leafdepartment ) {
			$LeafdepartmentIds [$key] = $Leafdepartment ['id'];
		}
		return $LeafdepartmentIds;
	}
	
	/**
	 *
	 * @param unknown_type $reportContent        	
	 * @return multitype:multitype:
	 */
	protected function ReturnReportHtml($reportContent) {
		$ReportHtml = array ();
		$reg = '/\d+/';
		
		foreach ( $reportContent as $cell ) {
			
			
			
			
			
			$address = $cell ['address'];
			preg_match_all ( $reg, $address, $row );
			$row = $row [0] [0];
			if (! isset ( $ReportHtml [$row] )) {
				$ReportHtml [$row] = array ();
			}
			array_push ( $ReportHtml [$row], $cell );
		}
		return $ReportHtml;
	}
	protected function FormatDepartment($departments) {
		$departmentArray = array (
				'0' => '请选择' 
		);
		foreach ( $departments as $value ) {
			$department = '';
			while (--$value['level']){
				$department .= "　　"; 
			}
			if ($value['left_number'] != $value['right_number'] - 1 ){
				$department .= '+'.$value ['name'];
			}else{
				$department .= '--'.$value ['name'];
			}
			
			$departmentArray [$value ['id']] = $department;
		}
		return $departmentArray;
	}
	protected function processreportList($ReportInfoList) {
		if ($ReportInfoList) {
			// 取得部门ID 和人员ID
			
			$userIdArr = array ();
			$departmentIdArr = array ();
			foreach ( $ReportInfoList as $key => $value ) {
				$departmentIdArr [] = $value ["department_id"];
				$userIdArr [] = $value ['add_user_id'];
			}
			
			// 取得部门列表
			$departmentList = array ();
			if ($departmentIdArr) {
				// 去重复值
				$departmentIdArr = array_unique ( $departmentIdArr );
				
				// 取得部门名称
				$departmentModel = $this->departmentModel;
				$departmentList = $departmentModel->getDepartmentListByDepartmentId ( $departmentIdArr );
			}
			
			// 取得人员列表
			$userList = array ();
			if ($userIdArr) {
				// 去重复值
				$userIdArr = array_unique ( $userIdArr );
				
				// 取得人员名称
				$userModel = $this->userModel;
				$userList = $userModel->getUserListByIds ( $userIdArr );
			}
			foreach ( $ReportInfoList as $key => $value ) {
				// 关联部门名称
				if (array_key_exists ( $value ["department_id"], $departmentList )) {
					$ReportInfoList [$key] ["department_name"] = $departmentList [$value ["department_id"]] ["name"];
				} else {
					$ReportInfoList [$key] ["department_name"] = "-";
				}
				
				// 关联用户
				if (array_key_exists ( $value ["add_user_id"], $userList )) {
					$ReportInfoList [$key] ["add_user_name"] = $userList [$value ["add_user_id"]] ["name"];
				} else {
					$ReportInfoList [$key] ["add_user_name"] = "-";
				}
				unset ( $ReportInfoList [$key] ["department_id"] );
				unset ( $ReportInfoList [$key] ["add_user_id"] );
				
				// 取得时间段
				
				$data = $this->getWeekdate ();
				$add_time = strtotime ( $ReportInfoList [$key] ["add_time"] );
				$WeekStart = strtotime ( $data ['WeekStart'] . '00:00:00'  );
				$WeekEnd = strtotime ( $data ['WeekEnd'] . '23:59:59');
				$DayStart = strtotime ( date ( 'Y-m-d' ) . '00:00:00' );
				$DayEnd = strtotime ( date ( 'Y-m-d' ) . '23:59:59' );
				
				
				if ($add_time > $DayStart && $add_time <= $DayEnd) {
					$ReportInfoList [$key] ["TimeBucket"] = '今天';
				} elseif ($add_time >= $WeekStart && $add_time <= $WeekEnd) {
					$ReportInfoList [$key] ["TimeBucket"] = '本周';
				} elseif ($add_time >= ($WeekStart - 604800) && $add_time <= ($WeekEnd - 604800)) {
					$ReportInfoList [$key] ["TimeBucket"] = '上周';
				} else {
					$ReportInfoList [$key] ["TimeBucket"] = '更早';
				}
			}
		}
		return $ReportInfoList;
	}
	protected function FormatReportListPaginationParameter($queryData) {
		// 统计总数
		$totalCount = $queryData ['totalCount'];
		$pageNumShow = ceil ( $totalCount / $queryData ['numPerPage'] );
		
		$page ['totalCount'] = $totalCount;
		$page ['pageNumShow'] = $pageNumShow;
		
		return $page;
	}
	protected function FormatReportPostdata($type) {
		// 列表查询、翻页动作时，提交的数据
		$postData = $this->getPostDataOfReportList ();
		// 查询数据
		$queryData = $postData;
		// 列表数据
		$queryData ['offset'] = ($postData ['pageNum'] - 1) * $postData ['numPerPage'];
		$queryData ['type'] = $type;
		
		return $queryData;
	}
	protected function getPostDataOfReportList() {
		$numPerPage = 20; // 每页显示多少条记录
		$pageNum = 1; // 当前在第几页
		
		$keywords = '';
		$nameStr = '';
		$departmentId = 0;
		
		$request = $this->getRequest ();
		
		if ($request->isPost ()) {
			$post = $request->getPost ();
			
			if (! empty ( $post->numPerPage )) {
				$numPerPage = ( int ) $post->numPerPage;
			}
			
			if (! empty ( $post->pageNum )) {
				$pageNum = ( int ) $post->pageNum;
			}
			
			if (! empty ( $post->keywords )) {
				$keywords = trim ( $post->keywords );
			}
			
			if (! empty ( $post->nameStr )) {
				$nameStr = trim ( $post->nameStr );
			}
			
			if (! empty ( $post->departmentId )) {
				$departmentId = ( int ) $post->departmentId;
			}
		}
		
		$postData = array (
				'numPerPage' => $numPerPage,
				'pageNum' => $pageNum,
				'keywords' => $keywords,
				'nameStr' => $nameStr,
				'departmentId' => $departmentId 
		);
		
		return $postData;
	}
	protected function ReturnreportData($ReportInfoId, $postData) {
		$ReportData = $postData ['content'];
		
		foreach ( $postData ['content'] as $key => $cell ) {
			$ReportData [$key] ['id'] = $ReportInfoId;
			if (!isset($cell['enedit'])){
				$ReportData [$key] ['enedit'] = 'F';
			}
		}
		return $ReportData;
	}
	protected function ReturnUserInfo() {
		$user = $this->getLoginUser ();
		$user_id = $user->id;
		$userModel = $this->userModel;
		$userInfo = $userModel->getUserByUserId ( $user_id );
		$departmentModel = $this->departmentModel;
		$department = $departmentModel->getDepartmentListByDepartmentId( $userInfo['department_id'] );
		$department = array_pop($department);
		$userInfo['department_name'] = $department['name'];
		return $userInfo;
	}
	protected function ReturnreportinitHtml($userInfo, $templateContent) {
		$templateHtml = array ();
		$reg = '/\d+/';
		
		foreach ( $templateContent as $cell ) {
			$address = $cell ['address'];
			preg_match_all ( $reg, $address, $row );
			$row = $row [0] [0];
			
			if (!isset($templateHtml[$row])){
				$templateHtml [$row] = array ();
			}
			switch (trim($cell ['content'])) {
				case 'name' :
					{
						$cell ['content'] = $userInfo ['realname'];
						$cell ['enedit'] = "F";
						break;
					}
				case 'time' :
					{
						$cell ['content'] = date ( "Y-m-d H:i:s" );
						$cell ['enedit'] = "F";
						break;
					}
				case 'department' :
					{
						$cell ['content'] = $userInfo ['department_name'];
						$cell ['enedit'] = "F";
						break;
					}
				case 'weekday' :
					{
						$cell ['content'] = 
						'<select>
						<option value="&nbsp;">		
							请选择	
						</option>		
						<option value="周一">		
							周一	
						</option>
						<option value="周二">		
							周二	
						</option>
						<option value="周三">		
							周三	
						</option>	
						<option value="周四">		
							周四	
						</option>	
						<option value="周五">		
							周五	
						</option>									
						<option value="周六">		
							周六	
						</option>									
						<option value="周日">		
							周日	
						</option>									
						</select>'
								;
						$cell ['enedit'] = "T";
						break;
					}	
					case 'yesorno' :
						{
							$cell ['content'] =
						'<select>
						<option value="&nbsp;">		
							请选择	
						</option>								
						<option value="否">
							否
						</option>
						<option value="是">
							是
						</option>
						</select>'
									;
						$cell ['enedit'] = "T";
									break;
						}					
					
					
				case 'edit' :
					{
						$cell ['enedit'] = "T";
						break;
					}
				default :
					$cell ['enedit'] = "F";
					break;
			}
			array_push ( $templateHtml [$row], $cell );
		}
		return $templateHtml;
	}
	/**
	 * 返回给添加界面
	 *
	 * @param unknown_type $templateContent        	
	 * @return \Zend\Form\Form
	 */
	protected function ReturnAddForm($templateContent) {
		$FormEdit = new Form ( "FormEdit" );
		
		foreach ( $templateContent as $cell ) {
			if ($cell ['content'] == 'edit') {
				// 如果是编辑则开始生成textarea
				$TextArea = new Element\Textarea ();
				$TextArea->setName ( $cell ['address'] );
				$TextArea->setAttribute ( 'style', "width:99%;height:".($cell['height']-2).'px;' );
				$FormEdit->add ( $TextArea );
			}
		}
		
		return $FormEdit;
	}
	/**
	 * 返回给编辑界面
	 */
	protected function ReturnEditForm($reportContent) {
		$FormEdit = new Form ( "FormEdit" );
		
		foreach ( $reportContent as $cell ) {
			if ($cell ['enedit'] == 'T') {
				// 如果是编辑则开始生成textarea
				$TextArea = new Element\Textarea ();
				$TextArea->setName ( $cell ['address'] );
				$TextArea->setAttribute ( 'style', "width:99%;height:".($cell['height']-2).'px;' );
				$TextArea->setValue ( $cell ['content'] );
				$FormEdit->add ( $TextArea );
			}
		}
		
		return $FormEdit;
	}
	

	/**
	 *
	 * @param unknown_type $date
	 *        	格式化的时间戳
	 * @return 返回该时间戳在本年的周数， 本周开始时间和结束时间
	 */
	protected function getWeekdate($time = NULL) {
		if (! $time) {
			$time = time ();
		}
		$weeknum = date ( "W", $time );
		$daynum = date ( "N", $time );
		$WeekStart = $time - 86400 * ($daynum - 1);
		$WeekEnd = $time + 86400 * (7 - $daynum);
		
		return array (
				'WeekStart' => date ( "Y-m-d", $WeekStart ),
				'WeekEnd' => date ( "Y-m-d", $WeekEnd ),
				'weeknum' => $weeknum 
		);
	}
	
	protected function getAllowedIds()
	{
		// 得到本角色可以访问的人员的id
		$userId = $this->getLoginUser ()->id;
		
		// 首先获得该用户是否是某部门的领导
		$departmentIds = $this->departmentLeaderModel->getLeadedDepartment ( $userId );
		
		$Leafdepartment = array ();
		foreach ( $departmentIds as $key => $departmentId ) {
		
			$temp = $this->departmentModel->getLeafDepartmentList ( $departmentId );
		
			if (! in_array ( $temp, $Leafdepartment )) {
				$Leafdepartment  = array_merge($temp,$Leafdepartment);
			}
		}
		
		$LeafdepartmentIds = $this->getLeafDepartmentIds ( $Leafdepartment );
		
		$result = $this->userModel->getUsersBydepartmentIds ( $LeafdepartmentIds );
		if ($result) {
			foreach ( $result as $key => $user ) {
				$AllowedIds [$key] = $user ['id'];
			}
		}else{
			$AllowedIds = array();
		}
		/*
		 * 这句话把当前登录人员自己加入允许列表，因为默认是可以看到自己的，除非传入了筛选结果中剔除了自己。
		* 所以这个需要加在筛选条件之前
		*/
		if (! in_array ( $userId, $AllowedIds )) {
			array_push ( $AllowedIds, $userId );
		}
		
		return $AllowedIds;
	}
	

}
