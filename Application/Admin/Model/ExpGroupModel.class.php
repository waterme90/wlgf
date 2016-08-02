<?php

namespace Admin\Model;
use Think\Model;
class ExpGroupModel extends Model
{
    protected $fields = array(
       'exp_group_id','exp_group_name','exp_group_description'
    );
    //获取所有实验方向
    function selectExpGroup(){
        $list = $this->field('exp_group_id,exp_group_name')->select();
        return $list;
     }

    //根据实验方向名称获取id
    public function getGroupId($name = ''){
        $map['exp_group_name'] = $name;
        $list = $this->field($this->fields)->where($map)->find();
        return $list;
     }  
}
