<?php

namespace Admin\Model;
use \Think\Model;

class ExpTeacherModel extends Model
{
	//获取教师姓名
    public function selectTeaName($exp_creatorid=1){
       $table = 'exp as e,user as u';
       $map['u.user_id'] = $exp_creatorid;
       $map['e.exp_id'] = $exp_id;
       $map['_string'] = 'e.exp_creatorid = u.user_id';
       $list = $this->table($table)->where($map)->field('u.real_name')->select();
       return $list;
    }

     //插入新的教师实验信息
    public function insert($data){
    	$this->add($data);
    }

    //获取exp_teacher_id
    public function selectTeaid($creator = 1,$expId = 0){
         $map['exp_id'] = $expId;
         $map['teacher_id'] = $creator;
         $info = $this->field('exp_teacher_id')->where($map)->select();
         return $info;

    }

    //删除记录
    public function del($expId){
       $map = array('exp_id' => $expId);
       return $this->where($map)->delete();
    }

    //更新发布状态
    public function publish($idArr, $creator = 1,$publish){
       $map['teacher_id'] = $creator;
       $map['exp_id'] = array('IN', $idArr);
       $data['publish_status'] = $publish;     //已发布
       return $this->where($map)->save($data);
    }

    
}