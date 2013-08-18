<?php
namespace Etah\Report\Template;

use \PHPExcel_Reader_Excel2007;
use \PHPExcel_Reader_Excel5;

class Template
{
	protected $defaultWidth = 72;
	protected $defaultheight = 18;
	
	public function ReturnTemplateInfoArray($filepath,$TemplateName){
	
		$CurrentActiveSheet = $this->ReturnCurrentActiveSheet($filepath);
		$CurrentActiveSheetInfo = $this->ReturnCurrentActiveSheetInfo($CurrentActiveSheet);
	
		$InfoArray = array_merge(array("name"=>$TemplateName),$CurrentActiveSheetInfo);
	
	
		return $InfoArray;
	}//function end
	
	public function ReturnTemplateContentArray($filepath,$TemplateId){
	
		$CurrentActiveSheet = $this->ReturnCurrentActiveSheet($filepath);
	
		$CellList = $CurrentActiveSheet->getCellCollection();
	
		// 		print_r($CellList);
	
		$MergeCellRangeArray = $CurrentActiveSheet->getMergeCells();
		//得到所有的处于被合并状态的单元格的数组，这个数组不是单纯的一维数组，而是一个一个的范围的数组
		// 		print_r($MergeCellRangeArray);exit;
	
		$MergeCellList = array();
		//从范围数组到一维数组的转化,处于合并状态和被合并状态的单元格，是一个一维数组
	
		foreach($MergeCellRangeArray as $key=>$value){
			$array = $CurrentActiveSheet->getCell("A1")->extractAllCellReferencesInRange($key);
			$MergeCellList = array_merge($MergeCellList,$array);
		}//foreach end
		// 		print_r($MergeCellList);exit;
		$NormalCellList      = $this->ReturnNormalCellList($CurrentActiveSheet,$CellList, $MergeCellList);
		//得到既没有被合并也没有合并其他单元格的单元格,rowpan=1,colspan=1
	
		$ComplicatedCellList = $this->ReturnComplicatedCellList($CurrentActiveSheet,$MergeCellRangeArray);
		//得到复杂单元格，就是合并其他单元格的单元格m, 1<=rowspan 且 1<=colspan
	
// 		$MergedCellList      = $this->ReturnMergedCellList($CurrentActiveSheet,$MergeCellList);
		//得到被合并的单元格
		
		$CurrentActiveSheetArray = array();
		foreach($CellList as $key=>$address){
	
			$sort = $key + 1;
	
			$sort_array = array("id"=>$TemplateId,"sort"=>$sort);
	
			if(array_key_exists($address,$NormalCellList)){
	
				$normal_temp      = array_merge($sort_array,$NormalCellList[$address]);
	
				array_push($CurrentActiveSheetArray,$normal_temp);
			}//if end
			else if(array_key_exists($address,$ComplicatedCellList)){
	
				$complicated_temp = array_merge($sort_array,$ComplicatedCellList[$address]);
	
				array_push($CurrentActiveSheetArray,$complicated_temp);
			}
			
	
		}//foreach end
		// 		print_r($CurrentActiveSheetArray);exit;
		return $CurrentActiveSheetArray;
	}//function ReturnCurrentActiveSheetDatabaseArray() end
	
	private function ReturnMergedCellList($CurrentActiveSheet,$MergeCellList){
	
		$MergeCellRangeArray = $CurrentActiveSheet->getMergeCells();
	
		$ComplexCellList = array();
		$temp = array();
		foreach($MergeCellRangeArray as $key=>$value){
				
			$start_cell = substr($key,0,strpos($key,":"));
			array_push($temp,$start_cell);
			//$RangeList = $CurrentActiveSheet->getCell("A1")->
		}//foreach end
	
		$temp = array_diff($MergeCellList,$ComplexCellList);
	
		$MergedCellList = array();
	
		foreach($temp as $value){
				
			$array = array(
					"address"=>$value,
					"rowspan"=>0,
					"colspan"=>0,
					"type"=>"merged",
					"width" =>NULL,
					"height" =>NULL,
					"content"=>NULL
			);
				
			$MergedCellList[$value] = $array;
				
		}
	
		return $MergedCellList;
	
	}//function ReturnMergedCellList() end
	
	private function ReturnNormalCellList($CurrentActiveSheet,$CellList,$MergeCellList){
	
		$temp = array_diff($CellList,$MergeCellList);
	
		$NormalCellList =  array();
	
		foreach($temp as $key=>$value){
				
			$array = array(
					"address"=>$value,
					"rowspan"=>1,
					"colspan"=>1,
					"type"=>"normal",
					"width" =>$this->ReturnCellWidth($CurrentActiveSheet, $value),
					"height" =>$this->ReturnCellHeight($CurrentActiveSheet, $value),
					"content"=>$this->ReturnCellContent($CurrentActiveSheet,$value)
			);
				
			$NormalCellList[$value] =  $array;
		}
	
		return $NormalCellList;
	
	}//function ReturnNormalCellList() end
	
