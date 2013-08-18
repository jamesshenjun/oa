<?php

namespace Report\Controller;


use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Etah\Report\Template\Template as RTemplate;
use Zend\Validator\File\Upload;
use Zend\Form\Form;
use Zend\Authentication\AuthenticationService as AuthenticationService;
use Zend\View\Model\ViewModel;
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
use Report\Controller\ReportTemplateBaseController;

class DailyReportTemplateController extends ReportTemplateBaseController{
	
	

	
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~日报部分~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	/**
	 * 日报模板列表
	 */
	public function showDailyReportTemplateListAction() {
		// 处理post过来的数据
		$queryData = $this->FormatReportTemplatePostdata ( 'd' );
		// 处理分页事物
		$Parameter = $this->FormatReportTemplateListPaginationParameter ( $queryData );
		
		// 查询内容列表
		$reportTemplateInfoModel = $this->reportTemplateInfoModel;
		$templateList = $reportTemplateInfoModel->gettemplateList ( $queryData );
		
// 		print_r ( $templateList );
// 		exit ();
		$templateList = $this->processtemplateList ( $templateList );
		
		// 准备返回数据
		$tplVar = $queryData;
		$tplVar ["totalCount"] = $Parameter ['totalCount'];
		$tplVar ["pageNumShow"] = $Parameter ['pageNumShow'];
		$tplVar ["templateList"] = $templateList;
		
		// 表单生成
		$reporttemplateForm = new ReporttemplateForm ();
		$reporttemplateForm->get ( 'numPerPage' )->setValue ( $queryData ['numPerPage'] );
		$reporttemplatePageForm = new ReporttemplatePageForm ();
		$reporttemplatePageForm->get ( 'numPerPage' )->setValue ( $queryData ['numPerPage'] );
		
		// 视图生成
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'reporttemplateForm', $reporttemplateForm );
		$viewModel->setVariable ( 'reporttemplatePageForm', $reporttemplatePageForm );
		$viewModel->setVariables ( $tplVar );
		
