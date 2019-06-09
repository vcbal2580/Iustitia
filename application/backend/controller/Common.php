<?php
namespace app\backend\controller;
use  think\Controller;
use think\Db;
use think\Cookie;
//后台公共控制器
    class Common extends Controller
    {
        public $_user= [];//保存用户信息
        public $is_check_rule = true ; //是否识别是否有权限
       public function __construct()
       {
           //避免重写父类构造方法
           parent::__construct();
         
           //token校验
           if(config("is_check_token")){
            if(request()->isPost()){
				$token = input('login_token');//接受表单所提交的token值
				$session_token = session('login_token');//获取session中记录的token值
				// 如果表单没有生成令牌 或者所提交的令牌与session中不一致
				if(!isset($token) || !isset($session_token) || $token != $session_token){
					$this->error('登录失效');
				}
				// 销毁在session中的令牌
				session('login_token',null);
			}
           }
           //校验用户是否登录
           if(!cookie('admin_info')){
               $this->error('想搞什么小老弟？登录下','login/index');
           }
           //将用户信息保存
           $this->_user= cookie('admin_info');
           $this->_user['role_info']= Db::name('role')->find($this->_user['role_id']);
            //判断角色对应的权限
            if($this->_user['role_id']== 1){
                //超级管理员所有权限
                $rules =Db::name('rule')->select(); $this->is_check_rule =false;
            }else{
                //普通角色下的用户
                $rules =Db::name('rule')->where('id','in',$this->_user['role_info']['role_id'])->select();
            }
           $this->_user['rules']=[];//保存权限的一维数组
           foreach($rules as $key => $value) {
               //提取导航菜单的信息
               if($value['is_show'] == 1){
                   $this->_user['menus'][]=$value;
               }
               //过滤重复数据
               $key = strtolower($value['controller_name'].'/'.$value['action_name']);
               if(!in_array($key,$this->_user['rules'])){
                   $this-> _user['rules'][]=$key;
               }
               //具体检查是否有权限访问
               if($this->is_check_rule){
                   //增加后台权限，避免什么都不显示
                   $this->_user['rules'][]='index/index';
                   $this->_user['rules'][]='index/top';
                   $this->_user['rules'][]='index/menu';
                   $this->_user['rules'][]='index/main';
                   //获取当前用户访问的地址
                   $url = strtolower(request()->controller().'/'.request()->action());
                   if(!in_array($url,$this->_user['rules'])){
                       if(request()->isAjax()){
                           return json(['status'=>0,'msg'=>'汝未得入']);
                       }else{
                           $this->error('没有权限禁止入内');
                       }

                   }
               }
           }
            
           
       }
    }
?>