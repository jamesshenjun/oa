<?php

namespace Report\Controller;

use Etah\Report\Template\Template as RTemplate;
use Zend\Form\Form;
use Application\Controller\BaseController;
use Zend\Authentication\AuthenticationService as AuthenticationService;
use Zend\Db\Adapter\Exception;
use Report\Form\ReporttemplateForm;
use Report\Form\ReporttemplatePageForm;
use Report\Form\ReporttemplateAddForm;
use Zend\Validator\File\Size;
use Zend\Validator\File\Extension;
use Report\Model\ReporttemplateInfoModel;
use Report\Model\ReporttemplateContentModel;
use Application\Filter\ReporttemplateAddFilter;
use Application\Factory\ServiceLocatorFactory;

class ReportTemplateBaseController extends BaseController {
	
	protected $reportTemplateInfoModel;
	protected $reportTemplateContentModel;
	protected $userModel;
	protected $departmentModel;
	protected $loginUser;
	
	function __construct() {
		$serviceManager = ServiceLocatorFactory::getInstance ();
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'UserModel' );
		$this->getDbModel ( $serviceManager, 'Report', 'Model', 'ReportTemplateInfoModel' );
		$this->getDbModel ( $serviceManager, 'Report', 'Model', 'ReportTemplateContentModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'DepartmentModel' );
	}
	 
	protected function ReturnTempalteData($TemplateId, $postData) {
		$TemplateData = $postData ['content'];
		foreach ( $TemplateData as $key => $cell ) {
			$TemplateData [$key] ['id'] = $TemplateId;
		}
		return $TemplateData;
	}
	protected function FormatDepartment($department) {
		$departmentArray = array (
				'0' => '请选择' 
		);
		foreach ( $department as $value ) {
			$departmentArray [$value ['id']] = $value ['name'];
		}
		return $departmentArray;
	}
	protected function FormatReportTemplatePostdata($type) {
		// 列表查询、翻页动作时，提交的数据
		$postData = $this->getPostDataReportList ();
		// 查询数据
		$queryData = $postData;
		// 列表数据
		$queryData ['offset'] = ($postData ['pageNum'] - 1) * $postData ['numPerPage'];
		$queryData ['type'] = $type;
		
		return $queryData;
	}
	protected function FormatReportTemplateListPaginationParameter($queryData) {
		$reportTemplateInfoModel = $this->reportTemplateInfoModel;
		// 统计模板总数
		$totalCount = $reportTemplateInfoModel->countTemplate ( $queryData );
		$pageNumShow = ceil ( $totalCount / $queryData ['numPerPage'] );
		
		$page ['totalCount'] = $totalCount;
		$page ['pageNumShow'] = $pageNumShow;
		
		return $page;
	}
	protected function getPostDataReportList() {
		$numPerPage = 20; // 每页显示多少条记录
		$pageNum = 1; // 当前在第几页
		
		$request = $this->getRequest ();
		
		if ($request->isPost ()) {
			$post = $request->getPost ();
			
			if (! empty ( $post->numPerPage )) {
				$numPerPage = ( int ) $post->numPerPage;
			}
			
			if (! empty ( $post->pageNum )) {
				$pageNum = ( int ) $post->pageNum;
			}
		}
		
		$postData = array (
				'numPerPage' => $numPerPage,
				'pageNum' => $pageNum 
		)
		;
		
		return $postData;
	}
	protected function ReturntemplateHtml($templateContent) {
		$templateHtml = array ();
		$reg = '/\d+/';
		
		foreach ( $templateContent as $cell ) {
			$address = $cell ['address'];
			preg_match_all ( $reg, $address, $row );
			// print_r($row); echo ' : '; echo $address; echo " ";
			$row = $row [0] [0];
			if (! array_key_exists($row, $templateHtml)) {
				$templateHtml [$row] = array ();
			}
			array_push ( $templateHtml [$row], $cell );
		}
		return $templateHtml;
	}
	
	/**
	 * 联表查询数据
	 * 
	 * @param
	 *        	$templateList
	 * @return array $templateList
	 */
	protected function processtemplateList($templateList) {
		if ($templateList) {
			// 取得人员ID
			
			$userIdArr = array ();
			foreach ( $templateList as $key => $value ) {
				$userIdArr [$key] = $value ['add_user_id'];
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
			
			// 取得指定的部门id
			
			$departmentModel = $this->departmentModel;
			$deparmentList = $departmentModel->getDepartmentStructure ();
			
			$temp = array ();
			
			foreach ( $deparmentList as $value ) {
				if ($value ['weekly_report_template_id']) {
					$weekly_report_template_id = $value ['weekly_report_template_id'];
					if (array_key_exists ( $weekly_report_template_id, $temp )) {
						$temp [$weekly_report_template_id] .= ' ' . $value ['name'];
					} else {
						$temp [$weekly_report_template_id] = $value ['name'];
					}
				}
			}
			foreach ( $templateList as $key => $value ) {
				
				if (array_key_exists ( $value ['id'], $temp )) {
					$templateList [$key] ["department_name"] = $temp [$value ['id']];
				} else {
					$templateList [$key] ["department_name"] = '-';
				}
				// 关联用户
				if (array_key_exists ( $value ["add_user_id"], $userList )) {
					$templateList [$key] ["add_user_name"] = $userList [$value ["add_user_id"]] ["name"];
				} else {
					$templateList [$key] ["add_user_name"] = "-";
				}
				unset ( $templateList [$key] ["add_user_id"] );
			}
		}
		
		return $templateList;
	}
	
}
