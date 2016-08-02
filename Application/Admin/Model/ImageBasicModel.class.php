<?php
/**
 * Created by PhpStorm.
 * User: liuxiumei
 * Date: 2016/4/18
 * Time: 16:40
 */

namespace Admin\Model;
use Think\Model;

class ImageBasicModel extends Model
{
    
    
     // 根据where条件查询实验基础镜像数据
    public function sel($where = array())
    {
        return $list = $this->where($where)->select();
        
    }
    // 向实验基础镜像数据表插入数据
    public function insert($data = array())
    {
        return $list = $this->data($data)->save();
        
    }
   
    // 更新实验基础镜像数据表
    public function update($where = array(),$data = array())
    {
       
        return $this->where($where)->data($data)->save();
    }
    /**
    *@fun :删除基础镜像数据记录及镜像文件
    *@parma:where 查询条件数组
    *@return:执行结果。0：成功；1：失败
    **/
    public function del($where = array())
    {
        
        $arr = $this->where($where)->find();
        $path = $arr['img_basic_path'];
        //exec("sudo rm $path",$res,$rec);     要用双引号，否则单引号不能解析
        if(is_file($path)){
            chmod($path,0777);
            $flag = unlink($path);                   //以后改
            if($flag){
                return $this->where($where)->delete();
            }else{
               return 0;
            } 
        }else{
            return $this->where($where)->delete();
        }
        
    }
   
    /**
    *@fun :恢复镜像，将备份文件夹的镜像文件复制到基础镜像文件夹中
    *@parma:where 查询条件数组
    *@return:执行结果。0：成功；1：失败
    **/
    public function repaire($where = array()){
         //$map['img_basic_id'] = $id;
         $result = $this->where($where)->find();
         $path = $result['img_basic_path'];
         $path_arr = explode('/',$path);
         $name = end($path_arr);
         $newpath = $this->basicimage_backup_path.$name;        //备份镜像的文件夹
         exec("sudo cp -f $newpath $path",$res,$rec);      // $newpath源文件，$path目标文件*/
   
         return $rec;
    }

    /**
    *@fun :get some field of the img_image_basic
    *@parma:where 查询条件数组
    *@parma:fields 查询字段变量，可以是字符串也可以是数组
    *@return:执行结果。0：成功；1：失败
    **/
    public function getfields($map = array(), $fields)
    {
        return $this->where($map)->field($fields)->select();
    }


}