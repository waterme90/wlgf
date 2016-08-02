<?php
/** 
*系统管理控制器 
* 
* @author      liuxiumei<853557362@qq.com> 
* @version      
* @since        1.0 
* @time   		2016-4-26
*/  
namespace Admin\Controller;
use Common\Controller\LoginController;
class SystemController  extends LoginController
{
	private $image_path = '/var/lib/libvirt/images/';
	private $cgi_bin = '/var/www/cgi-bin/';
    private $etc = '/etc/network/interfaces';
    private $recordVideo = "/home/www-data/wlgf/RecordVideo/";
    private $image_basic = "/var/www/lib/libvirt/images/basic/ ";
    
    private $image_course = "/var/www/lib/libvirt/images/course/ ";
    private $image_instance = "/var/www/lib/libvirt/images/instance/ ";
    private $tool = "/home/www-data/wlgf/Toolbox/";
    private $video = "/home/www-data/wlgf/Video/";
	/***************begin 镜像管理*****************************/

  /** 
  * 镜像列表显示界面 
  *
  * @return  
  */
	public function imageIndex($attribute = 'basic'){
		$this->attribute = $attribute;
		$this->display('imageIndex');
	}
	/** 
	* 镜像列表显示 
	* 显示基础镜像、课件镜像、实例镜像的列表
	* 
	* @param mixed $attribute 属性 basic：基础镜像；course：课件镜像；instance:实例镜像
	* @param mixed $type 所属的类型 all:全部；exp:实验；pro:工程；tar:靶场
	* @since 1.0 
	* @return  array 镜像的信息
	*/
	public function imageList($attribute = 'basic',$type = "all",$searchtext = '')
	{
		$where = array();

		switch($attribute)
		{
			case "basic":
				$tableName  = 'ImageBasic';
				if($searchtext){
					$where['img_basic_name'] = array("like",$searchtext."%");
					$where['img_basic_os'] = array("like",$searchtext."%");
					$where['_logic'] = "or";
				}
				
				$image = D($tableName);
				$list = $image->sel($where);
				
				break;
			case "course":
				if($searchtext){
					$where['img_course_name'] = array("like",$searchtext."%");

					$where['exp_name'] = array('like',$searchtext."%");
					$where['_logic'] = "OR";
				}
				$tableName = 'ImageCourse';
				$image = D($tableName);
				$user = M("User");
				$basicImage = D("ImageBasic");
				$list = $image->seljoinexp($where,"exp_name,exp_creatorid,i.*");

				foreach ($list as $key => $img) {
					$exp_attribute = $img['exp_attribute'];
					$creator_id = $img['exp_creatorid'];
					if(!$exp_attribute){
						$listUser = $user->where(array('user_id'=>$creator_id))->field('real_name')->find();
						$list[$key]['img_creator'] = $listUser['real_name'];
					}else{
						$list[$key]['img_creator'] = "系统";
					}
					$basic_id = $img['img_basic_id'];
					$listBasic = $basicImage->getfields(array('img_basic_id'=>$basic_id),"img_basic_name");
					$list[$key]['img_basic_name'] = $listBasic[0]['img_basic_name'];
				}
				break;
			case "instance":
				if($searchtext){
					$where['img_instance_name'] = array("like",$searchtext."%");
					$where['img_course_name'] = array("like",$searchtext,"%");
					$where['real_name'] = array('like',$searchtext."%");
					$where['exp_name'] = array('like',$searchtext."%");
					$where['_logic'] = "OR";
				}
				$tableName = 'ImageInstance';
				$image = D($tableName);
				$list = $image->seljoinall($where,"img_instance_id,img_instance_name,real_name,img_instance_cretime,img_instance_path,exp_name,img_course_name");
				
				break;
			default:break;
		}
		
		
		
	    vendor('image');
	    $imageObj = new \imageManagement();
	    foreach ($variable as $key => $value) {
	    	# code...
	    }
	     $this->attribute = $attribute;
	    $this->searchtext = $searchtext;
		$this->list = $list;
		$this->display();
	}
	
