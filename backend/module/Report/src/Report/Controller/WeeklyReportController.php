<?php

namespace Report\Controller;


use Zend\Form\Form;
use Zend\Form\Element\Textarea;
use Zend\Authentication\AuthenticationService as AuthenticationService;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Exception;
use Report\Form\ReportSearchForm;
use Report\Form\PageForm;
use Report\Form\PerPageForm;
use Application\Factory\ServiceLocatorFactory;

use Zend\Mvc\MvcEvent;
use Report\Controller\ReportBaseController;


class WeeklyReportController extends ReportBaseController{
	
	

	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~周报部分~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	/**
	 * 周报列表
	 */
	public function showWeeklyReportListAction() {
		
		$type = 'w';
		
		$userModel = $this->userModel;
		$departmentModel = $this->departmentModel;
		$departmentLeaderModel = $this->departmentLeaderModel;
		
		// 处理提交的数据
		$queryData = $this->FormatReportPostdata ( $type );
		
		// 得到本角色可以访问的人员的id
		
		$AllowedIds = $this->getAllowedIds();
		
		//以下是筛选条件传入的结果
		
		//如果传过来的筛选条件中存在部门id 则先将部门条件取交集
		if ($queryData ['departmentId'] != 0 ){
			$searchDepartment = $departmentModel->getLeafDepartmentList ( $queryData ['departmentId'] );
			$searchDepartmentIds = $this->getLeafDepartmentIds ( $searchDepartment );
			$result1 = $userModel->getUsersBydepartmentIds ( $searchDepartmentIds );
			if ($result1) {
				foreach ( $result1 as $key => $user ) {
					$searchAllowedIds [$key] = $user ['id'];
				}
			}else{
				$searchAllowedIds = array();
			}
			$AllowedIds = array_intersect ( $AllowedIds, $searchAllowedIds );
		}
		
		// 得到提交数据中的人员搜索结果
		$nameStr = $queryData ['nameStr'];

		if (!empty($nameStr)) {
			$userList = $userModel->getUserList ( array (
					'nameStr' => $nameStr 
			) );
			if ($userList != null) {
				$idsnameStr = array();
				foreach ( $userList as $key => $user ) {
					$idsnameStr [$key] = $user ['id'];
				}
				$AllowedIds = array_intersect ( $AllowedIds, $idsnameStr );
			}
		}
		// print_r($idsnameStr);
		// 得到关键字的搜索结果
		$keywords = $queryData ['keywords'];
		if (!empty($keywords)) {
			$reportContentModel = $this->reportContentModel;
			$rid = $reportContentModel->getRepoartByKeywords ( $keywords );
		} else {
			$rid = null;
		}
		
		// 查询数据内容列表
		$reportInfoModel = $this->reportInfoModel;
		$ReportInfoList = $reportInfoModel->getReportList ( $queryData, $AllowedIds, $rid );
		$ReportInfoList = $this->processreportList ( $ReportInfoList );
		
		// 处理分页数据
		$queryData ['totalCount'] = $reportInfoModel->countReport ( $queryData, $AllowedIds, $rid );
		$Parameter = $this->FormatReportListPaginationParameter ( $queryData );
		
		// 准备返回数据
		$tplVar = $queryData;
		$tplVar ["totalCount"] = $Parameter ['totalCount'];
		$tplVar ["pageNumShow"] = $Parameter ['pageNumShow'];
		$tplVar ["reportList"] = $ReportInfoList;
		
		// 建立表单
		$ReportSearchForm = new ReportSearchForm ();
		$PageForm = new PageForm ();
		$PerPageForm = new PerPageForm ();
		
		// 取得部门名称
		$departmentModel = $this->departmentModel;
		$department = $departmentModel->getDepartmentStructure( );
		$department = $this->FormatDepartment ( $department );
		
		// 把值都放回去，为了翻页时不丢失数据
		$ReportSearchForm->get ( 'departmentId' )->setValueOptions ( $department );
		$ReportSearchForm->get ( 'departmentId' )->setValue ( $queryData ['departmentId'] );
		$ReportSearchForm->get ( 'nameStr' )->setValue ( $queryData ['nameStr'] );
		$ReportSearchForm->get ( 'keywords' )->setValue ( $queryData ['keywords'] );
		$PageForm->get ( 'nameStr' )->setValue ( $queryData ['nameStr'] );
		$PageForm->get ( 'keywords' )->setValue ( $queryData ['keywords'] );
		$PageForm->get ( 'departmentId' )->setValue ( $queryData ['departmentId'] );
		$PageForm->get ( 'numPerPage' )->setValue ( $queryData ['numPerPage'] );
		$PerPageForm->get ( 'numPerPage' )->setValue ( $queryData ['numPerPage'] );
		
		// 生成视图
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'ReportSearchForm', $ReportSearchForm );
		$viewModel->setVariable ( 'PageForm', $PageForm );
		$viewModel->setVariable ( 'PerPageForm', $PerPageForm );
		$viewModel->setVariables ( $tplVar );
		
