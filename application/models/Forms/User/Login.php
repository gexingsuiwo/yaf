<?php

namespace Forms\User;

/**
 * 用户登录表单
 */
class LoginModel extends \Forms\AbstractModel {

    /**
     * 表单字段
     * 
     * @var array
     */
    protected $_fields = array(
        'user_name'   => array(
            'label'    => '用户名',
            'name'     => 'user_name',
            "require"  => true,
            'message' => '用户名不能为空',
            "validate" => array(
                array("type" => "string", "min" => "6", "msg" => "用户名长度最少6位")
            ),
        ),        
        'passwd' => array(
            'label'    => '密码',
            'name'     => 'passwd',
            "require"  => true,
            'message' => '密码不能为空',
            "validate" => array(
                array("type" => "string", "min" => "6", "max" => "18", "msg" => "密码长度6到18位")
            ),
        ),
        'code' => array(
            'label'    => '验证码',
            'name'     => 'code',
            "require"  => true,
            'message' => '验证码不能为空',
        ),
     
            
    );
    
    /**
     * 校验demo2字段，名字由validate+字段名开头的方法将被调用
     *
     * @return boolean
     */
    public function validatePasswd() {
        $passwd = $this->getFieldValue("passwd");
        //这里可以进行更加复杂的校验
        if($passwd) {
            //var_dump($passwd);
        }
        return true;
    }    

}
