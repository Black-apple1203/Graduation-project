<?php 
namespace Common\Model;
use Think\Model;
class UcConfigModel extends Model
	{
		/**
	     * 读取系统参数生成缓存文件
	     */
	    public function uc_config_cache() {
	        $res = $this->getField('id,name,value');
	        $data = '';
	        $cache_file_path =DATA_PATH."uc_config.php";
	        foreach ($res as $key=>$val) {
	        	$data .= 'define("'.strtoupper($val["name"]).'","'.$val["value"].'");';
	        }
	        $uc_config = "<?php\r\n";
	        $uc_config .= $data . ";\r\n";
	        $uc_config .= "?>";
	        if (!file_put_contents($cache_file_path, $uc_config, LOCK_EX))
	        {
	            $fp = @fopen($cache_file_path, 'wb+');
	            if (!$fp)
	            {
	                return false;
	            }
	            if (!@fwrite($fp, trim($uc_config)))
	            {
	                return false;
	            }
	            @fclose($fp);
	        }
	    }
	    // 读取邮件配置
	    public function get_cache(){
	    	$cache_file_path =DATA_PATH."uc_config.php";
	    	if(!file_exists($cache_file_path)){
	    		$this->uc_config_cache();
	    	}
	    	require_once($cache_file_path);
	    }
	    /**
	     * 后台有更新则删除缓存
	     */
	    protected function _before_write($data, $options) {
	        @unlink(DATA_PATH."uc_config.php");
	    }
	}
?>