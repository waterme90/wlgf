<?php
namespace Admin\Controller;
use Common\Controller\LoginController;
/**
*实验基类控制器
*
*@author     sunbiyun<1319258322@qq.com>
*@version 
*@since      1.0
*/
abstract class ExpController extends LoginController
{
	private $upload;
	private $virtual;
    private $image;
	private $uppath = "/home/www-data/wlgf/Manual/";
    public function _initialize(){
        parent::_initialize();
        Vendor("FilesOP");
        $this->upload = new \FilesOP();
        Vendor("image");
        $this->image = new \imageManagement();
       // Vendor("Virtual");
        //$this->virtual = new \Virtual();
    }
    public function getpath(){
        $arr = $this->diffAdd(); 
        $type = $arr[1];
          switch($type){
            case 0 : 
                     $path = $this->uppath."exp/";
                     break;
            case 1 : 
                     $path = $this->uppath."tooltrain/";
                     break;
            case 2 :
                     $path = $this->uppath."tar/";
                     break;
            case 3 ; 
                     $path = $this->uppath."pro/";
                     break;
            }
        return $path;

    }
/**
    *显示新建实验页面
    *
    *@access public 
    *@since 1.0
    *@return  无                                  
*/    
    public function showNewExp(){
        $this->getGroupList(0,1);
        $this->display();
    }
/**
	*新建实验
	*
	*@access public 
	*@since 1.0
	*@return  无                                  
*/
    public function newExpInfo(){
        $direction = I('get.direction');
        $exp = D('Admin/Exp');    //实例化实验类
        $user = D('Admin/User');
        $creator = session('id');
        $role = session('role');

        if(!$exp->create()){     //若表单验证失败，则跳转回上一个界面
            $this->error($exp->getError());
        }else{
            $expName = I('post.exp_name');
            $file = $_FILES['file'];
            $files = $_FILES['attachment'];      //上传的实验附件信息
            $video = $_FILES['video'];

            $arr = $this->diffAdd(I('post.exp_sn')); 
            if($file || $files || $video){
                $result = $this->addFile($expName,$file,$files,$video,$arr[1]);
                $exp->exp_instruction_path = $result['exp_instruction_path'];   // 实验信息文件路径
                $exp->exp_video_path = $result['exp_video_path']; 
            }else{
                $exp->exp_instruction_path = '';   // 实验信息文件路径
                $exp->exp_video_path = '';
            }
            $exp->exp_creatorid = $creator;  //获取创建人id
            $exp->exp_attribute = $arr[0];   //调用抽象函数 
            $exp->type = $arr[1];
            $exp->exp_subgroup_id = I('post.exp_subgroup');  //实验分类id
            $exp->exp_difficulty = I('post.exp_level');  //实验难度
            $exp->exp_description = I('post.exp_description');
          
            $exp->exp_sn = $arr[2];

            if($direction == 0){
                $res = $exp->insertExp($role ,$creator);   //新建实验
            }else{
                $res = $exp->insertOther();   //新建实验
            }
            if($res){
                $this->success('创建成功！');
            }else{
                $this->error('创建失败！');
            }  
        }
    }
/**
    *抽象新建实验时不同的地方
    *
    *@access protected 
    *@param mixed $exp_sn          实验序列号
    *@since 1.0
    *@return  array                要删除的实验数组信息               
*/
    abstract protected function diffAdd($exp_sn);
/**
    *显示编辑实验页面
    *
    *@access public 
    *@since 1.0
    *@return  无                                  
*/
    public function editExp(){
        $expId = I('get.expId');
        $this->getGroupList(0,1);
        $exp = D("Admin/Exp");
        $creator = session('id');
        $expInfo = $exp->getExpInfo($expId,$creator);
        $this->expInfo = $expInfo[0];
        /*为实验文件创建软连接,调用function.php里的公共函数*/
        //对路径进行处理，获取文件夹名字，对相应的实验文件夹做软链接
        $path = explode('/',$expInfo[0]['exp_instruction_path']);
        $count = count($path);
        $dirpath = '';
        foreach ($path as $key => $value) {
            if($key == ($count-1)) break;
            else{
                $dirpath.= $value.'/';
            }
        }
        $symlink = newSymlinkdir($dirpath);
        sleep('1');
        //对视频的软链接
        $symlink2 = newSymlink($expInfo[0]['exp_video_path']);
        //获取该实验分类对应的实验方向
        $subgroup = D('Admin/ExpSubgroup');
        $groupId = $subgroup->getGroupId($expInfo[0]['exp_subgroup_id']);
        $this->groupId = $groupId[0]['exp_group_id'];

        //获取所有的实验方向
        $group = D('Admin/ExpGroup');
        $groupList = $group->selectExpGroup();
        $this->groupList = $groupList;
        $this->manual = $symlink['manual'];
        $this->isExistFile = $symlink['isExistFile'];
        $this->file = end($path);     
        $this->manual2 = $symlink2['manual'];
        $this->isExistFile2 = $symlink2['isExistFile'];

        $this->display();
    }
/**
    *更新实验信息
    *有待完善
    *@access public 
    *@param mixed $exp_id          对应的实验id
    *@since 1.0
    *@return 无                          
*/
    public function updateExp($expId = 1){
        $exp = D('Admin/Exp');    //实例化实验类
        $user = D('Admin/User');
        $creator = session('id');
        $arr = $this->diffAdd(); 
        $type = $arr[1];
        $mapid['exp_id'] = $expId;
        $infopath = $exp->selField($mapid,'exp_instruction_path,exp_video_path');
        if(!$exp->create()){     //若表单验证失败，则跳转回上一个界面
            $this->error($exp->getError());
        }else{
            $expName = I('post.exp_name');
            $file = $_FILES['file'];
            $files = $_FILES['attachment'];      //上传的实验附件信息
            $video = $_FILES['video'];

            $expInfo = $exp->getExpInfo($expId,$creator);
            $path = $this->getpath();
            if($file['name']){
                //首先获取.files目录进行删除，因为is_dir不会将其解读为目录，所以不能调用delDirAndFile（）
                $path = explode('/',$expInfo[0]['exp_instruction_path']);
                $filesname = explode('.',end($path));
                $count = count($path);
                $dirpath = '';
                foreach ($path as $key => $value) {
                    if($key == ($count-1)) break;
                    else{
                        $dirpath.= $value.'/';
                    }
                }
                $delfilesdir = $this->upload->delDirAndFile($dirpath.$filesname[0].'.files/',true);   //删除原来的实验.files文件夹
                if(is_dir($dirpath))
                    $deldir = $this->upload->delDirAndFile($dirpath,true);                  //删除原来的实验文件夹及他下面的文件
            }
            if($video['name']){             //此处若上传路径变了，还需在改变
                if(is_file($expInfo[0]['exp_video_path'])){
                    $del_video = $this->upload->delFile($expInfo[0]['exp_video_path']);
                }
            }

          if($file || $files || $video){
                $result = $this->addFile($expName,$file,$files,$video,$type);
                $exp->exp_instruction_path = $result['exp_instruction_path'] ? $result['exp_instruction_path']:$infopath[0]['exp_instruction_path'];
                $exp->exp_video_path = $result['exp_video_path'] ? $result['exp_video_path']:$infopath[0]['exp_video_path'];;
            }
            $exp->exp_creatorid = $creator;  //获取创建人id
            $exp->exp_attribute = $this->diffAdd();                         //调用抽象函数 
            $exp->exp_name = $expName;
           
            $exp->exp_subgroup_id = I('post.exp_subgroup');  //实验分类id
            $exp->exp_difficulty = I('post.exp_level');  //实验难度
            $exp->exp_description = I('post.exp_description');
            $exp->exp_sn = I('post.exp_sn');
            $res = $exp->updateExp($expId);   //更新实验          
            if($res){
                $this->success('实验更新成功！');
            }else{
                $this->error('实验更新失败！');
            }
        }
    }

/**
    *新建实验时上传文件处理方法
    *
    *@access protected 
    *@param mixed $expName         实验名称
    *@param mixed $file            上传的单文件数组信息
    *@param mixed $files           上传的多文件数组信息
    *@since 1.0
    *@return  array                是否上传成功和上传文件路径信息              
*/
    private function addFile($expName,$file,$files,$video,$type){
        $fileName = $file['name'];
        if($fileName){
            $config = array(    
                'maxSize' => 3145728,   //设置文件上传大小
                'exts' => array('htm','html'),  //设置文件上传类型
                'rootPath' => '/home/www-data/wlgf/Manual/',   //设置文件上传的目录
                'autoSub' => false,  //不使用子目录保存，即不使用以“日期命名的文件夹”为目录
                'saveName' => '',   //设置文件的文件名不便
                'replace' => true,   //重名时覆盖
            );
          
            switch($type){
            case 0 : $config['rootPath'] = $config['rootPath']."exp/";
                     
                     break;
            case 1 : $config['rootPath'] = $config['rootPath']."tooltrain/";
                    
                     break;
            case 2 : $config['rootPath'] = $config['rootPath']."tar/";
                     
                     break;
            case 3 ; $config['rootPath'] = $config['rootPath']."pro/";
                     
                     break;
            }
            $uppath = $config['rootPath'].$expName.'/';
            $redir = mkdir($uppath);       //创建属于某个实验的文件夹
            $config['rootPath'] = $uppath;
            //$path = $this->getpath();
            $info = $this->upload->uploadFile($file,$config);   //调用文件上传类里面的单文件上传方法
            if($info){
                /*$old_name = $uppath. $info['savename'];
                $new_name = $uppath. $expName .'_' . $info['savename'];
               
                rename($old_name,$new_name);  //文件重命名 */        
                $result = $info['name'];
                if($files['name'][0]){
                    $exp_instruction_path = $result ? $uppath.$result : '';   // 实验信息文件路径
                    $arr['exp_instruction_path'] = $exp_instruction_path;
                    $name = explode('.',$result);
                    $fileName = $name[0];
                    $files_path =  $uppath.$fileName.".files";
                    if(!file_exists($files_path)){ //判断“以实验信息文件名为名的文件夹”是否存在
                        $res = mkdir($files_path);  //创建“以实验信息文件名为名的文件夹”
                        if(!$res){
                            $this->error('实验附件信息文件上传失败');
                        }
                    }

                    $config = array(
                        'maxsize' => 3145728,     //设置文件上传大小
                        'rootPath' => $uppath.$fileName.'.files/',   //设置文件上传目录
                        'autoSub' => false,    //不使用子目录保存，即不使用以“日期命名的文件夹”为目录
                        'saveName' => '',     //设置文件的文件名不变
                        'replace' => true,    //重名时覆盖
                    );
                    $attachment = $this->upload->uploadFiles($files,$config);   //上传实验附件信息文件
                    if(!$attachment){                                            //实验附件上传不成功
                        $this->error('实验附件信息文件上传失败');
                    }                          
                }
            }else{
                //$this->error('实验信息文件上传失败');
            }
        }
            //调用视频控制器中视频处理方法
            $video_upload = A('Admin/AdminVideo');
            if($video['name'][0]){
                 $video_path = $video_upload->insertOneVideo($video);
                 $arr['exp_video_path'] = $video_path;
            }
        return $arr;                  //返回路径数组信息
    }

/**
    *获取实验分类，当direction=0时，查询所有实验方向，当direction=1时查询所有实验分类，当direction=2时查询groupid对应的实验分类
    *
    *@access public 
    *@param mixed $direction 所属的方向
    *@param mixed $groupid   所属的分类
    *@since 1.0
    *@return array          对应的分类列表信息
*/
    public function showGroup($direction = 0,$groupid = 1){
       $this->getGroupList($direction,$groupid);
        if($direction == 0){
            $this->display('group');      //显示安全实验的实验方向
        }else if($direction == 3){
            $this->display('subgroupselect');
        }else{
            if($direction == 1){
                $this->groupid = 0;         //显示安全实验的所有实验分类
            }else{
                $this->groupid = $groupid;     //显示安全实验方向下的实验分类
            }
            $this->display('subgroup');
        }
    }
    public function getGroupList($direction = 0, $groupid = 1){
        if($direction == 0){ //查询所有的实验方向
            $expGroup = D('Admin/ExpGroup');
            $this->expGroupList = $expGroup->selectExpGroup();

        }else{
            $expSubGroup = D('Admin/ExpSubgroup');
            if($direction == 1){ //查询所有实验分类
                 $this->expSubGroupList = $expSubGroup->selectAllSubGroup();
            }
            else{ //查询groupid方向下对应的实验分类
                 $this->expSubGroupList = $expSubGroup->selectSubGroup($groupid);
            }           
        }
        
    } 
    public function getSubGroupSelect($groupid = 1){
        $this->getGroupList(2,$groupid);
        $this->display('subgroupselect');
    }