	/** 
	* 镜像上传界面 
	*
	* @return  
	*/
	public function imageAdd()
	{
	    $m = D('ImageBasicGroup');
	    $fields = "DISTINCT(group_id),group_name";
	    $group = $m->getfields($fields);
	    $this->group = $group;
	    $subgroup = $m->getfields('subgroup_id,subgroup_name');
	   
	    $this->subgroup = $subgroup;
	    $this->display();
	}
	public function getSubgroup($group_id)
	{
		if(!is_numeric($group_id)) return '';
		$m = D('ImageBasicGroup');
		$data = $m->getfields('subgroup_id,subgroup_name',array('group_id'=>$group_id));
		$this->ajaxReturn($data);
	}
	/** 
	* 检测镜像名称是否存在 
	*
	* @param string $img_basic_name 输入框中填写的基础镜像的名称
	* @return  string 存在：repeat；不存在：success
	*/
	public function imageNameCheck($img_basic_name = '')
	{
	  	$mImageBasic = D('ImageBasic');
	  	$res = $mImageBasic->sel(array('img_basic_name'=>$img_basic_name));
	  	$r = (count($res)==0)?"success":"repeat";
	  	$this->ajaxReturn($r);
	}
  	/** 
	* 镜像上传 
	* 上传基础镜像
	* 
	* @param mixed $data 上传镜像的参数
	* @return  
	*/
	public function imageUpload()
	{
		
	    $rootPath = $this->imagePath."basic"."/";
	    chmod($rootPath,777);
			$config = array(
				'rootPath' => $rootPath,
				'savePath' => '',
				'saveName' => '',
				'exts'	   => array('img'),
				'autoSub'  => false,
				'subName'  => '',
				);
	    $upload = new \Think\Upload($config);
	    $res = $upload->uploadOne($_FILES['file']);
		if($res)
		{
			vendor('image');
	    	$imageObj = new \imageManagement();
	    	$path = $rootPath.$_FILES['file']['name'];
	    	$arr = $imageObj->imageInfo($path);
	    	$size = (int)($arr['size']/1024);
		    $mbasicgruop = D('ImageBasicGroup');
		    $grouplist = $mbasicgruop->getfields('group_name,subgroup_name',array('subgroup_id'=>I('post.subgroup_id')));
		    $newdata = array(
		        'img_basic_name'=>I('post.img_basic_name'),
		        'img_basic_os'=>I('post.img_basic_os'),
		        'img_basic_num'=>I('post.img_basic_num'),
		        'img_basic_group'=>$grouplist[0]['group_name'],
		        'img_basic_subgroup'=>$grouplist[0]['subgroup_name'],
		        'img_basic_attribute'=>1,
		        'img_basic_size'=>$size."KB",
		        'img_basic_path'=>$path,
		    );
	      	$mImageBasic = D('ImageBasic');
	      	$mImageBasic->create($newdata);
	      	$res = $mImageBasic->add();
		}
		if($res) {
      		$this->success('上传成功',U('admin.php/System/imageIndex',array('attribute'=>'basic')));
			
		}else{
     		$this->error($upload->getError(),U('admin.php/System/imageIndex',array('attribute'=>'basic')));
		}
	}
	public function imageEdit($imgId)
	{
		$mImageBasic = D("ImageBasic");
		$list = $mImageBasic->sel(array('img_basic_id'=>$imgId));
		foreach ($list as $key => $image) {
			$subgroup_name = $image['img_basic_subgroup'];
			$mGroup = D('ImageBasicGroup');
			$grouplist = $mGroup->sel(array('subgroup_name'=>$subgroup_name));
			$subgroup_id = $grouplist[0]['subgroup_id'];
			$subgroup_name = $grouplist[0]['subgroup_name'];
			$list[$key]['subgroup_id'] = $subgroup_id;
			$list[$key]['subgroup_name'] = $subgroup_name;
		}
		 $m = D('ImageBasicGroup');
	    $fields = "DISTINCT(group_id),group_name";
	    $group = $m->getfields($fields);
	    $this->group = $group;
	    $subgroup = $m->getfields('subgroup_id,subgroup_name');
	   
	    $this->subgroup = $subgroup;

		$this->list = $list;
		$this->display();
	}
	public function imageUpdate($imgId){
		$newdata = array(
		        'img_basic_name'=>I('post.img_basic_name'),
		        'img_basic_os'=>I('post.img_basic_os'),
		        'img_basic_num'=>I('post.img_basic_num'),
		        'img_basic_group'=>$grouplist[0]['group_name'],
		        'img_basic_subgroup'=>$grouplist[0]['subgroup_name'],
		        'img_basic_attribute'=>1,
		        'img_basic_size'=>$size
		);
		$mImageBasic = D('ImageBasic');
	    $mImageBasic->update(array('img_basic_id'),$newdata);
	}
	public function imageDelete($type="basic",$str = array())
	{
		
		switch($type){
			case "basic":
				foreach ($str as $key => $image_id) 
				{
					$mImage= D('ImageBasic');
					$res = $mImage->del(array('img_basic_id'=>$image_id));
				}
				break;
			case "course":
				foreach ($str as $key => $image_id) 
				{
					$mImage= D('ImageCourse');
					$res = $mImage->del(array('img_course_id'=>$image_id));
				}
				break;
			case "instance":
				foreach ($str as $key => $image_id) 
				{
					$mImage= D('ImageInstance');
					$res = $mImage->del(array('img_instance_id'=>$image_id));

				}
				break;
		}
		if($res){
			$this->ajaxReturn("success");
		}else{
			$this->ajaxReturn("error");
		}
		
	}
	public function imageRenew($str = array())
	{
		foreach ($arrImageBasic as $key => $ImageBasic) 
		{
			$mImageBasic= D('ImageBasic');
			$res = $mImageBasic->repaire(array('img_basic_id'=>$ImageBasic));
			if($res){
				$this->ajaxReturn("success");
			}else{
				$this->ajaxReturn("error");
			}
		}
	}
	
