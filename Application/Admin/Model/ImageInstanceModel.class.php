<?php
/**
 * Created by PhpStorm.
 * User: liuxiumei
 * Date: 2016/4/18
 * Time: 16:40
 */

namespace Admin\Model;
use Think\Model;

class ImageInstanceModel extends Model
{
    private $instanceimage_backup_path = '/var/lib/libvirt/images/instanceimage_bak/';
    //private $instanceimage_path = '/var/lib/libvirt/images/instanceimage/';
    protected $fields = array(
        'img_instance_id','img_course_id','img_instance_name','img_instance_path','exp_id',
        '_type' => array(
            'img_instance_id'                  =>  'int',
            'img_instance_name'                =>  'char',
            'img_instance_path'                =>  'char',
            'img_course_id'                    =>  'int',
            'exp_id'			               =>  'int'
           

        ),
    );
    protected $pk = 'img_instance_id';

   
 
    /**
    *@fun :get some field of the img_image_instance
    *@parma:where 查询条件数组
    *@parma:fields 查询字段变量，可以是字符串也可以是数组
    *@return:执行结果。0：成功；1：失败
    **/
    public function getfield($map = array(), $fields)
    {
        return $this->where($map)->field($fields)->select();
    }
    // 查询
    public function sel($where = array())
    {
        return $list = $this->where($where)->select();
        
    }
    /** 
    * 联合exp表查询相应字段的信息
    *
    * @param mixd $where where条件
    * @param mixed $fields 要查询的字段
    * @return  mixed 查询结果集
    */
    public function seljoinexp($where = array(),$fields = ''){

        return $this->table('image_instance i')->join('exp e on i.exp_id=e.exp_id')->field($fields)->where($where)->select();
    }

    public function seljoinall($where=array(),$fields){
        return $this->table('image_instance i')
            ->join("exp e on e.exp_id=i.exp_id")
            ->join("user u on u.user_id=i.user_id")
            ->join("image_course c on i.img_course_id = c.img_course_id")
            ->field($fields)->where($where)->select();
    }

    public function insert($data = array(),$data = array())
    {
        return $list = $this->data($data)->save();
        
    }
   
    // 更新
    public function update($where = array())
    {
       
        return $this->where($where)->save();
    }
    // 删除
    public function del($where = array())
    {
        
        $arr = $this->where($where)->find();
        $path = $arr['img_instance_path'];
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
    * @parma:where 查询条件数组
    * @return:执行结果。0：成功；1：失败
    **/
    public function repaire($where = array()){
         //$map['img_instance_id'] = $id;
         $result = $this->where($where)->find();
         $oldPath = $result['img_instance_path'];
         $path_arr = explode('/',$path);
         $name = end($path_arr);
         $newPath = $this->instanceimage_backup_path.$name;        //备份镜像的文件夹
         exec("sudo cp -f $newPath $oldPath",$res,$rec);      // $newpath源文件，$path目标文件*/
   
         return $rec;
    }

   

}