    //获取只有一级分类的项目
    public function getOneList($type){
        switch($type){
            case 1 : $table = D('Admin/TooltrainGroup');break;
            case 2 : $table = D('Admin/TarGroup');break;
            case 3 : $table = D('Admin/ProGroup');break;
        }
        $groupList = $table->selectGroup();
        $this->groupList = $groupList;
        $this->display('group');

    }
    public function searchAndListexp($direction=0,$groupid=1,$searchKeyWord=''){
        $creator = session('id');
        $exp = D('Admin/Exp');
        $type = 0;
        if($direction == 2){ //获取某个实验分类下的实验列表
            $expList = $exp->expOfSub($groupid,$searchKeyWord,$creator,$type);
        }else if($direction == 1){//获取某个实验方向下的实验列表
            $expList = $exp->expOfGroup($groupid,$searchKeyWord,$creator,$type);

        }else{//获取该用户所有实验
            $expList = $exp->expOfAll($searchKeyWord,$creator,$type);
        }
        $user = D('Admin/User'); 
        /*******************/
        foreach($expList as $key=>$value){
            $exp_creatorid = $value['exp_creatorid'];
            $exp_attribute = $value['exp_attribute'];//0为自建，1是系统的
            if($exp_attribute){//自建实验
                $creatorname = $user->selectTeaName($exp_creatorid);
                $exp_creator[] = $creatorname[0]['real_name'];    
            }else{              //系统实验
                $exp_creator[] = '系统';
            }
        }

        $this->expList = $expList;
        $this->exp_creator = $exp_creator;
        $this->searchKeyWord = $searchKeyWord;
        $this->display('exp');    
        /*******************/   
    }
 /**
    *查询除实验外的列表
    *
    *@access public
    *@param $direction 类型，1：工具培训，2：靶场，3：工程，4：工作站
    *@param $groupid  分类的id
    *@param $searchKeyWord 查询关键字
    *@since 1.0
    *@return  mixed        无                 
*/  
    public function searchAndListother($direction=1,$groupid=0,$searchKeyWord=''){
        $creator = session('id');
        $exp = D('Admin/Exp');
        $expList = $exp->selother($direction,$groupid,$searchKeyWord,1);
        $user = D('Admin/User'); 
        $this->expList = $expList;
        $this->exp_creator = $exp_creator;
        $this->searchKeyWord = $searchKeyWord;
        $this->display('exp');      
    }

/**
    *删除实验
    *
    *@access protected 
    *@since 1.0
    *@return  mixed              实验的类型。系统的：1，自建的：2               
*/
    public function deleteExp($del = array()){
        
       //$idArr = I('post.del');
        $idArr = $del;
        if($idArr == ""){  //未选择要删除的实验
            echo '未选择要删除的实验！';
        }else{
            $creator = session('id');  //获取删除人id
            $exp = D('Admin/Exp');
            $expTea = D('Admin/ExpTeacher');
            $expTeaStu = D('Admin/ExpStudent');
            $course_d = D('Admin/ImageCourse');    //初始化ImageCourse函数
            $instance_d = D('Admin/ImageInstance');   //初始化ImageInstance模型
            $domain = D('Admin/Domain');
            $domain_ip = D('Admin/DomainIpmapping');
            /*******************/
            //$expInfo = $exp->getExpInfo($idArr);  //获取所选实验的信息
            /*******************/
            $expInfo = $this->diffDel($idArr,$creator,$exp);
            foreach($expInfo as $e){
                $expId = $e['exp_id'];
                $info = $expTea->selectTeaid($expId,$creator); //获取exp_teacher表中的exp_teacher_id;
                $map['exp_teacher_id'] = $info['exp_teacher_id'];
                $map_exp['exp_id'] = $expId;
                
                $ip = $domain->get($map_exp); 
                $ipArr = '';
                foreach($ip as $key => $res){
                    $ipArr = $ipArr.$res['domain_ip'].",";
                } 
                $courseinfo = $course_d->getfields(array("exp_id"=>$expId),'img_course_path');
                
                $coursepath = $courseinfo['img_course_path']; 
                $instanceinfo = $instance_d->getfield(array("exp_id"=>$expId),'img_instance_path');
                $instancepath = $instanceinfo['img_instance_path'];
               // $map_delip['domain_ip'] = $ip[0]['domain_ip'];

                $explist = $exp->getExpInfo($expId,$creator);
                $path = $this->getpath();
                $exp_json_path = $explist[0]['exp_json_path'];
                $exp_video_path = $explist[0]['exp_video_path'];

                //首先获取.files目录进行删除，因为is_dir不会将其解读为目录，所以不能调用delDirAndFile（）
                $path = explode('/',$explist[0]['exp_instruction_path']);
                $filesname = explode('.',end($path));
                $count = count($path);
                $dirpath = '';
                foreach ($path as $key => $value) {
                    if($key == ($count-1)) break;
                    else{
                        $dirpath.= $value.'/';
                    }
                }
                $delfilesdir = $this->upload->delDirAndFile($dirpath.$filesname[0].'.files/',true);   //删除原来的实验.files文件夹
                if(is_dir($dirpath))
                    $deldir = $this->upload->delDirAndFile($dirpath,true);                  //删除原来的实验文件夹及他下面的文件

                //删除文件
                $deljson = $this->upload->delFile($exp_json_path); //删除json文件
                $del_video_path = $this->upload->delFile($exp_video_path);                        //删除视频文件
                $delimgcourse = $this->image->imageDelete($coursepath);   
                $delimginstance = $this->image->imageDelete($instancepath);                                                                  //删除课件镜像
                //调用秀梅镜像管理                                                       //删除实例镜像
                //删除数据表                                                                                       
                $flag1 = $expTea->del($expId);        //删除exp_teacher表记录
                $flag2 = $expTeaStu->del($map);       //删除exp_student表记录
                $flag3 = $course_d->del(array("exp_id"=>$expId));      //删除Image_course表记录
                $flag4 = $instance_d->del(array("exp_id"=>$expId));    //删除Image_instance表记录
                $flag5 = $domain_ip->del($ipArr); //删除domain_ipmapping表记录
                $flag6 = $domain->del($map_exp);      //删除domain表记录
                $flag7 = $exp->del($expId);           //删除exp记录  
            }

            echo true;
            return true;
        }

    }
/**
	*抽象删除实验时不同的地方
	*
	*@access protected 
	*@param mixed $idArr           要删除的id字符串序列，中间用逗号隔开
	*@param mixed $creator         创建者id
	*@since 1.0
	*@return  array                要删除的实验数组信息               
*/
    abstract protected function diffDel($idArr,$creator,$exp);

