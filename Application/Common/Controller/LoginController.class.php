<?php
namespace Common\Controller;
use Think\Controller;
class LoginController extends Controller{
	 public function _initialize(){
        //判断刷新的页面是否在当前用户的访问权限内.解决切换用户时访问到其他角色页面的问题
        $session_role = session('role');
        //根据当前的url确定操作页面的角色
        $currUrl = $_SERVER['PHP_SELF'];
        $admin = 'admin.php';
        $teacher = 'teacher.php';
        $student = 'index.php';
        // dump('session='.$session_role);
        if(strpos($currUrl,$student)){
            $role = 2;
        }else if(strpos($currUrl,$teacher)){
            $role = 1;
        }else if(strpos($currUrl,$admin)){
            $role = 0;
        }
        if($role!=$session_role || $session_role==''){

            $this->error("请先登录",U('admin.php/LoginShow/login'));
            print_r("expression");
        }


  //       /*在线时间按统计*/
  //       $user = D('Admin/User');
  //       // $sess = D('Admin/Session');
  //       // $sess->checkExpire();
	// 	     if($_SERVER['REQUEST_URI'] == "ptplat/admin.php/LoginShow/login"){
	// 	        //$this->assign('name',session('name'));
	// 	     }
  //       if(!session('id')){		
  //           $user->updateTime(2);
  //           $this->error("请先登录",U('admin.php/LoginShow/login'));
  //       }
  //       else{
  //           $user->updateTime(0);
  //           $path = $_SERVER['REQUEST_URI'];
  //           if(session('role')==0){            //管理员
  //               $position = strpos($path,'admin.php');
  //               if(!$position){         //如果路径没有admin.php,那么会返回登陆页面
  //                   $this->error("请先登录",U('admin.php/LoginShow/login'));
  //               }
  //           }
  //           else if(session('role')==1){      //教师
  //               $position = strpos($path,'teacher.php');
  //               if(!$position){         //如果路径没有teacher.php,那么会返回登陆页面
  //                   $this->error("请先登录",U('admin.php/LoginShow/login'));
  //               }
  //           } 
  //       }

    }

}