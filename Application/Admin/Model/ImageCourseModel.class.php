<?php
/**
 * Created by PhpStorm.
 * User: liuxiumei
 * Date: 2016/4/18
 * Time: 16:40
 */

namespace Admin\Model;
use Think\Model;

class ImageCourseModel extends Model
{
    private $courseimage_backup_path = '/var/lib/libvirt/images/courseimage_bak/';
    protected $fields = array(
        'img_course_id','img_course_name','img_course_path','img_basic_id','exp_id','device_id','device_name','device_mapping_port','device_nic_num',
        '_type' => array(
            'img_course_id'                  =>  'int',
            'img_course_name'                =>  'char',
            'img_course_path'                =>  'char',
            'img_basic_id'                   =>  'int',
            'exp_id'			             =>  'int',
            'device_id'                      =>  'int',
            'device_name'                    =>  'char',
            'device_mapping_port'            =>  'char',
            'device_nic_num'                 =>  'int'

        )
    );
    protected $pk = 'img_course_id';

   
    //后期添加，新建或编辑实验基础镜像时的验证信息
    protected $_validate = array(
        array('img_course_name','require','镜像名称必须填写',0),		// 实验名称不能为空
        array('img_course_name','require','镜像名称不能重复',0,'unique'),
        array('img_course_name','1,50','镜像名称太长',0,'length'),

        array('device_nic_num','0,10','镜像位数不能超过10位',0,'length'),
    );

    /**
    * 根据where条件获取指定的字段的记录
    * @param:where 查询条件数组
    * @param:fields 查询字段变量，可以是字符串也可以是数组
    * @return:执行结果。0：成功；1：失败
    **/
    public function getfields($map = array(), $fields)
    {
        return $this->where($map)->field($fields)->select();
    }
    /**
    * @fun :根据where条件查询课件镜像的记录
    * @param:where 查询条件数组
    * @param:fields 查询字段变量，可以是字符串也可以是数组
    * @return:执行结果。0：成功；1：失败
    **/
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

        return $this->table('image_course i')->join('exp e on i.exp_id=e.exp_id')->field($fields)->where($where)->select();
    }

    public function insert($data = array())
    {
        return $list = $this->data($data)->add();
        
    }
   
    // 更新实验基础镜像
    public function update($where = array())
    {
       
        return $this->where($where)->save();
    }
    // 删除基础镜像
    public function del($where = array())
    {
        
        $arr = $this->where($where)->find();
        $path = $arr['img_course_path'];
        //exec("sudo rm $path",$res,$rec);     要用双引号，否则单引号不能解析
        if(is_file($path)){
            chmod($path,0777);
            $flag = unlink($path);                   //以后改
            if($flag){
                return $this->where($map)->delete();
            }else{
               return 0;
            } 
        }else{
            return $this->where($map)->delete();
        }
        
    }
   
    /**
    *@fun :恢复镜像，将备份文件夹的镜像文件复制到基础镜像文件夹中
    * @parma:where 查询条件数组
    * @return:执行结果。0：成功；1：失败
    **/
    public function repaire($where = array()){
         //$map['img_course_id'] = $id;
         $result = $this->where($where)->find();
         $oldpath = $result['img_course_path'];
         $path_arr = explode('/',$path);
         $name = end($path_arr);
         $newpath = $this->courseimage_backup_path.$name;        //备份镜像的文件夹
         exec("sudo cp -f $newpath $oldpath",$res,$rec);      // $newpath源文件，$path目标文件*/
   
         return $rec;
    }
    
    public function selectpath($expId){
        return $this->field('img_course_path')->where('exp_id='.$expId)->select();
    }
    //查询工作站的基础镜像
    public function selWorkImage($workId){
        $map['exp_id'] = $workId;
        $map['img_course_type'] = 4;      //工作站
        return $this->field($fields)->where($map)->select();
    }
}