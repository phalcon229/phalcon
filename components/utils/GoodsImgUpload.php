<?php

namespace Components\Utils;

use Phalcon\Mvc\User\Component;

class GoodsImgUpload
{
    //public $max_size = '1000000';//设置上传文件大小
    public $max_size;//设置上传文件大小
    public $allow_types;//允许上传的文件扩展名，不同文件类型用“|”隔开
    public $errmsg = '';//错误信息
    public $save_path;//上传文件保存路径
    private $files;//提交的等待上传文件
    private $file_type = array();//文件类型
    private $ext = '';//上传文件扩展名
    private $max_width = ''; //设置缩略图图片最大宽度
    private $max_height = ''; //设置缩略图图片最大高度
    private $original_max_width = ''; //设置大图最大宽度
    private $original_max_height = ''; //设置大图片最大高度
    private $thumbName;//商品缩略图名
    private $originalName;//商品原图名

    /**
    * 构造函数，初始化类
    * @access public
    * @param string $di 容器
    * @param string $save_path 上传的目标文件夹
    */
    public function __construct($di, $allow_types = '')
    {
        $this->di=$di;
        $this->allow_types = empty($allow_types) ? 'jpg|gif|png|bmp|jpeg|JPG|GIF|PNG|BMP|JPEG' : $allow_types;
        $this->max_size = $this->di['config']['admin']['allowedFileSize'];
    }

    public function upload($files, $path, $goodsId = 0)
    {
        //获取文件后缀名
        $extName = '.' . substr($files['type'], 6);
        //处理图片命名
        $md5Time = substr(md5(uniqid(true).$goodsId), 8, 16);
        $fullFileName= $md5Time . $extName;
        $repath = rtrim($this->setDir($md5Time), '/');

        $this->save_path =__DIR__.'/../../' .ltrim($path, '/') . '/' . $repath;

        //判断是否需要创建文件夹
        if(!$this->_createdir( $this->save_path))
        {
            $this->errmsg = __DIR__;
            return false;
        }
        switch ($files['error'])
        {
            case 0 : $this->errmsg = '';
            break;
            case 1 : $this->errmsg = '文件太大';
            break;
            case 2 : $this->errmsg = '超过了选项指定的文件大小';
            break;
            case 3 : $this->errmsg = '文件只有部分被上传';
            break;
            case 4 : $this->errmsg = '没有文件被上传';
            break;
            case 5 : $this->errmsg = '上传文件大小为0';
            break;
            default : $this->errmsg = '上传文件失败！';
            break;
        }
        if($files['error'] == 0 && is_uploaded_file($files['tmp_name']))
        {
            //检测文件类型
            if($this->check_file_type($files['name']) == FALSE)
            {
                return FALSE;
            }
            //检测文件大小
            if($files['size'] > $this->max_size)
            {
                $this->errmsg = '上传文件<font color=red>'.$files['name'].'</font>太大，最大支持<font color=red>'.ceil($this->max_size/1024).'</font>kb的文件';
                return FALSE;
            }
            // 上传原图
            $fullPath = $this->save_path . '/' . $fullFileName;

            if (move_uploaded_file($files['tmp_name'], $fullPath)) {
                $type = explode('/', $path);
                return $type[1] . '/' . $repath . '/' . $fullFileName;
            } else {
                $this->errmsg = '文件上传失败！';
                return FALSE;
            }
        }
    }

    /**
    * 上传文件
    * @access public
    * @param $files 等待上传的文件(表单传来的$_FILES['tmp_name'])
    * @param string $path 文件上传的路径
    * @return boolean 返回布尔值
    */
    public function upload_file($files, $path, $originalName, $thumbName, $originalWidth,
    $originalHeight, $thumbWidth = 100, $thumbHeight = 100)
    {
        //获取并设置文件后缀名
        $extName = '.' . substr($files['type'], 6);
        $this->thumbName = $thumbName;
        $this->originalName = $originalName;
        $this->max_width = $thumbWidth;
        $this->max_height = $thumbHeight;
        $this->original_max_width = $originalWidth;
        $this->original_max_height = $originalHeight;
        //处理图片命名
        $this->md5_time= substr(md5(uniqid(true)), 8, 16);
        $this->save_re_path = $this->setDir($this->md5_time);
        $this->save_path =__DIR__.'/../../'.$path.'/'.$this->save_re_path;
        //判断是否需要创建文件夹
        if(!$this->_createdir( $this->save_path))
        {
            $this->errmsg = __DIR__;
            return false;
        }
        switch ($files['error'])
        {
            case 0 : $this->errmsg = '';
            break;
            case 1 : $this->errmsg = '文件太大';
            break;
            case 2 : $this->errmsg = '超过了选项指定的文件大小';
            break;
            case 3 : $this->errmsg = '文件只有部分被上传';
            break;
            case 4 : $this->errmsg = '没有文件被上传';
            break;
            case 5 : $this->errmsg = '上传文件大小为0';
            break;
            default : $this->errmsg = '上传文件失败！';
            break;
        }
        if($files['error'] == 0 && is_uploaded_file($files['tmp_name']))
        {
            //检测文件类型
            if($this->check_file_type($files['name']) == FALSE)
            {
                return FALSE;
            }
            //检测文件大小
            if($files['size'] > $this->max_size)
            {
                $this->errmsg = '上传文件<font color=red>'.$files['name'].'</font>太大，最大支持<font color=red>'.ceil($this->max_size/1024).'</font>kb的文件';
                return FALSE;
            }
            //缩放比例后存储
            if($this->resizeImage($files['tmp_name'], $this->original_max_width, $this->original_max_height, $this->originalName, $extName))
            {
                if($this->resizeImage($this->save_path . $this->originalName . $extName,
                    $this->max_width, $this->max_height, $this->thumbName, $extName))
                {
                    return [$this->save_re_path . $this->originalName . $extName, $this->save_re_path . $this->thumbName . $extName];
                }
                else
                    return FALSE;
            }
            else
            {
                $this->errmsg = '文件上传失败！';
                return FALSE;
            }
        }
    }