		return $viewModel;
	}
	/**
	 * 添加周报页面
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showWeeklyReportAddAction() {
		
		// 首先需要得到用户信息，根据用户所属部门选择模板
		$userInfo = $this->ReturnUserInfo ();
		
		// 开始约束校验
		$date = $this->getWeekdate ();

		$flag = $this->userReportModel->hasWeekReported($date ['weeknum'], $userInfo['id']);
		
		if ($flag) {
			$this->returnMessage ( '300', '本周你已经提交了周报,请点击修改' );
		}
		
		$departmentModel = $this->departmentModel;
		$departmentInfo = $departmentModel->getDepartmentListByDepartmentId ( $userInfo ['department_id'] );
		$departmentInfo = array_pop($departmentInfo);
		
		$templateId = $departmentInfo['weekly_report_template_id'];
		
		if (empty($templateId)) {
			$this->returnMessage ( '300', '您所在的部门没有指定模板，请与管理员联系' );
		}
		// 查询模板表信息
		$reportTemplateInfoModel = $this->reportTemplateInfoModel;
		$reportTemplateInfo = $reportTemplateInfoModel->getRowById($templateId);
		
		
		if (sizeof($reportTemplateInfo) < 1 ) {
			$this->returnMessage ( '300', '您所在的部门没有指定模板，请与管理员联系' );
		}
		
		// 查询模板表内容
		$reportTemplateContentModel = $this->reportTemplateContentModel;
		$templateContent = $reportTemplateContentModel->getTemplateContent ( $templateId );
		
		if (sizeof($templateContent) < 1 ) {
			$this->returnMessage ( '300', '您所在的部门没有指定模板，请与管理员联系' );
		}
		
		$reportinitHtml = $this->ReturnreportinitHtml ( $userInfo, $templateContent );
		
		
		
		$ReturnAddForm = $this->ReturnAddForm ( $templateContent );
		
		// 自动产生标题
		$title = $userInfo ['department_name'] . $userInfo ['realname'] . '第' . $date ['weeknum'] . '周工作周报';
		
		// 视图生成
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'templateInfo', $reportTemplateInfo );
		$viewModel->setVariable ( 'title', $title );
		$viewModel->setVariable ( 'reportinitHtml', $reportinitHtml );
		$viewModel->setVariable ( 'ReturnAddForm', $ReturnAddForm );
		
		return $viewModel;
	}
	/**
	 * 添加周报
	 */
	public function checkWeeklyReportAddAction() {

		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$postObject = $request->getPost ();
			$postData = get_object_vars ( $postObject );
			
			$userInfo = $this->ReturnUserInfo ();
			
			// 开始约束校验
			$date = $this->getWeekdate ();
			$deadline = strtotime ( $date ['WeekEnd'] . '18:00:00' );
			// echo date('Y-m-d H:i:s',$deadline);exit;
			if ($deadline < time ()) {
				$this->returnMessage ( '300', '抱歉，目前不是周报的提交时间' );
			}
			$ReportInfoData = array (
					'add_user_id' => $userInfo ['id'],
					'title' => $postData ['title'],
					'type' => 'w',
					'row_count' => $postData['row_count'],
					'column_count' =>$postData['column_count'],
					'department_id' => $userInfo ['department_id'],
					'status' => 'Y',
					'add_time' => date ( "Y-m-d H:i:s" ) 
			);
			
			// 此处开启事物
			$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
			$dbConnection->beginTransaction ();
			try{
				$reportInfoModel = $this->reportInfoModel;
				
				$reportInfoModel->saveReportInfo ( $ReportInfoData );
				// 插入信息后，开始插入内容
				$reportContentModel = $this->reportContentModel;
				$ReportInfoId = $reportInfoModel->lastInsertValue;
				
				// 准备数据
				$ReportData = $this->ReturnreportData ( $ReportInfoId, $postData );
				
				// 保存
				$reportContentModel->saveReportContent ( $ReportData );
					
				// 保存成功后更新用户最后提交的周报
				
				$date = $this->getWeekdate ();
				
				
				$this->userReportModel->addWeeklyReport($date['weeknum'], $userInfo['id']);
				
				
				
			}catch ( \Exception $e )
			{
				$dbConnection->rollback ();
				$this->returnMessage ( 300, $e->getMessage () );
			}
			$dbConnection->commit ();
			$this->returnMessage ( '200', '保存成功' );
		}
		else {
			$this->returnMessage ( '300', '传入数据异常' );
		}
		
		
	}
	/**
	 * 查看周报
	 */
	public function showWeeklyReportPreviewAction() {
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest())
		{
			$this->returnMessage(300, '数据传入异常，请勿非法操作');
		}
		$report_id = $request->getQuery('id');
		//得到允许访问的用户的ids
		$AllowedIds= $this->getAllowedIds();
		
		
		$reportInfoModel = $this->reportInfoModel;
		$ReportInfo = $reportInfoModel->getReprotInfo ( $report_id );
		// 查询模板表信息
		

		if (!in_array($ReportInfo['add_user_id'], $AllowedIds)){
			die ('非礼勿视');
		}
		
		
		$reportContentModel = $this->reportContentModel;
		$reportContent = $reportContentModel->getReportContent ( $report_id );
		// 查询模板表内容
