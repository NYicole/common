<?php
//文件上传类
class Upload{
  public staic $errorInfo;
  public staic $multiErrorInfo;
  
  //上传方法
  /*
   * 单文件上传
   * @param1 array $file ,上传图片的信息（5个）
   * @param2 array $allow, 允许上传的文件的格式，MIME类型
   * @param3 int $max , 允许上传文件的最大的限制（是为了前台和后台的统一）
   * @param4 string $path,需要上传文件的路径(相对路径)
   * @return mixed,成功返回文件上传的路径，失败返回FALSE
   */
  public static function uploadSingle($file,$allow,$max,$path){
    //判断上传的文件是否合理
    if(!is_array($file)){
      //给出提示
      self::$errorInfo = '当前不是一个合法的文件信息';
      return False;
    }
    
    //判断上传过来的文件类型是否是允许的
    if(!in_array($file['type'],$allow)){
      //文件不允许上传
      self::$errorInfo = '当前文件类型不允许上传，允许上传的文件类型有：'.implode(',',$allow);
      return False;
    }
    
    //处理上传文件的错误代码
    switch($file['error']){
      case 1 :
        self::$errorInfo = '文件过大，已经超出服务器允许上传的大小';
        return false;
      case 2 :
        self::$errorInfo = '文件过大，已经超出浏览器允许上传的大小';
        return false;
      case 3 :
        self::$errorInfo = '文件上传不完整';
        return false;
      case 4 :
        self::$errorInfo = '未选择要上传的文件';
        return false;
      case 6 :
        self::$errorInfo = '找不到服务器的临时目录';
        return false;
      case 7 :
        self::$errorInfo = '没有权限将文件上传到目标文件夹';
        return false;
      case 0 :
        //判断当前文件的大小是否在允许的范围内
        if($file['size'] > $max){
          //超过当前商品上传文件允许的大小
          self::$errorInfo = "文件太大，当前允许上传的大小为：{$max}字节大小！";
          return false;
        }
    }
    
    //处理文件
    //获取新的文件名
    $newname = self::getNewName($file['name']);
    if(move_uploaded_file($file['tmp_name']),$path.'/'.$newname){
      //上传成功
      return $path.'/'.$newname;
    }else{
      //失败
      self::$errorInfo = '文件移动失败';
      return false;
    }
    
  } 
  
  //生成随机的新的上传文件名
  /*
   * @param1 string $filename, 上传文件的原文件名
   * @return string $newname , 上传生成的新的文件名
   */
  private static function getNewName($filename){
    //获取上传文件的扩展名
    $extension = substr($filename,strrpos($filename,'.'));
    
    //生成随机的名字
    $newname = date('YmdHis',time());
    
    //拼凑字符串
    $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    //随机取出6个字符串拼接在$newname后
    for($i=0;$i<6;$i++){
      $newname .= $str[mt_rand(0,strlen($str)-1)];
    }
    
    //返回拼凑好的文件名以及后缀名
    return $newname.$extension;
  }
  
  //多文件上传
  // @param array $file,多维数组
  public static function uploadMultipe($file){
    //定义一个数组保存上传路径
    $upload_arr = array();
    //也要先输入进来的是不是一个数组
    if(is_array($file)){
      //遍历数组$file
      foreach($file as $single){
        //如果$single下面的name里面的值是数组的话，那么就是多文件上传，如果只有一个，就是单文件上传
        if(is_array($single['name'])){
          //多文件上传，还要遍历
          for($i=0,$length = count($single['name']);$i<$length;$i++){
            //构建数组
            $arr = array(
              'name' => $single['name'][$i],
              'type' => $single['type'][$i],
              'tmp_name' => $single['tmp_name'][$i],
              'error' => $single['error'][$i],
              'size' => $single['size'][$i]
            );
            
            //已经得到一个单独的文件
            if($path = self::uploadSingle($arr)){
              $upload_arr[$i] = $path;
            }else{
              //上传不成功，错误信息
              self::$mulitErrorInfo[] = self::$errorInfo;
            }
          }else{
            //不是多文件上传
            if($path = self::uploadSingle($single)){
              $upload_arr[] = $path;
            }else{
              //上传不成功
              self::$mulitErrorInfo[] = self::$errorInfo;
            }
          }
        }
      }
    }
    //返回数据
    return $upload_arr;
  }
}
