<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-3-8
 * Time: 下午4:29
 */

namespace Home\Controller;

use Common\Controller\CommonController;

class ModifyMemberController extends CommonController {
    /**
     * 添加个人信息
     */
    public function modify(){
        //判断是否是已经完成reg基本注册
       $login=$this->checkLogin();
       if(!$login){
      	 	$this->redirect('User/index');
       		return;
       }
       if(session('STATUS')!=0){
            $this->redirect('User/index');
            return;
        }
        if(IS_POST){
            $M_member = D('Member');
            $id = session('USER_KEY_ID');
            $_POST['member_id']=$id;
            $_POST['status'] = 1;//0=有效但未填写个人信息1=有效并且填写完个人信息2=禁用
            if (!$data=$M_member->create()){ // 创建数据对象
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $data['status'] = 0;
                $data['info'] = $M_member->getError();
                $this->ajaxReturn($data);
//                $this->error($M_member->getError());
                return;
            }else {
                $where['member_id'] = $id;
                $r = $M_member->where($where)->save();
                if($r){
                    session('procedure',2);//SESSION 跟踪第二步
                    session('STATUS',1);
                    $data['status'] = 1;
                    $data['info'] = "Submitted successfully";
                    $this->ajaxReturn($data);
//                    $this->redirect('Reg/regSuccess');
                }else{
                    $data['status'] = 0;
                    $data['info'] = 'The server is busy, please try again later';
                    $this->ajaxReturn($data);
//                    $this->error('The server is busy, please try again later');
//                    return;
                }
            }
        }else{
            $this->display();
        }
    }
    /**
     * ajax验证昵称是否存在
     */
    public function ajaxCheckNick($nick){
        $nick = urldecode($nick);
        $data =array();
        $M_member = M('Member');
        $where['nick']  = $nick;
        $r = $M_member->where($where)->find();
        if($r){
            $data['msg'] = "Nickname is already occupied";
            $data['status'] = 0;
        }else{
            $data['msg'] = "";
            $data['status'] = 1;
        }
        $this->ajaxReturn($data);
    }
    /**
     * ajax手机验证
     */
    function ajaxCheckPhone($phone) {
        $phone = urldecode($phone);
        $data = array();
        if(!checkMobile($phone)){
            $data['msg'] = "手机号不正确！";
            $data['status'] = 0;
        }else{
            $M_member = M('Member');
            $where['phone']  = $phone;
            $r = $M_member->where($where)->find();
            if($r){
                $data['msg'] = "This phone has been bundled! Please change your mobile number";
                $data['status'] = 0;
            }else{
                $data['msg'] = "";
                $data['status'] = 1;
            }
        }
        $this->ajaxReturn($data);
    }

    /**
     * ajax验证手机验证码
     */
    public function ajaxSandPhone(){
        $phone = urldecode(I('phone'));
        if(empty($phone)){
            $data['status']=0;
            $data['info'] = "Phone number can not be blank";
            $this->ajaxReturn($data);
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){  
            $data['status']=-1;
            $data['info'] = "the phone number is incorrect";
            $this->ajaxReturn($data);
        }  
        $user_phone=M("Member")->field('phone')->where("phone='$phone'")->find();
        if (!empty($user_phone)){
            $data['status']=-2;
            $data['info'] = "Mobile number already exists";
            $this->ajaxReturn($data);
        }
        $r = sandPhone($phone,$this->config['CODE_NAME'],$this->config['CODE_USER_NAME'],$this->config['CODE_USER_PASS']);
		if(!$r[1]){
			$data['status']=1;
        	$data['info']="Sent successfully";
        	$this->ajaxReturn($data);exit;
		}else{
			$data['status'] =-3;
        	$data['info']=chuanglan_status($r[1]);
        	$this->ajaxReturn($data);exit;
		}
 
        
//         if($r!="短信Sent successfully"){
//             $data['status']=0;
//             $data['info'] = $r;
//             $this->ajaxReturn($data);
//         }else{
//             $data['status']=1;
//             $data['info'] = $r;
//             $this->ajaxReturn($data);
//         }
    }
}