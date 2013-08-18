<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\File\Transfer\Adapter\Http;

use Zend\InputFilter\FileInput;

use Zend\InputFilter\InputFilter;

use Zend\Filter\File\Rename;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Validator\File\Size;
use Zend\Validator\File\Extension;

class UploadController extends AbstractActionController
{
    
	
	public function imageAction(){
		
		$config = array (
		
				'max_size' => 1024 * 2048,
				//上传文件的最大尺寸为2M
		
				'ext' => array ('jpg','jpeg','gif','png'),
				//允许的扩展名
		
				'destination' => WEB_ROOT_DISK_PATH."/".$this->getRelativeFileSavePath('article')
				//文件上传的地址
		);

		$this->uploadFile($config,'article');
		
	}//function image() end
	
	public function attachmentAction(){
		
		$config = array (
	
				'max_size' => 1024 * 2048 * 20,
				//上传文件的最大尺寸为2M
		
				'ext' => array ('zip','rar','doc','docx','ppt','pptx','xls','xlsx'),
				//允许的扩展名
		
				'destination' => WEB_ROOT_DISK_PATH."/".$this->getRelativeFileSavePath('article')
				//文件上传的地址
		);
		
		$this->uploadFile($config,'article');
		
	}
	
	private function getRelativeFileSavePath($type){
		
		$relativeFileSavePath = $type.'/'.date('Y/m/d');
		
		return $relativeFileSavePath;
		
	}//function getFileSavePath();
	
	
	private function uploadFile($config,$type){

		$request = $this->getRequest ();
		
		if (!$request->isPost ()) {
			$errorMessage =  array('err'=>'请不要尝试非常访问,谢谢您的合作','msg'=>'');
			exit(json_encode($errorMessage));
		}
			
		// 开始验证文件
		$file = $this->params()->fromFiles ( 'filedata' ); // 取得上传文件
		
		$size 		= new Size (array( 'max' => $config ['max_size'] ) ); // 允许最大尺寸
		
		$extension 	= new Extension ( $config ['ext'] ); // 允许扩展名
		
		$fileInput = new FileInput();
		$fileInput->setName('filedata');
		$fileInput->setValue($file);
		$fileInput->getValidatorChain()->addValidator($size);
		$fileInput->getValidatorChain()->addValidator($extension);
		
		if (! $fileInput->isValid ()) { // 上传验证
			$dataError = $fileInput->getMessages ();
			$errorMessage =  array('err'=>array_pop ( $dataError ),'msg'=>'');
			exit(json_encode($errorMessage));
		}
		
		if(!is_dir($config ['destination'])){
			$this->createDirectory($config ['destination']);
		}
		
		
		$oldName = $file['name'];
			
		$ext = strrchr($oldName, '.');
			
		$newName = md5($oldName.time()).$ext;
		
		$target = $config ['destination'].'/'.$newName;
		
		$resorce = $file ['tmp_name'];
		
		$options = array(
				'target'=>$target,
				'source'=>$resorce,
				'overwrite'=>true
				);
		
		$rename = new Rename('filedata');
		$rename->addFile($options);
		
		$adapter = new Http();
		$adapter->addFilter($rename);
		
		
		if ($adapter->receive ()) { // 是否上传成功！
				
			$config = $this->getServiceLocator()->get('config');
				
			$fileHttpPath = $config['image_server_http_address']."/".$this->getRelativeFileSavePath($type)."/".$newName;
				
			$errorMessage =  array('err'=>'','msg'=>$fileHttpPath);
			exit(json_encode($errorMessage));
				
		}else{
			
			$err = $adapter->getMessages();
			$errorMessage =  array('err'=>'','msg'=>$err);
			exit(json_encode($errorMessage));
			
		}
		
	}//function uploadFile() end
	
	
	private function createDirectory($path){
	//循环创建文件夹，保证路径传递进来一定有文件夹形成	
		
		if (!file_exists($path)){ 
		
			$this->createDirectory(dirname($path)); 
		
			mkdir($path, 0777); 
			
	 	} 
	
	}//function createDirectory() end
	
	
	
	
	
	
	
	
}
