<?php

namespace Admin\Model;
use \Think\Model;

class ExpStudentModel extends Model
{
   protected $connectFields = array(
        'exp_student_id','student_id','exp_student_learn_state','e.exp_id','e.exp_subgroup_id','e.exp_name','e.exp_difficulty'
    );
	//删除记录
  public function del($map){
    $this->where($map)->delete();

  }

  //判断记录是否已经存在
  public function check($map){
     $count = $this->where($map)->count();
     if($count){ //存在该记录
         return false;
     }else{   //不存在该记录
         return true;
     }
  }

  //发布实验课件时，插入exp_student表
  public function insertAll($data){
      return $this->addAll($data);
  }

  //获取某实验下学生学习的信息
  public function getStudyInfo($map){
    return $this->where($map)->select();
  }

  //保存批阅记录
  public function update($map,$data){
     return $this->where($map)->save($data);
  }

  //获取该学生的实验报告
  public function getReport($exp_id, $student_id)
    {
        $map['et.exp_id'] = $exp_id;
        $map['ets.student_id'] = $student_id;
        return $this->table('exp_student as ets')
                    ->join('exp_teacher AS et ON ets.exp_teacher_id = et.exp_teacher_id')
                    ->where($map)->find();
    }

  //获取某方向下所有实验
  public function getgroupexpList($groupid,$studentid){
        $table = 'exp_student as est,exp_teacher as et,exp as e,exp_subgroup as es';
        $map['es.exp_group_id'] = $groupid;
        $map['est.student_id'] = $studentid;
        $map['_string'] = 'est.exp_teacher_id = et.exp_teacher_id and et.exp_id = e.exp_id and e.exp_subgroup_id = es.exp_subgroup_id';
        $list = $this->table($table)->where($map)->field($this->connectFields)->select();
        return $list;
  }

  //获取某实验分类下的所有实验
   public function getsubexpList($groupid,$studentid){
        $table = 'exp_student as est,exp_teacher as et,exp as e';
        $map['e.exp_subgroup_id'] = $groupid;
        $map['est.student_id'] = $studentid;
        $map['_string'] = 'est.exp_teacher_id = et.exp_teacher_id and et.exp_id = e.exp_id ';
        $list = $this->table($table)->where($map)->field($this->connectFields)->select();
        return $list;
  }
   
   //获取所有实验
   public function getexpList($studentid){
        $table = 'exp_student as est,exp_teacher as et,exp as e';
        $map['est.student_id'] = $studentid;

        $map['_string'] = 'est.exp_teacher_id = et.exp_teacher_id and et.exp_id = e.exp_id ';
        $list = $this->table($table)->where($map)->field($this->connectFields)->select();
      
        
        return $list;
  }
}