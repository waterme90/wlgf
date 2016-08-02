<?php
namespace Admin\Model;
use Think\Model;
class ExpSubgroupModel extends Model
{
    protected $fields = 'exp_subgroup_id,exp_subgroup_name,exp_group_id';
    //获取所有实验分类
    public function selectAllSubGroup(){
        $list = $this->field($this->fields)->select();
        return $list;
    }
    //获取实验方向下相应的实验分类
    public function selectSubGroup($groupid=1){
        $map[]['exp_group_id'] = $groupid;
        $list2 = $this->field($this->fields)->where($map)->select();
        return $list2;
    }
    //获取实验分类所属的实验方向
    public function getGroupId($subGroupId = 1){
        $map[]['exp_subgroup_id'] = $subGroupId;
        $res = $this->where($map)->select();
        return $res;
    }

    // ²éÑ¯ÊµÑé·ÖÀà
    public function selectSubGroups($type = 1, $id = 1)
    {
        if($type == 1){     // ²éÑ¯basicgroupÏÂµÄËùÓÐÊµÑé·ÖÀà
            $table = 'exp_basicgroup AS b, exp_group AS g, exp_subgroup AS s';
            $map['b.exp_basicgroup_id'] = $id;
            $map['_string'] = 's.group_id=g.exp_group_id AND g.basic_group_id=b.exp_basicgroup_id';
        }else{      // ²éÑ¯ÊµÑé·½ÏòÏÂµÄÊµÑé·ÖÀà
            $table = 'exp_subgroup';
            $map['group_id'] = $id;
        }
        $list = $this->table($table)->where($map)->select();
        return $list;
    }

//    // ²éÑ¯È«²¿ÊµÑé·ÖÀà
//    public function selectAllSubGroups($basicgroup = 1)
//    {
//        $list = $this->table('exp_basicgroup AS b, exp_group AS g, exp_subgroup AS s')
//                ->where('s.group_id=g.exp_group_id AND g.basic_group_id=b.exp_basicgroup_id AND b.exp_basicgroup_id = '.$basicgroup)->select();
//        return $list;
//    }
//
//    // ²éÑ¯ÊµÑé·½ÏòÏÂµÄÈ«²¿ÊµÑé·ÖÀà
//    public function selectExpSubGroups($groupId = 1)
//    {
//        $list = $this->table('exp_subgroup')->where('group_id = '.$groupId)->select();
//        return $list;
//    }

    // ²éÑ¯¸ÃÊµÑé·ÖÀàµÄÊµÑéÊýÁ¿
    public function countExpOfSubGroups($subGroupId = 1)
    {
        $list = $this->table('exp')->where('exp_subgroup_id = '.$subGroupId)->count();
        return $list;
    }

    // ²éÑ¯Ä³Ò»ÊµÑé·ÖÀà
    public function selectExpSubGroup($exp_subgroup_name='', $groupId)
    {
        $map['exp_subgroup_name'] = array("like", "%".$exp_subgroup_name."%");
        $list = $this->table('exp_subgroup')->where($map)->where('group_id='.$groupId)->select();
        return $list;
    }

    // ÐÂ½¨ÊµÑé·ÖÀà£»×¢ÒâÔÚÊ¹ÓÃ×Ô¶¯Íê³ÉÊ±add()²»ÄÜÓÐ²ÎÊý´«Èë£¬Ò²²»ÄÜÓÐdata($data)
    public function insertExpSubGroup($subgroupName= '')
    {
        $map = array('exp_subgroup_name', $subgroupName);
        return $this->table('exp_subgroup')->add($map);
    }

    // ¸üÐÂÊµÑé·ÖÀàÐÅÏ¢
    public function updateExpSubGroup($subgroupname, $subgroupId)
    {
        $map = array('exp_subgroup_id' => $subgroupId);
        $data = array('exp_subgroup_name' => $subgroupname);
        return $this->table('exp_sybgroup')->where($map)->save($data);
    }
}
