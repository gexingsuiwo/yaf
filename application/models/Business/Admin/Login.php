<?php

namespace Business\Admin;

use Lib\Mysql\SqlBase;
use Lib\Cookies;
use Our\Model\Cache;

/**
 * 用户登录业务
 * @author wangliuyang
 */
class LoginModel extends \Our\Model\Base {
    
    private $_loginNum = 5;
    private $_loginFailCacheKey = 'login_fail_key';
    private $_loginCookieKey;
    private $_usersTable;
    
    public function __construct() {
        $this->_usersTable = new SqlBase('admin_users');
        $this->_loginCookieKey = Cache::getCookieKey('admin_login_num');
    }
    /**
     * 用户登录处理
     * @param array $data
     * @return boolean|Ambigous <multitype:, multitype:mixed string >
     */
    public function login($data)
    {
        if(!is_array($data) || !isset($data['user_name']) || !isset($data['passwd'])) return false;

        $condition = array(
            'user_name' => $data['user_name'],
            //'status' => 1,
        );
        
        $userInfoRes = $this->_usersTable->where($condition)->fRow();
       
        //$userInfoRes = $this->_fw_usersTables->fetch_row($condition);
        if(isset($userInfoRes['status']) && $userInfoRes['status'] <> 1) {
            return $this->_formatreturndata(false, '该账户已暂停，请联系管理员！');
        }
        if(isset($userInfoRes['user_name']))
        {
            if($userInfoRes['passwd'] == md5($data['passwd']))
            {
                $sigcode = $this->getSignCode($userInfoRes['id'], $userInfoRes['user_name']);
                setcookie('admin_uname', $userInfoRes['user_name'], 0, '/');
                setcookie('admin_uid', $userInfoRes['id'], time()+10800, '/');                
                setcookie('admin_realname', $userInfoRes['real_name'], 0, '/');
                setcookie('admin_sigcode', $sigcode, 0, '/');

                $update_data = array('last_login' => date('Y-m-d H:i:s'));
                $this->_usersTable->where('id=' . $userInfoRes['id'])->update($update_data);

                return $this->_formatReturnData(true);
            }
        }
        
        //登录失败记录
        $this->setLoginFail($data['user_name']);

        return $this->_formatReturnData(false);
    }

    /**
     * 退出登录
     * @return Ambigous <multitype:, multitype:mixed string >
     */
    public function logout()
    {
        setcookie('admin_uname', '', time()-3600, '/');
        setcookie('admin_uid', '', time()-3600, '/');
        setcookie('admin_realname', '', time()-3600, '/');
        setcookie('admin_sigcode', '', time()-3600, '/');
        setcookie('admin_groupid', '', time()-3600, '/');
        return $this->_formatReturnData(true);
    }
    
    
    /**
     * 验证是否登录
     * @return Ambigous <multitype:, multitype:mixed string >
     */
    public function isLogin()
    {
        //post传递cookie进行ajax操作
        //$this->set_post_cookie();

        $adminSigCode = isset($_COOKIE['admin_sigcode']) ? $_COOKIE['admin_sigcode'] : false;
        if($adminSigCode && isset($_COOKIE['admin_uid']))
        {
            $adminUid = $_COOKIE['admin_uid'];
            $adminUname = $_COOKIE['admin_uname'];
            $sigcode = $this->getSignCode($adminUid, $adminUname);
            
            if($adminSigCode == $sigcode)
            {
                $userInfoRes = $this->_usersTable->fRow($adminUid);
                if(isset($userInfoRes['id']))
                {
                    return $this->_formatreturndata(true, $userInfoRes);
                }
            }
        }
        return $this->_formatReturnData(false);
    }
    
 
    
    /**
     * 返回登录sig code
     * @param int $user_id
     * @param string $user_name
     */
    private function getSignCode($user_id, $user_name)
    {
        return md5($user_id.$user_name.SALES_KEY);
    }
    
    
    /*
     * 获取登陆次数
     */
    public function getLoginNum($userName) {
        $cacheKey = Cache::getRedisKey($this->_loginFailCacheKey,array($userName));
        $redis = new \Lib\Base\Redis();
        $cacheValue = $redis->get($cacheKey);
        $cacheValue = json_decode($cacheValue, TRUE);
        
        if($cacheValue)
        {
            $cacheValue['use_num'] = $this->_loginNum - $cacheValue['fail_num'];
        }
        else
        {
            $cacheValue = array('fail_num' => 0, 'use_num' => $this->_loginNum);
        }

        return $cacheValue;        
    }
    
    /**
     * 获取当前操作用户登录失败次数
     */
    public function getUserLoginNum()
    {
        $loginFailNum = Cookies::getCookie($this->_loginCookieKey);
        $data['fail_num'] = $loginFailNum ? $loginFailNum : 0;
        $data['use_num'] = $this->_loginNum - $data['fail_num'];
        return $data;
    }
    
    /**
     * 更新登录失败次数
     * @param unknown $username
     */
    public function setLoginFail($userName)
    {
        
    	$cacheKey  = Cache::getRedisKey($this->_loginFailCacheKey,array($userName));
    	$cacheTime = Cache::getRedisKeyTime($this->_loginFailCacheKey);
    	$redis = new \Lib\Base\Redis();
    	$cacheValue = $redis->get($cacheKey);
        $cacheValue = json_decode($cacheValue, TRUE);
    	if($cacheValue)
    	{
    		$cacheValue['fail_num'] += 1;
    	}
    	else
    	{
    		$cacheValue = array('fail_num' => 1);
    	}
               
    	$redis->set($cacheKey, json_encode($cacheValue));
        
        $redis->setTimeout($cacheKey, $cacheTime);
                
    	//当前操作用户失败次数
    	$loginFailNum = Cookies::getCookie($this->_loginCookieKey);
    	if($loginFailNum)
    	{
    		Cookies::setCookie($this->_loginCookieKey, $loginFailNum+1, time()+$cacheTime, '/');
    	}
    	else
    	{
    	    Cookies::setCookie($this->_loginCookieKey, 1, time()+$cacheTime, '/');
    	}        

    }    


}