	/***************end**********************************/
	/***************begin 虚拟机管理*****************************/
	public function domainIndex(){
		$this->display();
	}
	/** 
	* 开启的虚拟机列表显示 
	* 显示实验工程靶场等模块下开启的虚拟机列表
	* 
	* @access public 
	* @param mixed $type 所属的类型 all:全部；0：技术专项；1：工具培训；2：靶场集训；3：工程应用
	* @param mixed $searchtext 搜索关键字
	* @since 1.0 
	* @return  array 虚拟机的相关信息
	*/
	public function domainList($type = 'all',$searchtext = '')
	{
		$map = array();
		if($type!='all'){
			if(is_numeric($type)){
				$map['e.type'] = $type;
			}
		}
		if($searchtext!='') {
			$where['exp_name'] = array('like',$searchtext."%");
			$where['domain_name'] = array('like',$searchtext."%");
			$map['_complex'] = $where;
		}
		$fields = "domain_id,domain_name,exp_name,domain_ip,d.host_id,host_ip,d.user_id,real_name";
		$m = D('Domain');
		$list = $m->joinAll($map,$fields);
		$this->list = $list;
		$this->display();
	}
	/** 
	* 虚拟机关闭
	* 关闭虚拟机且删除虚拟机镜像
	* @access public 
	* @param mixed $str 虚拟机ID数组
	* @since 1.0 
	* @return  
	*/
	public function domainShutDown($str = array())
	{

		$errorID = array();//保存未正常处理的domainID
		vendor('imageManagement');
		$imageManagement = new \imageManagement();
		vendor('Libvirt');

		$domain = M('Domain');
		$imgInstance = M('ImageInstance');
		foreach ($str as $key => $domainid) 
		{
			$domains = M()->table('domain d,host h')->where('d.host_id = h.host_id')
						  ->where(array('domain_id'=>$domainid))->select();

			foreach ($domains as $key2 => $domain) 
			{
				//关闭虚拟机
				$domainName = $domain['domain_name'];
				$hostip = $domain['host_ip'];
				$lv = connectlv($hostip);
				$this->closeDomain($lv,$domainName);
				
				//实例镜像则删除虚拟机镜像，调用image类的imageDelete($path)方法

				$images = $imageInstance->sel(array('img_instance_name'=>$domainName));
				$imagePath = $images[0]['img_instance_path'];
				$res = $imageManagement->imageDelete($imagePath);
				if(!$res)
				{
					$errorID[] = $domainid; 
				}
			}

		}
		if(count($errorID)==0) 
		{
			$this->ajaxReturn("success");
			//全部正确处理
		}else
		{
			//没有正确处理的ID
			$this->ajaxReturn("error");
			
		}

	}
	/***************end 虚拟机管理*****************************/
	
