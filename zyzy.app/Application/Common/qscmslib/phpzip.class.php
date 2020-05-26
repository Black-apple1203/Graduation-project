<?php
namespace Common\qscmslib;
class phpzip
{
    function zip($dir, $zipfilename, $drop=false)
    {
        if (substr($dir, -1) != '/')
        {
            $dir = ($dir == '') ? './' : $dir . '/';
        }

        if (@function_exists('gzcompress'))
        {
            $curdir = getcwd();
            if (is_array($dir))
            {
                $filelist = $dir;
            }
            else
            {
                $filelist = $this -> get_filelist($dir);
            }

            if ((!empty($dir)) && (!is_array($dir))&&(file_exists($dir)))
            {
                chdir($dir);
            }
            else
            {
                chdir($curdir);
            }

            if (count($filelist) > 0)
            {
                foreach ($filelist AS $filename)
                {
                    if (is_file($filename))
                    {
                        $fd = fopen ($filename, 'rb');
                        $content = fread ($fd, filesize ($filename));
                        fclose ($fd);

                        if (is_array($dir))
                        {
                            $filename = basename($filename);
                        }
                        $this -> add_file($content, $filename);

                        if ($drop)
                        {
                            @unlink($filename);
                        }
                    }
                }
                $out = $this -> file();

                chdir($curdir);
                $fp = fopen($zipfilename, 'wb');
                fwrite($fp, $out, strlen($out));
                fclose($fp);
            }

            return 1;
        }
        else
        {
            return 0;
        }
    }

    function get_filelist($dir)
    {
        $file = array();
        $dir = rtrim($dir,'/');
        if (file_exists($dir))
        {
            $args = func_get_args();
            $pref = isset($args[1]) ? $args[1] : '';

            $dh = opendir($dir);
            while (($files = readdir($dh)) !== false)
            {
                if (($files != '.') && ($files != '..'))
                {
                    if (is_dir($dir .'/'. $files))
                    {
                        $file = array_merge($file, $this -> get_filelist($dir .'/'. $files, "$pref$files/"));
                    }
                    else
                    {
                        $file[] = $pref . $files;
                    }
                }
            }
            closedir($dh);
        }

        return $file;
    }

    var $datasec      = array();
    var $ctrl_dir     = array();
    var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
    var $old_offset   = 0;

