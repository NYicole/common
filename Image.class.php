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
    
    }
  }
