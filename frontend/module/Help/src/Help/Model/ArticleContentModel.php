<?php
namespace Help\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;

class ArticleContentModel extends BaseModel
{
    protected $table = 'oa_help_article_content';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    public function articleContentAdd($ArticleContent){
    	
    	if(!$this->insert($ArticleContent)){
    		
    		throw new \Exception ( "插入文章内容表时出现异常数据，请联系网站管理员！" );
    	  	
    	}
    	
    }//function articleInfoAdd() end
    
    
}