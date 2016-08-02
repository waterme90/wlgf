<?php
namespace Admin\Model;
use Think\Model;

class DomainIpmappingModel extends Model{
	public function del($ipArr){
         $map['domain_ip'] = array("IN", $ipArr);
         return $this->where($map)->delete();
	}
}
?>