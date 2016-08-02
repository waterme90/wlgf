<?php
/**
 * Created by PhpStorm.
 * User: liuxiumei
 * Date: 2016/4/18
 * Time: 16:40
 */

namespace Admin\Model;
use Think\Model;
class HostModel extends Model
{
	public function getField($where = array(),$fields){
		return $this->field($fields)->where($where)->select();
	}

	public function sel($where = array()){
		return $this->where($where)->select();
	}
	public function joinDomain($where = array(),$fields){
		return $this->table('host h')->join('left join domain d on d.host_id=h.host_id')->field($fields)->group('h.host_id')->select();
	}
	public function del($where){
		return $this->where($where)->delete();
	}
}