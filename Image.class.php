<?php
  //图片处理
  class Image{
    private $thumb_width;
    private $thumb_height;
    public $errorInfo;
    private $image_type = array(
      'jpg' => 'jpeg',
      'png' => 'png',
      'gif' => 'gif',
      'jpeg' => 'jpeg',
    );
    
    //初始化属性
    public function __construct($width = '',$height = ''){
      //判读用户是否传入了参数
      $this->thumb_width = emtpy($width) ? $GLOBALS['config']['goods_img_thumb_width'] : $width;
      $this->thumb_height = empty($height) ? $GLOBALS['config']['goods_img_thumb_height'] : $height;
    }
    
    //实现创建缩略图的方法
		/*
		 * 根据图片制作缩略图
		 * @param1 string $file,缩略图的原图资源
		 * @param2 string $path,path of uploaded file
		 * @return mixed,成功返回缩略图路径，失败返回false
		 */
    public function createThumb($file,$path){
      if(!$extension = $this->checkFile($file)){
        return false;
      }
      
      //缩略图制作
			//1.	获取原图的资源
			//知道使用哪个函数 //类似imagecreatefromjpeg
			$imagecreate = 'imagecreatefrom'.$this->image_type[$extension];
			$imagesave = 'image'.$this->image_type[$extension];
			
			//利用可变函数获取图片资源
			$src = @$imagecreate($file);
			
			//创建缩略图资源
			$dst = imagecreatetruecolor($this->thumb_width,$this->thumb_height);
			
			//填充背景色
			$dst_dg = imagecolorallocate($dst,255,255,255);
			
			//获取图片信息
			$fileinfo = getimagesize($file);
			
			//求出原图的宽高比和缩略图的宽高比(浮点数一般不用于比较大小)
			$src_cmp = $fileinfo[0] / $fileinfo[1];
			$dst_cmp = $this->thumb_width / $this->thumb_height;
			
			//比较宽高比,确定原图在缩略图中应该实际占用的宽和高
      if($src_cmp > $dst_cmp){
        $width = $this->thumb_width;
        $height = floor($width / $src_cmp);
      }else{
        $height = $this->thumb_height;
        $width = floor($height * $src_cmp);
      }
      
      //求出缩略图中原始图的起始位置
      $dst_x = ceil(($this->thumb_width - $width) / 2);
      $dst_y = ceil(($this->thumb_height - $height) / 2);
      
      //2.	采样和赋值(修改图片资源粘贴过程中原图所占的宽和高)
      if(imagecopyresampled($dst,$src,$dst_x,$dst_y,0,0,$width,$height,$fileinfo[0],$fileinfo[1])){
        //success ,save picture and get the picture's name
        $name = 'thumb_'.basename($file);
        $res = $imagesave($dst,$path.'/'.$name);
        
        //destroy picture resource
        imagedestroy($dst);
        
        if($res){
          //saved
          return $path.'/'.$name;
        }else{
          //fail
          $this->errorInfo = 'fail to save picture!';
          return false;
        }
      }else{
        $this->errorInfo = 'failure to get sample of thumb picture';
        imagedestroy($dst);
        return false;
      }
    }
    
    /*
		 * 创建水印
		 * @param1 string $file,要创建水印的图片
		 * @param2 int $posistion,水印图的位置，默认是0，表示右下角
		 * @param3 int $pct,水印图的透明度,默认为30,比较透明点
		 * @param4 string $watermark,默认值为空,读配置文件
		 * @param5 string $path,saved path
		 * @return string $path,水印图的路径
		 */
		 //把常用的参数尽量放在前面
		 public function createWatermark($file,$position = 5,$pct = 30, $watermark = '',$path){
		   //判断目标文件是否正确
		   if(!$extension = $this->checkFile($file)) return false;
		   
		   //确定水印图片
		   if(!$watermark){
		     //用户没有传入水印图片，使用默认的水印图片
		     $watermark = $GLOBALS['config']['goods_img_water'];
		   }
		   
		   //判断水印图片
		   if(!$water_ext = $this->checkFile($watermark)){
		     $this->errorInfo = '水印图片资源不存在！';
		     return false;
		   }
		   
		   //制作水印
			//1.	确定图片资源获取的函数
			$dstcreate = 'imagecreatefrom'.$this->image_type[$extension];
			$watercreate = 'imagecreatefrom'.$this->image_type[$water_ext];
			$dst_save = 'image'.$this->image_type[$extension];
			
			//2.	获取图片资源,利用可变函数来获取图片资源
			$dst = @$dstcreate($file);
			$wat = @$watercreate($watermark);
			
			//3.	获取图片的信息
			$dstinfo = getimagesize($file);
			$watinfo = getimagesize($watermark);
			
			//4.	计算水印在原图的坐标
			//通过用户选定位置，计算水印的位置
			switch($position){
			  case 1:
			    //左上角
			    $start_x = 0;
			    $start_y = 0;
			    break;
			  case 2:
			    //右上角
			    $start_x = $dstinfo[0] - $watinfo[0];
			    $start_y = 0;
			    break;
			  case 3:
			    //中间位置
			    $start_x = floor(($dstinfo[0] - $watinfo[0]) / 2);
			    $start_y = floor(($dstinfo[1] - $watinfo[1]) / 2);
			    break;
			  case 4:
			    //左下角
			    $start_x = 0;
			    $start_y = $dstinfo[1] - $watinfo[1];
			    break;
			  case 5:
			    default:
			      //右下角
			      $start_x = $dstinfo[0] - $watinfo[0];
			      $start_y = $dstinfo[1] - $watinfo[1];
			}
			
			//5.  采样合并
		  if(imagecopymerge($dst,$wat,$start_x,$start_y,0,0,$watinfo[0],$watinfo[1],$pct)){
		    //成功，保存图片返回路径
		    $name = 'water_'.basename($file);
		    
		    $res = $dstsave($dst,$path.'/'.$name);
		    //destroy resource
		    imagedestroy($dst);
		    imagedestroy($wat);
		    
		    if($res){
		      //success
		      return $path.'/'.$name;
		    }else{
		      //failed
		      $this->errorInfo = 'waterpicture saved failure';
		      return false;
		    }
		  }else{
		    //failure
		    $this->errorInfo = '水印图片合并失败！';
		    
		    //destroy resource
		    imagedestroy($dst);
		    imagedestroy($wat);
		    
		    return false;
		  }
		 }
		 
    
    /*
		 * 判断文件是否有效
		 * @param1 string $file,需要判断的文件
		 *
		 */
		public function checkFile($file){
		  //判断当前文件是否是一个图片
      if(!is_file($file)){
        //is not a file
        $this->errorInfo = 'It's not an effective file;
        return False;
      }
      
      //get the extension of $file
      $extension = substr($file,strrpos($file,'.')+1);
      
      if(!array_key_exists($extension,$this->image_type)){
        //not allowed picture
        $this->errorInfo = 'not an available file';
        return false;
      }
      
      //return
      return $extension;
		}
  }