	/***************begin 集群管理*****************************/
	/** 
	* 添加集群节点
	* 
	* @access public 
	* @since 1.0 
	* @return  
	*/
	public function hostList(){
		$mHost = D('Host');
		$mDomain = D('Domain');
		$where = array();
		$fields = 'h.host_id,host_ip,host_name,host_state';
		$list = $mHost->joinDomain($where,$fields);
		foreach($list as $key=>$host){
			$host_id = $host['host_id'];
			$domain = $mDomain->field('count(*) as domain_num')->where(array('host_id'=>$host_id))->find();
			$domain_num = $domain['domain_num'];
			$list[$key]['domain_num'] = $domain_num;
		}
		$this->list = $list;
		$this->display();
	}
	public function hostNew(){
		$this->display();
	}
	public function hostInsert(){
		$dataInput = array(
			"host_ip"		=>'',
			"host_role"		=>1,
			"host_state"	=>1,
			"host_#instance"=>0,
			"host_name"		=>'',
			);
		if(!filter_var(I("post.ip"),FILTER_VALIDATE_IP)){
			$this->error("IP错误！",'hostNew');
		}else{
			$dataInput['host_ip'] = I("post.ip");
		}
		if(I("post.role")!=0&&I("post.role")!=1){

			$this->error("角色错误！",'hostNew');
		}else{
			$dataInput['host_role'] = intval(I("post.role"));
		}
		if(empty(I('post.name'))) $dataInput['host_name'] = "未命名";
		else $dataInput['host_name'] = strval(I("post.name"));
		
		$mHost = M("Host");
		$res = $mHost->add($dataInput);
		if(!$res) $this->error("写入失败！",'hostNew');
		$this->success('添加成功!','hostIndex');
	}

	
	
	/** 
	* 删除集群删除
	* 
	* @access public 
	* @param mixed $str 集群节点ID数组
	* @since 1.0 
	* @return  
	*/
	public function hostRemove($str = array())
	{
		
		//vendor('Libvirt');
		$host = D('Host');
		foreach ($str as $key => $hostid) 
		{
			//关闭该节点上运行的所有虚拟机
			//if(!filter_var($hostip,FILTER_VALIDATE_IP)) continue;
			// $lv = $this->connectlv($hostip);
			// if(!$lv) continue;
			// $this->shutdownAll($lv);
			//删除数据库记录
			$host->del(array('host_id'=>$hostid));

		}
		
		$this->ajaxReturn(array('info'=>'删除成功!'));
		//返回处理结果
		return true;
	}
	/** 
	* 关闭节点服务器
	* 
	* @access public 
	* @param mixed $str 集群节点的ID数组
	* @since 1.0 
	* @return  
	*/
	public function hostShutdown($str = array())
	{
		if(is_array($str)) return '';
		foreach ($str as $key => $host_id) {
			if(!is_numeric($host_id)) continue;
			$mHost = D('Host');
			$list = $mHost->getFied(array('host_id'=>$host_id),'host_ip');
			//filter_var($)
			$host_ip = $list[0]['host_ip'];
			if($host_ip!=$_SERVER['SERVRER_ADDR']){
				//ssh连接到相应的服务器
				//exec("sudo ssh $host_ip");
				//关闭该服务器
			}
		}
	}
	/** 
	* 连接服务器服务器
	* 
	* @access public 
	* @param mixed $hostip 服务器的IP
	* @since 1.0 
	* @return  resource libvirt对象
	*/
	public function connectlv($hostip = ''){
		Vendor('Libvirt');              // 引用第三方类库
        if(is_null($hostip))
            $lv = new \Libvirt('qemu+unix:///system');
        else 
            $lv = new \Libvirt("qemu+tcp://".$hostip."/system");
        
        $hn = $lv->get_domainName();
        if(!$hn) return false;
        return $lv;
	}
	public function shutdownDomain($lv,$domain)
	{
		if($lv->domain_is_running($domain)){
			$ret = $lv->domain_destroy($domain);
		}
		// ? "Domain has been destroyed successfully" : 'Error while destroying domain: '.$lv->get_last_error();
        
        $lv->domain_undefine($domain);//删除虚拟机
       

        return $ret;
	}
	public function shutdownAll($lv)
	{
		$domains = $lv->list_domains();
		foreach ($domains as $key => $domain) 
		{
			$res = $this->closeDomain($lv,$domain);
		}
		return true;	
	}
	/** 
	* 关闭节点上的全部虚拟机
	* 
	* @access public 
	* @param mixed $hostidArr 虚拟机ID数组
	* @since 1.0 
	* @return  
	*/
	public function domainsShutdown($hostips = array())
	{
		$hostipArr = array();
		if(!is_array($hostips)){
			$hostipArr[] = $hostips;
			
		}else{
			if(count($hostips)== 0 ) return ;
			$hostipArr = $hostips;
		}
		
		foreach ($hostipArr as $key => $hostip) 
		{
			
			//关闭该节点上运行的所有虚拟机
			$lv = $this->connectlv($hostip);
			$this->shutdownAll($lv);

			//执行删除节点的脚本
			exec();
		
			//删除数据库中虚拟机的记录
			$host = M('Host');
			$hostids = $host->where(array('host_ip'=>$hostip))->getFied('host_id');
			foreach ($hostids as $key => $hostid) {
				$d = M('Domain');
				$d->where(array('host_id'=>$hostid))->delete();
			}
			

		}
		//返回处理结果
		return true;
		
	}

