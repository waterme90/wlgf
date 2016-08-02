<?php
namespace Admin\Model;
use Think\Model;
 class DomainModel extends Model{
 	public function get($map_exp){
       return $this->field('domain_ip')->where($map_exp)->select();
 	}


 	public function del($map_exp){
 		return $this->where($map_exp)->delete();
 	}

 	public function getField($where = array(),$fields = ''){
 		return $this->where($where)->field($fields)->select();
 	}

 	public function joinAll($where = array(),$fields = ''){
 		return  $this->table('domain d')
		 			 ->join('host h on h.host_id = d.host_id')
		 			 ->join('exp e on d.exp_id=e.exp_id')
		 			 ->join('user u on d.user_id = u.user_id')
		 			 ->where($where)
		 			 ->field($fields)
		 			 ->select();
 	}
 }

?>