    /**
    * 检查上传文件类型
    * @access public
    * @param string $filename 等待检查的文件名
    * @return 如果检查通过返回TRUE 未通过则返回FALSE和错误消息
    */
    public function check_file_type($filename)
    {
        $ext = $this->get_file_type($filename);
        $this->ext = $ext;
        $allow_types = explode('|',$this->allow_types);//分割允许上传的文件扩展名为数组
        //检查上传文件扩展名是否在请允许上传的文件扩展名中
        if(in_array($ext, $allow_types))
        {
            return TRUE;
        }
        else
        {
            $this->errmsg = '上传文件<font color=red>'.$filename.'</font>类型错误，只支持上传<font color=red>'.str_replace('|',',',$this->allow_types).'</font>等文件类型!';
            return FALSE;
        }
    }

    /**
    * 取得文件类型
    * @access public
    * @param string $filename 要取得文件类型的目标文件名
    * @return string 文件类型
    */
    public function get_file_type($filename)
    {
        $info = pathinfo($filename);
        $ext = $info['extension'];
        return $ext;
    }

    /**
    * 设置文件上传后的保存路径
    */
    public  function setDir( $time )
    {
        $schme1 = substr($time, 0, 2);
        $schme2 = substr($time, 2, 2);

        return   $schme1.'/'.$schme2.'/';
    }

    /**
    * 设置文件上传后的保存路径
    */
    public  function getFullFileName($fileName, $time)
    {
        $schme1 = substr($time, 0, 12);

        return   $schme1. '_' . $fileName;
    }

    /**
     * [resizeImage description]
     * @param  [type] $im        [源目标图片]
     * @param  [type] $maxwidth  [最大宽度]
     * @param  [type] $maxheight [最大高度]
     * @param  [type] $name      [图片名]
     * @param  [type] $filetype  [图片类型]
     * @param  [type] $tmp_name  [上传的文件的临时路径]
     * @return [type]            [成功ｔｒｕｅ]
     */
    function resizeImage($tmp_name, $maxwidth, $maxheight, $name, $filetype)
    {
        try
        {
            $img_info= getimagesize($tmp_name);
            if(!in_array($img_info['mime'], array('image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/pjpeg', 'image/jpg', 'image/x-png')))
            {
                $this->errmsg ='只支持上传图片';
                return FALSE;
            }
            $pic_width =$img_info[0];
            $pic_height = $img_info[1];
            if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight))
            {
                $resizeheightTag =  $resizewidthTag = false;
                if($maxwidth && $pic_width>$maxwidth)
                {
                    $widthratio = $maxwidth/$pic_width;
                    $resizewidthTag = true;
                }

                if($maxheight && $pic_height>$maxheight)
                {
                    $heightratio = $maxheight/$pic_height;
                    $resizeheightTag = true;
                }

                if($resizewidthTag && $resizeheightTag)
                {
                    if($widthratio<$heightratio)
                        $ratio = $widthratio;
                    else
                        $ratio = $heightratio;
                }

                if($resizewidthTag && !$resizeheightTag)
                    $ratio = $widthratio;
                if($resizeheightTag && !$resizewidthTag)
                    $ratio = $heightratio;

                $newwidth = $pic_width * $ratio;
                $newheight = $pic_height * $ratio;
                $newim = imagecreatetruecolor($newwidth, $newheight);
                switch($img_info['mime']){
                    case "image/gif":
                        $images=imagecreatefromgif($tmp_name);
                        break;
                    case "image/pjpeg":
                    case "image/jpeg":
                    case "image/jpg":
                        $images=imagecreatefromjpeg($tmp_name);
                        break;
                    case "image/png":
                    case "image/x-png":
                        $images=imagecreatefrompng($tmp_name);
                        break;
                    case "image/bmp" :
                        $images = imageCreateFromBmp($tmp_name);
                    break;
                }
                imagecopyresampled($newim,$images,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
                $name = $this->save_path . $name . $filetype;
                switch($img_info['mime']){
                    case "image/gif":
                        imagegif($newim, $name);
                        break;
                    case "image/pjpeg":
                    case "image/jpeg":
                    case "image/jpg":
                        imagejpeg($newim, $name, 100);
                        break;
                    case "image/png":
                    case "image/x-png":
                        imagepng($newim, $name);
                        break;
                    case "image/bmp" :
                        imagebmp($newim, $name);
                    break;
                }
                imagedestroy($newim);
            }
            else
            {
                if(!copy($tmp_name, $this->save_path . $name . $filetype))
                {
                    return false;
                }
            }
            return TRUE;

        }
        catch( E $e)
        {
            return FALSE;
        }
    }

        /**
     * 创建目录
     * @param str $path 多级目录
     * @param int $mode 权限级别
     * @return boolean
     */
    public function _createdir($path, $mode = 0777)
    {
        if(!is_dir($path))
        {
            //true为可创建多级目录
            $re = mkdir($path, $mode, true);
            if($re)
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return TRUE;
        }
    }
}