    /**
    *验证实验名是否已存在
    *
    *@access public 
    *@since  1.0
    *@return string               标识是否重名的字符串信息           
    */
    public function checkExpName(){
        $exp_name = I('get.exp_name');
        $exp = D('Admin/Exp');
        $list = $exp->selectExpName($exp_name);
        if(count($list)!=0){
            $this->ajaxReturn("repeat");
        }else{
            $this->ajaxReturn("success");
        }

    }

/********************************以下是虚拟机管理信息******************************/   
/********************************************************************************/
	//显示虚拟机开机后界面
    public function topoPreview(){
        
    }
    //检测虚拟机是否运行
    public function checkDomainStatus(){
       
    }

    // 虚拟机开机逻辑
    public function powerOn($expId,$deviceName){
        $this->virtual->powerOn($expId,$deviceName);
    }

    // 虚拟机关机逻辑
    public function powerOff($expId,$domainName){
        $this->virtual->powerOff($expId,$domainName);
    }

    // 虚拟机超时关机逻辑
    public function timeOut($expId,$domainName){
        $this->virtual->close($expId,$domainName);
    }
    
    // 获取ip地址
    public function getIP($expId)
    {
        $this->virtual->getIP($expId);
    }

    /*截屏*/
    public function snapShot($expId, $domainName, $imageName)
    {
       $this->virtual->snapShot($expId, $domainName, $imageName);
    }

    /*录屏*/
    public function recordScreen($expId, $domainName, $recordName, $button)
    {
        $this->virtual->recordScreen($expId, $domainName, $recordName, $button);
    }
    //连接libvirt
    public function libvirt_connect(){
    	Vendor("Virtual");
    	$this->virtual = new \Virtual();
    	return $this->virtual;
    }
}
    


















    
    

   

   
   

    
   

    
   
