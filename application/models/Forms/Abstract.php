<?php

namespace Forms;

/**
 * 表单抽象模型
 */
class AbstractModel {

    /**
     * 表单字段
     * 
     * @var array
     */
    protected $_fields = array();

    /**
     * 构造方法
     */
    public function __construct($data = array()) {
        if (count($this->_fields) == 0) {
            throw new \Exception("form fields is not set");
        }
        $this->_setFieldDefaultData();
        if ($data) {
            $this->setData($data);
        }
        $this->_validateFields();
    }

    /**
     * 设置字段的值
     * 
     * @param array $data
     */
    public function setData($data) {
        foreach ($this->_fields as $k => $v) {
            if (array_key_exists($k, $data)) {
                $this->_fields[$k]['value'] = is_string($data[$k]) ? trim($data[$k]) : $data[$k];
                continue;
            }
        }
    }

    /**
     * 设置字段的默认数据
     */
    private function _setFieldDefaultData() {
        foreach ($this->_fields as $k => $v) {
            $this->_fields[$k]["is_validate"] = true;
            if (!isset($v["require"])) {
                $this->_fields[$k]["require"] = true;
            }
            if (!isset($v["message"])) {
                $this->_fields[$k]["message"] = $k . " is error";
            }
            if (isset($v["default"])) {
                $this->_fields[$k]['value'] = $v["default"];
            }
        }
    }

    /**
     * 校验字段格式设置是否准确
     * 
     * @throws \Exception
     */
    private function _validateFields() {
        if (!is_array($this->_fields)) {
            throw new \Exception("fields is not array");
        }
        foreach ($this->_fields as $k => $v) {
            if (!isset($v["label"])) {
                throw new \Exception("field " . $k . " label is not set");
            }
            if (!isset($v["name"])) {
                throw new \Exception("field " . $k . " name is not set");
            }
            if ($k !== $v["name"]) {
                throw new \Exception("field " . $k . " name is not same");
            }
            if (isset($v["validate"])) {
                if (!is_array($v["validate"])) {
                    throw new \Exception("field " . $k . " validate is not array");
                }
                foreach ($v["validate"] as $validate) {
                    if (!isset($validate["type"])) {
                        throw new \Exception("field " . $k . " validate type is not set");
                    }
                    if ($validate['type'] == "set" &&
                            (!isset($validate['set']) || !is_array($validate['set']))) {
                        throw new \Exception("field " . $k . " validate set is not set");
                    }
                }
            }
        }
    }