	private function ReturnComplicatedCellList($CurrentActiveSheet,$MergeCellRangeArray){
	
		$ComplexCellList = array();
	
		foreach($MergeCellRangeArray as $key=>$value){
// 			print_r($MergeCellRangeArray);exit;
			$FormatedKey = substr($key,0,strpos($key,":"));
	
			$range = $CurrentActiveSheet->getCell("A1")->getRangeBoundaries($value);
			$colspan= ord($range[1][0]) - ord($range[0][0])+1;
	
			$rowspan= $range[1][1] - $range[0][1]+1;
	
			$array =  array(
					"address"=>$FormatedKey,
					"rowspan"=>$rowspan,
					"colspan"=>$colspan,
					"type"=>"complicated",
					"width" =>$this->ReturnComplicatedWidth($CurrentActiveSheet, $FormatedKey, $colspan),
					"height" =>$this->ReturnComplicatedHeight($CurrentActiveSheet, $FormatedKey, $rowspan),
					"content"=>$this->ReturnCellContent($CurrentActiveSheet,$FormatedKey));
	
			$ComplexCellList[$FormatedKey] = $array;
		}//foreach end
	
	
		return $ComplexCellList;
	
	}//function ReturnComplexCellList() end
	
	
	private function ReturnComplicatedWidth($CurrentActiveSheet, $address, $colspan)
	{
		if ($colspan > 1)
		{
			$ComplicatedWidth = 0;
			$Column = $CurrentActiveSheet->getCell($address)->getColumn();
			for (;$colspan>=1;$colspan--){
				$width = $CurrentActiveSheet->getColumnDimension($Column)->getWidth();
				$width = $width>0?round($width*8):$this->defaultWidth;
				$ComplicatedWidth += $width;
				$Column = chr(ord($Column)+1);
			}
			return $ComplicatedWidth;
		}else 
		{
			return $this->ReturnCellWidth($CurrentActiveSheet, $address);
		}
		
	}
	
	
	private function ReturnComplicatedHeight($CurrentActiveSheet, $address, $rowspan)
	{
		if($rowspan >1 )
		{
			$ComplicatedHeight = 0;
			$Row = $CurrentActiveSheet->getCell($address)->getRow();
			for (;$rowspan>=1;$rowspan--){
				$Height =$CurrentActiveSheet->getRowDimension($Row++)->getRowHeight();
				$Height = $Height>0?round($Height/0.75):$this->defaultheight;
				$ComplicatedHeight += $Height;
			}
			return $ComplicatedHeight;
		}else{
			return $this->ReturnCellHeight($CurrentActiveSheet, $address);
		}
		
	}

	private function ReturnCellContent($CurrentActiveSheet,$address){
	
		$content = $CurrentActiveSheet->getCell($address)->getFormattedValue();
	
		return $content;
	}//function ReturnCellContent() end
	
	private function ReturnCellWidth($CurrentActiveSheet,$address)
	{
		
		$width = $CurrentActiveSheet->getColumnDimension($CurrentActiveSheet->getCell($address)->getColumn())->getWidth();
		$width = $width>0?round($width*8):$this->defaultWidth;
		return $width;
	} 
	private function ReturnCellHeight($CurrentActiveSheet,$address)
	{
		$Height = $CurrentActiveSheet->getRowDimension($CurrentActiveSheet->getCell($address)->getRow())->getRowHeight();
		$Height = $Height>0?round($Height/0.75):$this->defaultheight;
		return $Height;
	}
	

	
	
	private function ReturnCurrentActiveSheet($filepath){
	
	
		$pathinfo      = pathinfo($filepath);
		$ExtensionName = strtolower($pathinfo['extension']);
	
		if(strtolower($ExtensionName)=='xls'){
			$PHPReader = new PHPExcel_Reader_Excel5($filepath);
		}
		else if(strtolower($ExtensionName)=='xlsx'){
			$PHPReader = new PHPExcel_Reader_Excel2007($filepath);
		}
		else{
			die("在解析excel报表的时候发生了错误，错误的excel文件后缀");
		}
	
		$PHPExcel = $PHPReader->load($filepath);
		//加载文件
			
		$CurrentActiveSheet = $PHPExcel->getActiveSheet();
		//得到处于激活状态的分表
		
		return $CurrentActiveSheet;
	}//function ReturnCurrentActiveSheet() end
	
	private function ReturnCurrentActiveSheetInfo($CurrentActiveSheet){
	
		$column_count = (ord($CurrentActiveSheet->getHighestColumn())-65)+1;
			
		$row_count    = $CurrentActiveSheet->getHighestRow();
	
		$info = array(
				'column_count'=>$column_count,
				'row_count'   =>$row_count
		);
		// 	print_r($info);exit;
		return $info;
	}//function ReturnCurrentActiveSheetInfo() end
	
	
}