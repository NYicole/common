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
   * @return mixed,成功返回文件上传的相对路径，失败返回FALSE
   */
  public static function uploadSingle($file,$allow,$max){
    //判断上传的文件是否合理
    if(!is_array($file)){
      //给出提示
      self::$errorInfo = '当前不是一个合法的文件信息'';
      return False;
    }
    
    //判断上传过来的文件类型是否是允许的
    if(!in_array($file['type'],$allow)){
      //文件不允许上传
      self::$errorInfo = '上传文件类型不合法';
      return False;
    }
    
    
  } 
}
