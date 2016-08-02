<?php
namespace Admin\Model;
use Think\Model;
class ExpModel extends Model
{
    protected $fields = array(
        'exp_id','exp_attribute','exp_name','exp_subgroup_id','exp_difficulty','exp_video_path','exp_topojson_path','exp_instruction_path','exp_creatorid','exp_use_topo','type','exp_description','exp_sn'
    );

    //后期添加，新建或编辑实验信息时的验证信息
    protected $_validate = array(
        array('exp_name', 'require','实验名称不能为空！'),
        array('exp_name','/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u','实验名称不能带有空格、括号、小数点、下划线等特殊符号！'),
        );
    //联合查询时获取的字段
    protected $connectFields = array(
        'e.exp_id','exp_name','exp_group_name','exp_subgroup_name','exp_difficulty','exp_attribute','exp_creatorid','publish_status','type'
        );

    //获取实验分类下的实验
    public function expOfSub($groupid=1,$searchKeyWord='',$creator=1,$type = 0){
         $table = 'exp as e, exp_group as g, exp_subgroup as s, exp_teacher as t';
         $map['s.exp_subgroup_id'] = $groupid;
         $map['type'] = $type;//0为实验
         $map['_string'] = 'e.exp_subgroup_id = s.exp_subgroup_id and s.exp_group_id = g.exp_group_id and e.exp_id = t.exp_id and t.teacher_id = '.$creator.'';
         $map['e.exp_name'] = array("like", "%" . $searchKeyWord . "%");
         
         $list = $this->table($table)->where($map)->field($this->connectFields)->order('e.exp_attribute')->select();
         return $list;
    }

    //获取实验方向下的实验
    public function expOfGroup($groupid=1,$searchKeyWord='',$creator=1,$type = 0){
        $table = 'exp as e, exp_group as g, exp_subgroup as s, exp_teacher as t';
         $map['g.exp_group_id'] = $groupid;
         $map['type'] = 0;//0为实验
         $map['_string'] = 'e.exp_subgroup_id = s.exp_subgroup_id and s.exp_group_id = g.exp_group_id and e.exp_id = t.exp_id and t.teacher_id = '.$creator.'';
         $map['e.exp_name'] = array("like", "%" . $searchKeyWord . "%");

         $list = $this->table($table)->where($map)->field($this->connectFields)->order('e.exp_attribute')->select();
         return $list;
    }

    //获取所有实验
    public function expOfAll($searchKeyWord='',$creator=1,$type = 0){
        $table = 'exp as e, exp_group as g, exp_subgroup as s, exp_teacher as t';
         $map['type'] = 0;//0为实验
         $map['_string'] = 'e.exp_subgroup_id = s.exp_subgroup_id and s.exp_group_id = g.exp_group_id and e.exp_id = t.exp_id and t.teacher_id = '.$creator.'';
         $map['e.exp_name'] = array("like", "%" . $searchKeyWord . "%");

         $list = $this->table($table)->where($map)->field($this->connectFields)->order('e.exp_attribute')->select();
         return $list;
    }
    
    //插入实验
    public function insertExp($role=0,$creator = 1){
        $ret = $this->add();
        if($ret){
            $data['exp_id'] = $this->getLastInsID();  //获取新建的实验的id
            $exp_tea = $this->instance('ExpTeacher');   //实例化ExpTeacher模型
            $data['teacher_id'] = $creator;  //创建人id
            $exp_tea->insert($data);   //插入exp_teacher表中
            if($role == 1){
            	$map['role'] = 0;
            }else{
            	$map['role'] = 1;
            }            
            $tea = $this->table('user')->field('user_id')->where($map)->select();  //获取所有的教师或管理员id
            foreach($tea as $t){
                $data['teacher_id'] = $t['user_id'];   //教师或管理员id
                $exp_tea->insert($data);     //插入exp_teacher表中
            }
        }
        return $ret;
    }
    //插入靶场、工程信息，不需要写入exp_teacher表
    public function insertOther(){
    	return $this->add();
    }
    //根据实验id获取实验信息
    public function getExpInfo($idArr,$creator){
        $map['exp_id'] = array("IN", $idArr);
        $role = $this->table('user')->field('role')->where('user_id = "'.$creator.'"')->find();
        if($role['role'] == 1){
            $map['exp_creatorid'] = $creator;
        }
        $list = $this->field($this->fields)->where($map)->select();
        return $list;
    }
    
    //删除exp表中实验记录
    public function del($id =1 ){
        $map = array('exp_id' => $id);
        return $this->where($map)->delete();
    }

    //更新数据函数
    public function updateExp($expId = 1){
        $map['exp_id'] = $expId;
        return $this->where($map)->save();
    }
    
    //检测该实验名是否已存在
    public function selectExpName($exp_name = ''){
        $map['e.exp_name'] = $exp_name;
        $map['_string'] = 'e.exp_subgroup_id = s.exp_subgroup_id AND s.exp_group_id = g.exp_group_id';
        $list = $this->table('exp as e,exp_subgroup as s,exp_group as g')->where($map)->fetchSql(true)->select();
        echo $list;
        return $list;
    }