// 		print_r($reportContent);exit;
		
		$ReportHtml = $this->ReturnReportHtml ( $reportContent );
		
		// 视图生成
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'ReportInfo', $ReportInfo );
		$viewModel->setVariable ( 'ReportHtml', $ReportHtml );
		
		return $viewModel;
	}
	/**
	 * 编辑周报界面
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showWeeklyReportEditAction() {
		
		$request = $this->getRequest();
		if(!$request->isXmlHttpRequest())
		{
			$this->returnMessage(300, '数据传入异常，请勿非法操作');
		}
		
		//得到允许访问的用户的ids
		$AllowedIds= $this->getAllowedIds();
		
		$report_id = $request->getQuery('id');
		$reportInfoModel = $this->reportInfoModel;
		$ReportInfo = $reportInfoModel->getRowById ( $report_id );
		
		//校验访问数据的权限
		if (!in_array($ReportInfo['add_user_id'], $AllowedIds)){
				
			die('非礼勿视2');
		}
		
		// 开始修改权限的校验
		$userInfo = $this->getLoginUser ();
		if ($userInfo->id != $ReportInfo ['add_user_id']) {
			$this->returnMessage ( '300', "目前只允许本人修改周报" );
		}
		$date = $this->getWeekdate ();
		$deadline = strtotime ( $date ['WeekEnd'] . '18:00:00' );
		$startline = strtotime ( $date ['WeekStart'] );
		// echo date('Y-m-d H:i:s',$deadline);exit;
		if ($deadline < time ()) {
			$this->returnMessage ( '300', '周报的截止时间已到' );
		}
		if (strtotime ( $ReportInfo ['add_time'] ) < $startline) {
			$this->returnMessage ( '300', '请勿修改过期的周报' );
		}
		
		// 查询模板表信息
		$reportContentModel = $this->reportContentModel;
		$reportContent = $reportContentModel->getReportContent ( $report_id );
		
		$ReportHtml = $this->ReturnReportHtml ( $reportContent );
		
// 		print_r($ReportHtml);exit;
		
		$EditForm = $this->ReturnEditForm ( $reportContent );
		
		//
	
		
		// 视图生成
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'ReportInfo', $ReportInfo );
		$viewModel->setVariable ( 'reportinitHtml', $ReportHtml );
		$viewModel->setVariable ( 'EditForm', $EditForm );
		
		return $viewModel;
	}
	/**
	 * 编辑周报
	 */
	public function checkWeeklyReportEditAction() {
		
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$postObject = $request->getPost ();
			$postData = get_object_vars ( $postObject );
			$userInfo = $this->ReturnUserInfo ();
			
			// 开始约束校验
			$date = $this->getWeekdate ();
			$deadline = strtotime ( $date ['WeekEnd'] . '18:00:00' );
			$startline = strtotime ( $date ['WeekStart'] );
			// echo date('Y-m-d H:i:s',$deadline);exit;
			if ($deadline < time ()) {
				$this->returnMessage ( '300', '抱歉，目前不是周报的提交时间' );
			}
			
			// 此处开启事物
			$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
			$dbConnection->beginTransaction ();
			
			try{
				$ReportInfoId = $postData ['ReportId'];
// 				echo $ReportInfoId;exit;
				$reportInfoModel = $this->reportInfoModel;
				
				// 首先删除所有之前的数据
				$reportContentModel = $this->reportContentModel;
				
				$reportContentModel->deleteReportContentById ( $ReportInfoId );
					// 删除成功，开始重新添加数据

					// 准备数据
				$ReportData = $this->ReturnreportData ( $ReportInfoId, $postData );
				// 保存
				$reportContentModel->saveReportContent ( $ReportData );
			}catch ( \Exception $e ){
				$dbConnection->rollback ();
				$this->returnMessage ( '300', $e->getMessage () );
			}
			$dbConnection->commit ();
			$this->returnMessage ( '200', '修改成功' );
		}else{
			$this->returnMessage ( '300', '传入数据异常！' );
		}
	}
	public function checkWeeklyReportDeleteAction() {
		
		$request = $this->getRequest();
		if(!$request->isXmlHttpRequest())
		{
			$this->returnMessage(300, '数据传入异常，请勿非法操作');
		}
		$id = $request->getQuery('id');
		
		$reportInfoModel = $this->reportInfoModel;
		$ReportInfo = $reportInfoModel->getReprotInfo ( $id );
		
		// 开始修改权限的校验
		$userInfo = $this->getLoginUser ();
		if ($userInfo->id != $ReportInfo ['add_user_id']) {
			$this->returnMessage ( '300', "目前只允许本人删除周报" );
		}
		
		
		
		//删除周报时，判断一下是不是本周的周报，如果是，则需要更新用户 的周报记录表
		
		$date = $this->getWeekdate ();
		$endline = strtotime ( $date ['WeekEnd'] . '23:59:59' );
		$startline = strtotime ( $date ['WeekStart'] );
		// 此处开启事物
		$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
		$dbConnection->beginTransaction ();		
		try{
			$reportInfoModel->deleteReporatInfo ( $id );
			
			
			if (strtotime ( $ReportInfo ['add_time'] ) < $endline && strtotime ( $ReportInfo ['add_time'] ) > $startline){
					
				//清除最近提交周报记录
				$userInfo = $this->getLoginUser ();
				$userId = $userInfo->id;
				
				$this->userReportModel->removeWeeklyReport($date['weeknum'] ,$userId);
					
			}
			
		}catch ( \Exception $e){
			$dbConnection->rollback ();
			$this->returnMessage ( '300', $e->getMessage () );
		}
		
		$dbConnection->commit ();
		$this->returnMessage ( '200', '删除成功' );
	}
	
	
	

}