    /**
     * 校验所有字段的值
     * 
     * @return boolean
     */
    public function validate() {
        foreach ($this->_fields as $fieldName => $field) {
            if (!$field["require"]) {
                if (!isset($field["value"])) {
                    continue;
                }
                if (is_string($field["value"]) && strlen($field["value"]) == 0) {
                    continue;
                }
                if (is_array($field["value"]) && !$field["value"]) {
                    continue;
                }
            }
            if ($field["require"] && !isset($field["value"])) {
                $this->_fields[$fieldName]["is_validate"] = false;
                continue;
            }
            if ($field["require"] &&
                    !in_array($field["value"], array(0, "0"), true) &&
                    empty($field["value"])) {
                $this->_fields[$fieldName]["is_validate"] = false;
                continue;
            }

            //这个是html标签的校验，通常在任何用户的输入中都要过滤
            //这里暂时注释掉
//            if (is_string($field["value"]) && $this->_valiateHtmlTag($field["value"])) {
//                $this->_fields[$fieldName]["is_validate"] = false;
//                continue;
//            }

            if (!empty($field['validate'])) {
                foreach ($field['validate'] as $validate) {
                    $validateMethodName = '_validateFieldValue' . $validate["type"];
                    if (method_exists($this, $validateMethodName)) {
                        $this->$validateMethodName($fieldName, $validate);
                    }
                }
            }
            //检测各个字段自己的校验方法
            $methodName = 'validate' . ucfirst(preg_replace_callback('/_\w/i'
                                    , create_function('$matches', 'return strtoupper(ltrim($matches[0],"_"));')
                                    , $fieldName));
            if (method_exists($this, $methodName)) {
                if (!$this->$methodName()) {
                    $this->_fields[$fieldName]["is_validate"] = false;
                }
            }
        }
        foreach ($this->_fields as $field) {
            if (!$field["is_validate"]) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取字段的值
     * 
     * @param string $fieldName
     * @return mix
     */
    public function getFieldValue($fieldName = null) {
        if (!$fieldName) { //获取所有字段的值
            $fieldsValue = array();
            foreach ($this->_fields as $field) {
                if (isset($field['value'])) {
                    $fieldsValue[$field['name']] = $field['value'];
                } else {
                    $fieldsValue[$field['name']] = null;
                }
            }
            return $fieldsValue;
        }
        foreach ($this->_fields as $field) {
            if ($field['name'] == $fieldName && isset($field['value'])) {
                return $field['value'];
            }
        }
        return null;
    }

    /**
     * 获取没有校验过的字段提示信息
     * 
     * @return array
     */
    public function getMessages($fieldName = null) {
        
        if ($fieldName) {
            if ($this->_fields[$fieldName]['is_validate']) {
                return null;
            }
            return $this->_fields[$fieldName]['message'];
        }

        $fieldsMessage = array();
        foreach ($this->_fields as $field) {
            if (!$field['is_validate']) {
                $fieldsMessage[$field['name']] = $field['message'];
            }
        }
        return $fieldsMessage;
    }
    
    /**
     * 获取没有校验过的第一个字段提示信息
     * @return array
     */
    public function getFirstMessage() {
        $all_message = $this->getMessages();
        return array_values($all_message)[0];
    }
    /**
     * 设置字段的属性值
     * 
     * @param string $fieldName
     * @param array $attrs
     */
    public function setFiledsAttr($fieldName, $attrs) {
        foreach ($attrs as $k => $v) {
            $this->_fields[$fieldName][$k] = $v;
        }
    }

    /**
     * 获取字段的属性值
     * 
     * @param string $fieldName
     * @param array|string $attrs
     */
    public function getFieldAttrs($fieldName, $attrs) {
        $this->_validateFieldExist($fieldName);
        if (is_string($attrs)) {
            if (isset($this->_fields[$fieldName][$attrs])) {
                return $this->_fields[$fieldName][$attrs];
            }
            return null;
        }

        $return = array();
        foreach ($attrs as $attr) {
            if (isset($this->_fields[$fieldName][$attr])) {
                $return[$attr] = $this->_fields[$fieldName][$attr];
                continue;
            }
            $return[$attr] = null;
        }
        return $return;
    }

    /**
     * 设置字段是否需要校验
     * 
     * @param string $fieldName
     * @param boolean $isRequire
     */
    public function setRequire($fieldName, $isRequire) {
        $this->_validateFieldExist($fieldName);
        $this->setFiledsAttr($fieldName, array('require' => $isRequire));
    }

    /**
     * 设置字段的提示信息
     * 
     * @param string $fieldName
     * @param string $message
     */
    public function setFieldMessage($fieldName, $message) {
        $this->_validateFieldExist($fieldName);
        $this->_fields[$fieldName]['message'] = $message;
    }

    /**
     * 校验字段是否存在
     * 
     * @param string $fieldName
     * @return boolean
     * @throws \Exception
     */
    private function _validateFieldExist($fieldName) {
        if (!array_key_exists($fieldName, $this->_fields)) {
            throw new \Exception("field " . $fieldName . " is not exist");
        }
        return true;
    }

    /**
     * 移除字段
     * 
     * @param string $fieldName
     */
    public function removeField($fieldName) {
        if (isset($this->_fields[$fieldName])) {
            unset($this->_fields[$fieldName]);
        }
    }

    /**
     * 获取所有字段
     * 
     * @return array
     */
    public function getFields() {
        return $this->_fields;
    }

    /**
     * 字符串校验器
     * 
     * @return boolean
     */
    private function _validateFieldValueString($fieldName, $validate) {
        $field   = $this->_fields[$fieldName];
        $options = array("value" => $field["value"]);
        if (isset($validate["min"])) {
            $options["min"] = $validate["min"];
        }
        if (isset($validate["max"])) {
            $options["max"] = $validate["max"];
        }
        if ($this->_validateLength($options)) {
            $this->_fields[$fieldName]["is_validate"] = true;
            return true;
        }
        if (isset($validate["msg"])) {
            $this->setFieldMessage($fieldName, $validate["msg"]);
        }
        $this->_fields[$fieldName]["is_validate"] = false;
        return false;
    }

    /**
     * 整形校验器
     * 
     * @return boolean
     */
    private function _validateFieldValueInt($fieldName, $validate) {
        $field   = $this->_fields[$fieldName];
        $options = array("value" => $field["value"]);
        if (isset($validate["min"])) {
            $options["min"] = $validate["min"];
        }
        if (isset($validate["max"])) {
            $options["max"] = $validate["max"];
        }
        if ($this->_validateInt($options)) {
            $this->_fields[$fieldName]["is_validate"] = true;
            return true;
        }
        if (isset($validate["msg"])) {
            $this->setFieldMessage($fieldName, $validate["msg"]);
        }
        $this->_fields[$fieldName]["is_validate"] = false;
        return false;
    }

    /**
     * 小数校验器
     * 
     * @return boolean
     */
    private function _validateFieldValueFloat($fieldName, $validate) {
        $field   = $this->_fields[$fieldName];
        $options = array("value" => $field["value"]);
        if (isset($validate["min"])) {
            $options["min"] = $validate["min"];
        }
        if (isset($validate["max"])) {
            $options["max"] = $validate["max"];
        }
        if ($this->_validateFloat($options)) {
            $this->_fields[$fieldName]["is_validate"] = true;
            return true;
        }
        if (isset($validate["msg"])) {
            $this->setFieldMessage($fieldName, $validate["msg"]);
        }
        $this->_fields[$fieldName]["is_validate"] = false;
        return false;
    }

    /**
     * 日期校验器
     * 
     * @return boolean
     */
    private function _validateFieldValueDate($fieldName, $validate) {
        $field   = $this->_fields[$fieldName];
        $options = array("value" => $field["value"]);
        if (isset($validate["formats"])) {
            $options["formats"] = $validate["formats"];
        }
        if ($this->_validateDate($options)) {
            $this->_fields[$fieldName]["is_validate"] = true;
            return true;
        }
        if (isset($validate["msg"])) {
            $this->setFieldMessage($fieldName, $validate["msg"]);
        }
        $this->_fields[$fieldName]["is_validate"] = false;
        return false;
    }

    /**
     * 集合校验器
     * 
     * @return boolean
     */
    private function _validateFieldValueSet($fieldName, $validate) {
        $field = $this->_fields[$fieldName];
        if (in_array($field['value'], $validate['set'])) {
            $this->_fields[$fieldName]["is_validate"] = true;
            return true;
        }
        if (isset($validate["msg"])) {
            $this->setFieldMessage($fieldName, $validate["msg"]);
        }
        $this->_fields[$fieldName]["is_validate"] = false;
        return false;
    }

    /**
     * 校验字符串长度
     * 
     * @param array $options
     * @return boolean
     */
    protected function _validateLength($options) {
        $length = mb_strlen($options['value']);
        if (isset($options['min'])) {
            if ($length < $options['min']) {
                return false;
            }
        }
        if (isset($options['max'])) {
            if ($length > $options['max']) {
                return false;
            }
        }
        return true;
    }

    /**
     * 校验整形数字
     * 
     * @param array $options
     * @return boolean
     */
    protected function _validateInt($options) {
        $num = $options['value'];
        if (isset($options['min']) && isset($options['max'])) {
            $int_options = array("options" => array("min_range" => $options['min'], "max_range" => $options['max']));
        } else if (isset($options['min'])) {
            $int_options = array("options" => array("min_range" => $options['min']));
        } else if (isset($options['max'])) {
            $int_options = array("options" => array("max_range" => $options['max']));
        }
        $result = filter_var($num, FILTER_VALIDATE_INT, $int_options);
        if ($result === FALSE) {
            return false;
        }
        return true;
    }

    /**
     * 校验小数
     * 
     * @param array $options
     * @return boolean
     */
    protected function _validateFloat($options) {
        $result = filter_var($options["value"], FILTER_VALIDATE_FLOAT);
        if ($result === false) {
            return false;
        }
        if (isset($options['min']) && $options["value"] < $options["min"]) {
            return false;
        }
        if (isset($options['max']) && $options["value"] > $options["max"]) {
            return false;
        }
        return true;
    }

    /**
     * 校验日期格式是否正确
     * 
     * @param array $options
     * @return boolean
     */
    protected function _validateDate($options) {
        $date    = $options['value'];
        $formats = $options["formats"];

        $unixTime = strtotime($date);
        if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
            return false;
        }

        //校验日期的有效性，只要满足其中一个格式就OK
        foreach ($formats as $format) {
            if (date($format, $unixTime) == $date) {
                return true;
            }
        }

        return false;
    }

    /**
     * 校验是否包含html和php标签
     * 
     * @param string $str
     * @return boolean
     */
    private function _valiateHtmlTag($str) {
        return $str != strip_tags($str);
    }

}
