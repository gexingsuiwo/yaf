<?php

/**
 * 安全
 * @version 1.0
 * xss ,sql注入
 * @author wangliuyang
 */

namespace Lib;

class security {

    const XSS_CLEAN_ALL_HTML_TAGS = 'ALL_HTML_TAGS'; // 过滤所有的 html tag
    const XSS_CLEAN_NOLY_SCRIPT_TAGS = 'NOLY_SCRIPT_TAGS'; // 只过滤脚本
    const XSS_CLEAN_NO_DENY = "NO_DENY";

    public function xssClean($val) {
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters  
        // this prevents like <img SRC=@avascript:alert('XSS')/>  
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional 
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars 
            // @ @ search for the hex values 
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times  
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ; 
        }

        // now the only remaining whitespace attacks are \t, \n, and \r 
        $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something 
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';

                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag  
                //$replacement = htmlspecialchars($ra[$i]);
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags  
                // $val = htmlspecialchars($val);
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop  
                    $found = false;
                }
            }
        }
        return $val;
    }

    /**
     * 递归的过滤xss,过滤全部的html tag （包括正常的 '<b></b>' 等，性能好 ）
     */
    public function dXssClean($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->dXssClean($v);
            }
        } else {
            $data = htmlspecialchars($data);
        }
        return $data;
    }

    /**
     * 递归的过滤xss,只过滤危险的script （此方法比较消耗资源，不建议大数据量使用）
     */
    public function dxssClean2($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->dXssClean2($v);
            }
        } else {
            $data = $this->xssClean($data);
        }
        return $data;
    }

    /**
     * sql 注入过滤
     */
    public function sql_clean() {
        // TOdo....
    }

}
