<?php
define("TOKEN", "beipiaoyu"); // 定义自己的TOKEN值，并且与微信公众平台提供的值相同即可

$wechatObj = new wechatCallbackapiTest();

$wechatObj->valid();

class wechatCallbackapiTest
{

    public function valid()
    {
        $echoStr = $_GET["echostr"]; //随机字符串,该值只在第一次验证的时候有值，

        if ($this->checkSignature()) {
            header('content-type:text');
            echo $echoStr;
            exit();
        }
    }

    public function responseMsg()
    {
        // get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        // extract post data
        if (! empty($postStr)) {
            /*
             * libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
             * the best way is to check the validity of xml by yourself
             */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
            if (! empty($keyword)) {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            } else {
                echo "Input something...";
            }
        } else {
            echo "";
            exit();
        }
    }

    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (! defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];//微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
        $timestamp = $_GET["timestamp"];//时间戳
        $nonce = $_GET["nonce"];//随机数

        $token = TOKEN;
        $tmpArr = array(
            $token,
            $timestamp,
            $nonce
        );//将以上三个变量放到数组中
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);//针对数组进行排序
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}

?>
