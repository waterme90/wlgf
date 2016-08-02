<?php
namespace Admin\Model;
use Think\Model;
class ImageBasicGroupModel extends Model
{
  public function sel($where = array())
  {
    return $this->where($where)->select();
  }
  public function getfield($fields,$where = array())
  {
    return $this->where($where)->field($fields)->select();
  }
}
?>