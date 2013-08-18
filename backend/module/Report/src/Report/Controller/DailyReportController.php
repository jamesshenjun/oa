<?php

namespace Report\Controller;


use Zend\Form\Element;
use Zend\Form\View\Helper\FormTextarea;
use Zend\Form\Form;
use Zend\Form\Element\Textarea;
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
use Report\Controller\ReportBaseController;

class DailyReportController extends ReportBaseController{

	
	/**
	 * 日报列表
	 */
	public function showDailyReportListAction() {
		
		$type = 'd';
	
		
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
	public function showDailyReportAddAction() {
		// 首先需要得到用户信息，根据用户所属部门选择模板
		$userInfo = $this->ReturnUserInfo ();
	
		// 开始约束校验
	
	
		$departmentModel = $this->departmentModel;
		$departmentInfo = $departmentModel->getDepartmentListByDepartmentId ( $userInfo ['department_id'] );
		$departmentInfo = array_pop($departmentInfo);
	
		$templateId = $departmentInfo['daily_report_template_id'];
	
		
		if (empty($templateId)) {
			$this->returnMessage ( '300', '您所在的部门没有指定模板，请与管理员联系' );
		}
		// 查询模板表信息
		$reportTemplateInfoModel = $this->reportTemplateInfoModel;
		$reportTemplateInfo = $reportTemplateInfoModel->getRowById($templateId);
		// 查询模板表内容
		$reportTemplateContentModel = $this->reportTemplateContentModel;
		$templateContent = $reportTemplateContentModel->getTemplateContent ( $templateId );
	
		$reportinitHtml = $this->ReturnreportinitHtml ( $userInfo, $templateContent );
	
		$ReturnAddForm = $this->ReturnAddForm ( $templateContent );
	
		// 自动产生标题
		$title = $userInfo ['department_name'] . $userInfo ['realname'] . date ( "Y 年 m 月 d 日" ) . '工作日报';
	
		// 视图生成
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'templateInfo', $reportTemplateInfo );
		$viewModel->setVariable ( 'title', $title );
		$viewModel->setVariable ( 'reportinitHtml', $reportinitHtml );
		$viewModel->setVariable ( 'ReturnAddForm', $ReturnAddForm );
	
		return $viewModel;
	}
	public function showDailyReportPreviewAction() {
		
		
		
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
		
		if (!in_array($ReportInfo['add_user_id'], $AllowedIds)){
			die('非礼勿视');
		}
		
		// 查询模板表信息
		// print_r($ReportInfo);
		$reportContentModel = $this->reportContentModel;
		$reportContent = $reportContentModel->getReportContent ( $report_id );
		// 查询模板表内容
		// print_r($reportContent);exit;
	
		$ReportHtml = $this->ReturnReportHtml ( $reportContent );
		//
		// print_r($ReportHtml);exit;
	
		// 视图生成
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'ReportInfo', $ReportInfo );
		$viewModel->setVariable ( 'ReportHtml', $ReportHtml );
	
		return $viewModel;
	}
	public function showDailyReportEditAction() {
	
		$config = $this->getServiceLocator()->get('config');
		$config = $config['daliy_report'];
	
		
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
	
		if (!in_array($ReportInfo['add_user_id'], $AllowedIds)){
			die('非礼勿视');
		}
		
		
		// 开始修改权限的校验
		$userInfo = $this->getLoginUser ();
		if ($userInfo->id != $ReportInfo ['add_user_id']) {
			$this->returnMessage ( '300', "目前只允许本人修改日报" );
		}
		$deadline = strtotime ( date ( 'Y-m-d' ) . $config['deadline'] );
		$startline = strtotime ( date ( 'Y-m-d' ) . $config['startline'] );
		if ($deadline < time ()) {
			$this->returnMessage ( '300', '抱歉,日报的截止时间已到' );
		}
		if (strtotime ( $ReportInfo ['add_time'] ) < $startline) {
			$this->returnMessage ( '300', '请勿修改过期的日报' );
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
	public function checkDailyReportAddAction() {
	
		
		$config = $this->getServiceLocator()->get('config');
		$config = $config['daliy_report'];
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$postObject = $request->getPost ();
			$postData = get_object_vars ( $postObject );
				
				
			$userInfo = $this->ReturnUserInfo ();
				
			// 开始约束校验
			$deadline = strtotime ( date ( 'Y-m-d' ) . $config['deadline'] );
			if ($deadline < time ()) {
				$this->returnMessage ( '300', '抱歉,日报的截止时间已到' );
			}
			$ReportInfoData = array (
					'add_user_id' => $userInfo ['id'],
					'title' => $postData ['title'],
					'type' => 'd',
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
					
				// 				// 保存成功后更新用户最后提交的周报
	
				// 				$userModel = $this->userModel;
				// 				$date = $this->getWeekdate ();
				// 				$userModel->updateUser ( array (
				// 						'latest_report' => $date ['weeknum']
				// 				), array (
				// 						'id' => $userInfo ['id']
				// 				) );
			}catch ( \Exception $e )
			{
				$dbConnection->rollback ();
				$this->returnMessage ( 300, $e->getMessage () );
			}
			$dbConnection->commit ();
			$this->returnMessage ( '200', '保存成功' );
		}else {
			$this->returnMessage ( '300', '传入数据异常' );
		}
	}
	public function checkDailyReportEditAction() {
		
		$config = $this->getServiceLocator()->get('config');
		$config = $config['daliy_report'];
	
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$postObject = $request->getPost ();
			$postData = get_object_vars ( $postObject );
			$userInfo = $this->ReturnUserInfo ();
				
			// 开始约束校验
			$date = $this->getWeekdate ();
			$deadline = strtotime ( date ( 'Y-m-d ' ) . $config['deadline'] );
			if ($deadline < time ()) {
				$this->returnMessage ( '200', '抱歉,日报的截止时间已到' );
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
	public function checkDailyReportDeleteAction() {
		
		$id = $_GET ['id'];
		$reportInfoModel = $this->reportInfoModel;
		$ReportInfo = $reportInfoModel->getReprotInfo ( $id );
		
		// 开始修改权限的校验
		$userInfo = $this->getLoginUser ();
		if ($userInfo->id != $ReportInfo ['add_user_id']) {
			$this->returnMessage ( '300', "目前只允许本人删除日报" );
		}
		
		
		try{
			$reportInfoModel->deleteReporatInfo ( $id );
		}catch ( \Exception $e){
			$this->returnMessage ( '300', $e->getMessage () );
		}
		$this->returnMessage ( '200', '删除成功' );
	}
	

	

}
