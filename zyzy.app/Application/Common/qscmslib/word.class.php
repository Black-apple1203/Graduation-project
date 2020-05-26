<?php
/**
 * 生成word类
 *
 * @author andery
 */
namespace Common\qscmslib;
class word
{
    public function __construct(){
        $this->start();
    }
    protected function start()
    {
        ob_start();
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:w="urn:schemas-microsoft-com:office:word"
        xmlns="http://www.w3.org/TR/REC-html40">';
    }
    public function save($path)
    {
        echo "</html>";
        $data = ob_get_contents();
        ob_end_clean();
        $this->wirtefile($path,$data);
        ob_flush();
        flush();
    }
    protected function wirtefile($fn,$data)
    {
        $fp=fopen($fn,"wb");
        fwrite($fp,$data);
        fclose($fp);
    }

}