	/****begin网络管理**********************************************/
	public function networkIndex()
  	{
    	$arr = $this->networkInfo();
    	$this->network = $arr;
    	$this->display('networkIndex');
  	}
    /** 
	* 获取服务器的网络设置
	* 
	* @access public 
	* @since 1.0 
	* @return $arr 服务器网络配置信息的数组
	*/
    public function networkInfo()
    {
    	$interfaces = $this->etc;
        $fp = fopen($interfaces,'r');         //以只读模式打开
        if(!$fp) return '';
    
        for($i=1; !feof($fp); $i++){
            $str = "";
            $content = fgets($fp);//读一行数据
            for($j=0; $j<7; $j++){
                $str .= $content[$j];
            }
            $str1 = "";
            switch ($str) {
                case 'address':
                    for($j=8; $j<strlen($content); $j++){
                        $str1 .= $content[$j];
                    }
                    $address = $str1;
                    break;
                case 'netmask':
                    for($j=8; $j<strlen($content); $j++){
                        $str1 .= $content[$j];
                    }
                    $netmask = $str1;
                    break;
                case 'gateway':
                    for($j=8; $j<strlen($content); $j++){
                        $str1 .= $content[$j];
                    }
                    $gateway = $str1;
                    break;
                case 'dns-nam':
                    for($j=16; $j<strlen($content); $j++){
                        $str1 .= $content[$j];
                    }
                    $dns = $str1;
                    break;
                default:
                    break;
            }
            
            
        }
        $arr = array('address'=>$address,'netmask'=>$netmask,'gateway'=>$gateway,'dns'=>$dns);
        fclose($fp);
        return $arr;
    }
    /** 
	* 修改服务器的网络设置
	* 
	* @access public 
	* @since 1.0 
	* @return  
	*/
    public function networkUpdate()
    {
        header('Content-type:text/html;charset=utf8');
        $ip = I('post.address');
        $mask = I('post.netmask');
        $gateway = I('post.gateway');
        $dns=I('post.dns');
        if(!filter_var($ip, FILTER_VALIDATE_IP))
        {
            $this->error("IP地址不合法",'networkIndex');
        }
        else
        {
            $ip_sh = $this->cgi_bin.'ip.sh';
            //exec("$ip_sh $ip $mask $gateway $dns",$res,$rec);
            if(!$rec){
                $this->success('设置成功','networkIndex');
            }else{
               $this->error("设置失败",'networkIndex');
            }
           
        }
    }
  
    /** 
	* 检测是否对网络配置信息做了修改
	* 
	* @access public 
	* @since 1.0 
	* @return  
	*/
    public function networkCheck()
    {
        $address_new = I('get.add');
        $net_new = I('get.net');
        $gate_new = I('get.gate');
        $dns_new = I('get.dns');
        $oldInfo = $this->networkInfo();

        $address_old = $oldInfo['address'];
        if(trim($address_new)==trim($oldInfo['address']) && trim($net_new)==trim($oldInfo['netmask']) && trim($gate_new)==trim($oldInfo['gateway']) && trim($dns_new)==trim($oldInfo['dns']))
        {
        	//信息未修改
        	$this->ajaxReturn("repeat");
        }
        else
        {
        	$this->ajaxReturn("modified");
        }
        
    }
    /****end网络管理**********************************************/