		return $viewModel;
	}
	public function showDailyReportTemplateAddAction() {		
		$ReportTemplateAddForm = new ReporttemplateAddForm ();
		
		// 视图生成
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'ReporttemplateAddForm', $ReportTemplateAddForm );
		
		return $viewModel;
	}
	public function showDailyReportTemplatePreviewAction() {
		$tid = $_GET ['id'];
		// 查询模板表信息
		$reportTemplateContentModel = $this->reportTemplateContentModel;
		$templateContent = $reportTemplateContentModel->getTemplateContent ( $tid );
		// 查询模板表内容
		// print_r($templateContent);exit;
		
		$templateHtml = $this->ReturntemplateHtml ( $templateContent );
		//
		// print_r($templateHtml);exit;
		
		// 视图生成
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'templateHtml', $templateHtml );
		
		return $viewModel;
	}
	public function showDailyReportTemplateEditAction() {
		
		$id = $_GET ['id'];
		$reportTemplateInfoModel = $this->reportTemplateInfoModel;
		$templateInfo = $reportTemplateInfoModel->getTemplateInfo($id);
		// 查询模板表信息
		
		$reportTemplateContentModel = $this->reportTemplateContentModel;
		$templateContent = $reportTemplateContentModel->getTemplateContent ( $id );
		// 查询模板表内容
		
		// print_r($templateContent);exit;
		
		$templateHtml = $this->ReturntemplateHtml ( $templateContent );
		//
		// print_r($templateHtml);exit;
		
		// 视图生成
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'templateInfo', $templateInfo );
		$viewModel->setVariable ( 'templateHtml', $templateHtml );
		
		return $viewModel;
	
	}
	public function checkDailyReportTemplateAddAction() 
	{
		$config = $this->getServiceLocator()->get('config');
		
		$config = $config['template_upload'];
		
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			
			$post = $request->getPost ();
			// 开始验证数据
			$templateFilter = new ReporttemplateAddFilter ();
			$ReporttemplateAddFilter = $templateFilter->getInputFilter ();
			$ReporttemplateAddFilter->setData ( $post );
			if ($ReporttemplateAddFilter->isValid ()) {
				$info = $ReporttemplateAddFilter->getValues ();
			} else {
				foreach ( $ReporttemplateAddFilter->getInvalidInput () as $error ) {
					$dataError = $error->getMessages ();
					$this->returnMessage ( 300, array_pop ( $dataError ) );
				}
			}
			
			$file = $this->params()->fromFiles ( 'template_excel' ); // 取得上传文件
			
			//重命名
			$oldName = $file['name'];
			$ext = substr($oldName, strrpos($oldName,'.'));
			$newName = time().$ext;
			
			//判断路径是否存在
			if (!file_exists($config['dir'])){
					
				mkdir($config['dir'], 0777);
					
			}
			
			$target  = $config['dir'].$newName;
			
			
			$renameFilter = $templateFilter->getRenameFiler();
			
			$options = array(
				'source' => $file['tmp_name'],
				'target' => $target,
				'overwrite' => true
			);
			$renameFilter->addFile($options);
			
			$size = new Size ();
			$size->setMax($config ['max_size']);
			$size->setMessage('上传的文件不能超过 % max',size::TOO_BIG);
			
			$extension = new Extension ( $config ['ext'] ); // 允许扩展名
			
			$extension->setMessage('上传文件的后缀名只能是'.$config['ext']);
			
			$adapter = new \Zend\File\Transfer\Adapter\Http ();
			
			$adapter->setValidators ( array (
					$size,
					$extension 
			));
			
			$adapter->addFilter($renameFilter);
			
			if (! $adapter->isValid ()) { // 上传验证
				$dataError = $adapter->getMessages ();
				$this->returnMessage ( 300, array_pop ( $dataError ) );
			} else {
				
				if ($adapter->receive ()) { // 是否上传成功！
					$filepath = $target;
					
					// 准备模板信息
					$reportTemplate = new RTemplate ();
					$templateInfo = $reportTemplate->ReturnTemplateInfoArray ( $filepath, $file ['name'] );
					$templateInfo ['add_time'] = date ( "Y-m-d H:i:s" );
					$templateInfo ['add_user_id'] = $this->getLoginUser ()->id;
					$templateInfo ['name'] = $info ['name'];
					$templateInfo ['status'] = 'Y';
					$templateInfo ['type'] = 'd';
					$reportTemplateInfoModel = $this->reportTemplateInfoModel;
					
					// print_r($templateInfo);exit;
					
					// 开启事物
					$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
					$dbConnection->beginTransaction ();
					
					$AddState = $reportTemplateInfoModel->saveTemplateInfo ( $templateInfo );
					try {
						// 信息添加成功后开始添加内容
						$TemplateId = $reportTemplateInfoModel->lastInsertValue;
						
						$template_content = $reportTemplate->ReturnTemplateContentArray ( $filepath, $TemplateId );
						
						// print_r($template_content);exit;
						
						$reportTemplateContentModel = $this->reportTemplateContentModel;
						
						$reportTemplateContentModel->saveTemplateContent ( $template_content );
					} catch ( \Exception $e ) {
						$dbConnection->rollback ();
						$this->returnMessage ( 300, $e->getMessage () );
					}
					$dbConnection->commit ();
					$this->returnMessage ( 200, '添加模板成功！' );
				} else {
					$dataError = $adapter->getMessages ();
					$err = array_pop ( $dataError );
					$this->returnMessage ( 300, $err );
				}
			}
		}
	}
	public function checkDailyReportTemplateEditAction() {
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$postObject = $request->getPost ();
			$postData = get_object_vars ( $postObject );

			// 开启事物
			$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
			$dbConnection->beginTransaction ();
			// 首先删除所有之前的数据
			
			$TemplateId = $postData ['TemplateId'];
			// echo $TemplateId;exit;
			$reportTemplateInfoModel = $this->reportTemplateInfoModel;
			$reportTemplateContentModel = $this->reportTemplateContentModel;
			
			try{
				$reportTemplateContentModel->deleteTemplateContnetById ( $TemplateId );
				// 删除成功，开始重新添加数据
				// 准备数据
				$TempalteData = $this->ReturnTempalteData ( $TemplateId, $postData );
				// print_r($TempalteData);
				
				// 添加新数据
				$reportTemplateContentModel->saveTemplateContent ( $TempalteData );
			} catch ( \Exception $e ) {
				$dbConnection->rollback ();
				$this->returnMessage ( 300, $e->getMessage () );
			}
			$dbConnection->commit ();
			$this->returnMessage ( '200', '修改成功' );
		
		}
		$this->returnMessage ( '300', '没有收到数据' );
	}
	public function checkDailyReportTemplateDeleteAction() {
			$tid = $_GET ['id'];
		$reportTemplateInfoModel = $this->reportTemplateInfoModel;
		if ($reportTemplateInfoModel->deleteTemplateInfo ( $tid )) {
			$this->returnMessage ( '200', '删除成功' );
		} else {
			$this->returnMessage ( '300', '删除失败' );
		}
	}
	
	
	

}