    //学生显示某一分类的实验列表
    public function selectExpGroup($userId, $exp_group_id = 1, $searchtext = '')
    {
        $table = 'exp AS e, exp_teacher AS et, exp_student AS ets, user AS u, exp_subgroup AS es, exp_group AS eg';
        $map['_string'] = 'e.exp_id = et.exp_id AND et.exp_teacher_id = ets.exp_teacher_id AND ets.student_id = u.user_id
                            AND e.exp_subgroup_id = es.exp_subgroup_id AND es.group_id = eg.exp_group_id';       // 表关联
        $map['u.user_id'] = $userId;
        $map['eg.exp_group_id'] = $exp_group_id;
        $map['e.exp_name'] = array("like", "%" . $searchtext . "%");
        
        $count = $this->table($table)->where($map)->count();
        $Page = new \Think\AjaxPage($count, $this->pagesize, 'exp');             // 实例化分页类 传入总记录数和每页显示的记录数
        $limit_value = $Page->firstRow . "," . $Page->listRows;
        $ret[] = $Page->show();                                  // 分页显示输出   
        $ret[] = $this->table($table)->where($map)->limit($limit_value)->select();
        return $ret;
    }

    //获取某一方向的实验列表
     public function selectExpSub($userId, $exp_group_id = 1, $searchtext = '')
    {
        $table = 'exp AS e, exp_teacher AS et, exp_student AS ets, user AS u, exp_subgroup AS es';
        $map['_string'] = 'e.exp_id = et.exp_id AND et.exp_teacher_id = ets.exp_teacher_id AND ets.student_id = u.user_id
                            AND e.exp_subgroup_id = es.exp_subgroup_id';       // 表关联
        $map['u.user_id'] = $userId;
        $map['es.exp_subgroup_id'] = $exp_group_id;
        $map['e.exp_name'] = array("like", "%" . $searchtext . "%");
        
        $count = $this->table($table)->where($map)->count();
        $Page = new \Think\AjaxPage($count, $this->pagesize, 'exp');             // 实例化分页类 传入总记录数和每页显示的记录数
        $limit_value = $Page->firstRow . "," . $Page->listRows;
        $ret[] = $Page->show();                                  // 分页显示输出   
        $ret[] = $this->table($table)->where($map)->limit($limit_value)->select();
        return $ret;
    }

    //获取管理员或教师/工具培训、工程或靶场的列表
    public function selother($direction,$groupid,$searchKeyWord='',$creator=1){
        switch($direction){
            case 1:$instance = D('TooltrainGroup');$tablegroup = 'tooltrain_group';$groupidname = 'tooltrain_group_id';break;
            case 2:$instance = D('TarGroup');$tablegroup = 'tar_group';$groupidname = 'tar_group_id';break;
            case 3:$instance = D('ProGroup');$tablegroup = 'pro_group';$groupidname = 'pro_group_id';break;
        }
        $table = 'exp AS e,'.$tablegroup.' AS s,user as u';
        $role = $this->table('user')->field('role')->where('user_id = "'.$creator.'"')->find();
        if($role['role'] == 0){   //管理员
            $map['_string'] = 'e.exp_subgroup_id = s.'.$groupidname.' and  e.exp_creatorid = u.user_id';
        }else{                    //教师
            $map['_string'] = 'e.exp_subgroup_id = s.'.$groupidname.' and  e.exp_creatorid = u.user_id and (e.exp_attribute = 0 || (e.exp_attribute = 1 and e.exp_creatorid = '.$creator.'))';
        }
        
        $map['e.type'] = $direction;
        if($groupid!=0){
            $map['e.exp_subgroup_id'] = $groupid;
        }
        $map['e.exp_name'] = array("like", "%" . $searchKeyWord . "%");

        $list = $this->table($table)->field('e.*,u.real_name,s.*')->distinct(true)->where($map)->order('exp_attribute')->select();
        return $list;

    }
    public function getWorkstation($searchKeyWord=''){
    	$map['e.type'] = 4;
        $map['e.exp_name'] = array('like','%'.$searchKeyWord.'%');
        $table = 'exp e,user u';
        $map['_string'] = 'e.type = 4 and u.user_id = e.exp_creatorid';
    	return $this->table($table)->field('e.*,u.real_name')->where($map)->order('exp_attribute')->select();
    }
    // 实例化数据库表
    public function instance($table){
        return D('Admin/'.$table);
    }

    //查询某一字段或多个字段信息
    public function selField($map=array(),$fields){
        return $this->field($fields)->where($map)->select();
    }



    //获取学生工具培训、靶场集训等的信息
    public function selectstudent($groupid,$type){
        switch($type){
            case 1:$instance = D('TooltrainGroup');$tablegroup = 'tooltrain_group';$groupidname = 'tooltrain_group_id';break;
        }

        $table ='exp as e,$tablegroup as g';
        $map['e.exp_subgroup_id'] = $groupid;
        $map['_string'] = 'e.exp_subgroup_id = g.$groupidname';
        $list = $this->table($table)->field('e.*,g.*')->where($map)->select();
        return $list;


    }
}