    /****begin课件管理**********************************************/
    public function expIndex(){
    	$this->display();
    }
    /** 
	* 课件列表
	* 显示安全试验、工程应用、靶场、操作台等的实验
	* @access public 
	* @param imxed $type 实验类型 all:全部 0:技术专项；1：工具培训；2：靶场集训；3：工程应用
	* @param mixed $searchtext 搜索的关键词
	* @return  array 
	*/
    public function expList($type = 'all',$searchtext='')
    {
    	$where = array();
    	$map = array();
    	if($type!='all'){
    		if($type == 0 || $type == 1 || $type == 2 || $type == 3){
    			$where['type'] = $type;
    		}
    	}else{
    		$where['type'] = array('IN','0,1,2,3');
    	}
    	if($searchtext!=''){
    		$map['exp_name'] = array('LIKE',$searchtext."%");
    		
    	}
    	
    	$exp = D('Exp');
        $list = $exp->where($where)->where($map)->select();
        $this->list = $list;
        $this->display();
    }
    
    /** 
	* 课件导入
	* 导入完整课件压缩包  
	* @param $str 实验ID数组                                     
	* @return  boolean 成功:true;失败:false 
	*/
    public function expImport($str = array())
    {
    	$failExp = array();//保存导出失败的实验ID
    	//执行课件导入脚本
    	foreach ($str as $key => $exp_id) {
    		exec("sudo /var/www/cgi-bin/eximport.sh $exp_id",$res);
    		if($res){
    			$failExp[] = $exp_id;
    		}
    	
    	}
    	if(count($failExp)!==0){
    		//return $failExp;
    		$this->ajaxReturn(array("info"=>"导入失败！"));
    	}
    	$this->ajaxReturn(array("info"=>"导入成功!"));

    	
    }
    /** 
	* 课件导出
	* 导出完整的课件，包括课件相关文件、数据记录、镜像、拓扑等
	* @access public 
	* @param mixed $str 需要导出的实验ID数组
	* @return  mixed 0:成功 其他：导出失败的实验ID
	*/
    public function expExport($str = array())
    {
    	$failExp = array();//保存导出失败的实验ID
    	
    	//执行课件导出脚本
    	foreach($str as $key => $exp_id)
    	{

    		// $exp_id = $expinfo['exp_id'];
    		// $html = (!isset($expinfo['html'])) ? '' : '-html';
    		// $topu = (!isset($expinfo['topu'])) ? '' : '-json -img';
    		// $video = (!isset($expinfo['video'])) ? "" : "-video";
    		// if(is_null($html) && is_null($topu)  && is_null($video))
    		// {
    		// //只有实验ID有值时将该实验的全部信息都导出
    		// 	exec("sudo /var/www/cgi-bin/export.sh $exp_id -html -json -img -video",$res);

    		// }
    		// else
    		// {
    		// 	exec("sudo /var/www/cgi-bin/export.sh $exp_id $html $topu $video",$res);
    		// }
    		exec("sudo /var/www/cgi-bin/export.sh $exp_id -html -json -img -video",$res);

    		if($res) $failExp[] = $exp_id;
    	}
    	
    	if(count($failExp)!==0){
    		//return $failExp;
    		$this->ajaxReturn(array("info"=>"导出失败！"));
    	}
    	$this->ajaxReturn(array("info"=>"导出成功!"));
    }
    /** 
	* 课件删除
	* 删除完整的课件
	* @access public 
	* @param mixed $str 实验ID数组
	* @return  
	*/
    public function expDelete($str = array())
    {
    	$exp = A('Admin/Exp');
    	$res = $exp->deleteExp($str);
    	if($res){
    		$this->ajaxReturn(array("info"=>"删除成功！"));
    	}else{
    		$this->ajaxReturn(array("info"=>"删除失败！"));
    	}
    }

    /****end课件管理**********************************************/

    /****begin磁盘管理**********************************************/
    public function diskIndex()
    {
    	$recordVideoSize = $this->dirsize($this->recordVideo);
    	$imageSize = $this->dirsize($this->image_path);
    	$this->recordVideoSize = (int)($recordVideoSize/1024/1024);
    	$this->imageSize = (int)($imageSize/1024/1024);
    	$this->display();
    }

    /****end磁盘管理**********************************************/

    public function dirsize($dir) 
    { 
		@$dh = opendir($dir); 
		$size = 0; 
		while ($file = @readdir($dh)) { 
			if ($file != "." and $file != "..") { 
				$path = $dir."/".$file; 
				if (is_dir($path)) { 
					$size += dirsize($path); 
				} elseif (is_file($path)) { 
					$size += filesize($path); 
				} 
			} 
		} 
		@closedir($dh); 
		return $size; 
	} 
}