    /**
     * Converts an Unix timestamp to a four byte DOS date and time format (date
     * in high two bytes, time in low two bytes allowing magnitude comparison).
     *
     * @param  integer  the current Unix timestamp
     *
     * @return integer  the current date in a four byte DOS format
     *
     * @access private
     */
    function unix2DosTime($unixtime = 0)
    {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980) {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        } // end if

        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
                ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    } // end of the 'unix2DosTime()' method

    /**
     * Adds "file" to archive
     *
     * @param  string   file contents
     * @param  string   name of the file in the archive (may contains the path)
     * @param  integer  the current timestamp
     *
     * @access public
     */
    function add_file($data, $name, $time = 0)
    {
        $name     = str_replace('\\', '/', $name);

        $dtime    = dechex($this->unix2DosTime($time));
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
                  . '\x' . $dtime[4] . $dtime[5]
                  . '\x' . $dtime[2] . $dtime[3]
                  . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');

        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00";            // ver needed to extract
        $fr .= "\x00\x00";            // gen purpose bit flag
        $fr .= "\x08\x00";            // compression method
        $fr .= $hexdtime;             // last mod time and date

        // "local file header" segment
        $unc_len = strlen($data);
        $crc     = crc32($data);
        $zdata   = gzcompress($data);
        $c_len   = strlen($zdata);
        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
        $fr      .= pack('V', $crc);             // crc32
        $fr      .= pack('V', $c_len);           // compressed filesize
        $fr      .= pack('V', $unc_len);         // uncompressed filesize
        $fr      .= pack('v', strlen($name));    // length of filename
        $fr      .= pack('v', 0);                // extra field length
        $fr      .= $name;

        // "file data" segment
        $fr .= $zdata;

        // "data descriptor" segment (optional but necessary if archive is not
        // served as file)
        $fr .= pack('V', $crc);                 // crc32
        $fr .= pack('V', $c_len);               // compressed filesize
        $fr .= pack('V', $unc_len);             // uncompressed filesize

        // add this entry to array
        $this -> datasec[] = $fr;
        $new_offset        = strlen(implode('', $this->datasec));

        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00";                // version made by
        $cdrec .= "\x14\x00";                // version needed to extract
        $cdrec .= "\x00\x00";                // gen purpose bit flag
        $cdrec .= "\x08\x00";                // compression method
        $cdrec .= $hexdtime;                 // last mod time & date
        $cdrec .= pack('V', $crc);           // crc32
        $cdrec .= pack('V', $c_len);         // compressed filesize
        $cdrec .= pack('V', $unc_len);       // uncompressed filesize
        $cdrec .= pack('v', strlen($name) ); // length of filename
        $cdrec .= pack('v', 0 );             // extra field length
        $cdrec .= pack('v', 0 );             // file comment length
        $cdrec .= pack('v', 0 );             // disk number start
        $cdrec .= pack('v', 0 );             // internal file attributes
        $cdrec .= pack('V', 32 );            // external file attributes - 'archive' bit set

        $cdrec .= pack('V', $this -> old_offset ); // relative offset of local header
        $this -> old_offset = $new_offset;

        $cdrec .= $name;

        // optional extra field, file comment goes here
        // save to central directory
        $this -> ctrl_dir[] = $cdrec;
    } // end of the 'add_file()' method

    /**
     * Dumps out file
     *
     * @return  string  the zipped file
     *
     * @access public
     */
    function file()
    {
        $data    = implode('', $this -> datasec);
        $ctrldir = implode('', $this -> ctrl_dir);

        return
            $data .
            $ctrldir .
            $this -> eof_ctrl_dir .
            pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries "on this disk"
            pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries overall
            pack('V', strlen($ctrldir)) .           // size of central dir
            pack('V', strlen($data)) .              // offset to start of central dir
            "\x00\x00";                             // .zip file comment length
    }
    /**********************************************************
     * 解压部分
     **********************************************************/
    // ------------------------------------------------------ //
    // ReadCentralDir($zip, $zipfile)
    // $zip是经过@fopen($zipfile, 'rb')打开的
    // $zipfile是zip文件的路径
    // ------------------------------------------------------ //
    private function ReadCentralDir($zip, $zipfile)
    {
        $size     = filesize($zipfile);
        $max_size = ($size < 277) ? $size : 277;
         
        @fseek($zip, $size - $max_size);
        $pos   = ftell($zip);
        $bytes = 0x00000000;
         
        while($pos < $size)
        {
            $byte  = @fread($zip, 1);
            $bytes = ($bytes << 8) | Ord($byte);
            $pos++;
            if($bytes == 0x504b0506){ break; }
        }
         
        $data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', fread($zip, 18));

        $centd['comment']      = ($data['comment_size'] != 0) ? fread($zip, $data['comment_size']) : '';  // 注释
        $centd['entries']      = $data['entries'];
        $centd['disk_entries'] = $data['disk_entries'];
        $centd['offset']       = $data['offset'];
        $centd['disk_start']   = $data['disk_start'];
        $centd['size']         = $data['size'];
        $centd['disk']         = $data['disk'];
        return $centd;
    }
     
     
    private function ReadCentralFileHeaders($zip)
    {
        $binary_data = fread($zip, 46);
        $header      = unpack('vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $binary_data);

        $header['filename'] = ($header['filename_len'] != 0) ? fread($zip, $header['filename_len']) : '';
        $header['extra']    = ($header['extra_len']    != 0) ? fread($zip, $header['extra_len'])    : '';
        $header['comment']  = ($header['comment_len']  != 0) ? fread($zip, $header['comment_len'])  : '';

 
        if($header['mdate'] && $header['mtime'])
        {
            $hour    = ($header['mtime']  & 0xF800) >> 11;
            $minute  = ($header['mtime']  & 0x07E0) >> 5;
            $seconde = ($header['mtime']  & 0x001F) * 2;
            $year    = (($header['mdate'] & 0xFE00) >> 9) + 1980;
            $month   = ($header['mdate']  & 0x01E0) >> 5;
            $day     = $header['mdate']   & 0x001F;
            $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
        } else {
            $header['mtime'] = time();
        }
        $header['stored_filename'] = $header['filename'];
        $header['status'] = 'ok';
        if(substr($header['filename'], -1) == '/'){ $header['external'] = 0x41FF0010; }  // 判断是否文件夹
        return $header;
    }
 
 
    private function ReadFileHeader($zip)
    {
        $binary_data = fread($zip, 30);
        $data        = unpack('vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $binary_data);
 
        $header['filename']        = fread($zip, $data['filename_len']);
        $header['extra']           = ($data['extra_len'] != 0) ? fread($zip, $data['extra_len']) : '';
        $header['compression']     = $data['compression'];
        $header['size']            = $data['size'];
        $header['compressed_size'] = $data['compressed_size'];
        $header['crc']             = $data['crc'];
        $header['flag']            = $data['flag'];
        $header['mdate']           = $data['mdate'];
        $header['mtime']           = $data['mtime'];
 
        if($header['mdate'] && $header['mtime']){
            $hour    = ($header['mtime']  & 0xF800) >> 11;
            $minute  = ($header['mtime']  & 0x07E0) >> 5;
            $seconde = ($header['mtime']  & 0x001F) * 2;
            $year    = (($header['mdate'] & 0xFE00) >> 9) + 1980;
            $month   = ($header['mdate']  & 0x01E0) >> 5;
            $day     = $header['mdate']   & 0x001F;
            $header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
        }else{
            $header['mtime'] = time();
        }
 
        $header['stored_filename'] = $header['filename'];
        $header['status']          = "ok";
        return $header;
    }
 
 
    private function ExtractFile($header, $to, $zip)
    {
        $header = $this->readfileheader($zip);
         
        if(substr($to, -1) != "/"){ $to .= "/"; }
        if(!@is_dir($to)){ @mkdir($to, 0777); }
         
        $pth = explode("/", dirname($header['filename']));
        for($i=0; isset($pth[$i]); $i++){
            if(!$pth[$i]){ continue; }
            $pthss .= $pth[$i]."/";
            if(!is_dir($to.$pthss)){ @mkdir($to.$pthss, 0777); }
        }
         
        if(!($header['external'] == 0x41FF0010) && !($header['external'] == 16))
        {
            if($header['compression'] == 0)
            {
                $fp = @fopen($to.$header['filename'], 'wb');
                if(!$fp){ return(-1); }
                $size = $header['compressed_size'];
                 
                while($size != 0)
                {
                    $read_size   = ($size < 2048 ? $size : 2048);
                    $buffer      = fread($zip, $read_size);
                    $binary_data = pack('a'.$read_size, $buffer);
                    @fwrite($fp, $binary_data, $read_size);
                    $size       -= $read_size;
                }
                fclose($fp);
                touch($to.$header['filename'], $header['mtime']);
             
            }else{
                 
                $fp = @fopen($to.$header['filename'].'.gz', 'wb');
                if(!$fp){ return(-1); }
                $binary_data = pack('va1a1Va1a1', 0x8b1f, Chr($header['compression']), Chr(0x00), time(), Chr(0x00), Chr(3));
                 
                fwrite($fp, $binary_data, 10);
                $size = $header['compressed_size'];
                 
                while($size != 0)
                {
                    $read_size   = ($size < 1024 ? $size : 1024);
                    $buffer      = fread($zip, $read_size);
                    $binary_data = pack('a'.$read_size, $buffer);
                    @fwrite($fp, $binary_data, $read_size);
                    $size       -= $read_size;
                }
                 
                $binary_data = pack('VV', $header['crc'], $header['size']);
                fwrite($fp, $binary_data, 8);
                fclose($fp);
                 
                $gzp = @gzopen($to.$header['filename'].'.gz', 'rb') or die("Cette archive est compress!");
                 
                if(!$gzp){ return(-2); }
                $fp = @fopen($to.$header['filename'], 'wb');
                if(!$fp){ return(-1); }
                $size = $header['size'];
                 
                while($size != 0)
                {
                    $read_size   = ($size < 2048 ? $size : 2048);
                    $buffer      = gzread($gzp, $read_size);
                    $binary_data = pack('a'.$read_size, $buffer);
                    @fwrite($fp, $binary_data, $read_size);
                    $size       -= $read_size;
                }
                fclose($fp); gzclose($gzp);
                 
                touch($to.$header['filename'], $header['mtime']);
                @unlink($to.$header['filename'].'.gz');
            }
        }
        return true;
    }
    public function unZip($zipfile, $to, $index = Array(-1))
    {
        $ok  = 0;
        $zip = @fopen($zipfile, 'rb');
        if(!$zip){ return(-1); }
         
        $cdir      = $this->ReadCentralDir($zip, $zipfile);
        $pos_entry = $cdir['offset'];
         
        if(!is_array($index)){ $index = array($index); }
        for($i=0; $index[$i]; $i++)
        {
            if(intval($index[$i]) != $index[$i] || $index[$i] > $cdir['entries'])
            {
                return(-1);
            }
        }
         
        for($i=0; $i<$cdir['entries']; $i++)
        {
            @fseek($zip, $pos_entry);
            $header          = $this->ReadCentralFileHeaders($zip);
            $header['index'] = $i;
            $pos_entry       = ftell($zip);
            @rewind($zip);
            fseek($zip, $header['offset']);
            if(in_array("-1", $index) || in_array($i, $index))
            {
                $stat[$header['filename']] = $this->ExtractFile($header, $to, $zip);
            }
        }
         
        fclose($zip);
        return $stat;
    }
}
?>