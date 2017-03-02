
#基于nodeJs 开发微信公众号

1、利用express生成项目文件目录是很方便的，命令是：

	-  express nodejs-wechat	
	-  cd nodejs-wechat & npm install 进入并安装node的包 
	-  SET DEBUG=nodejs-wechat:* & npm start 启动项目
	 
	对于目前这个文件，你git clone +地址 复制下来就行了，不需要重新开始建立文件；

2、 如何在本地进行微信公众号的开发和调试
	
    开发微信公众号，大家都知道需要一个外部ip，也就是我们需要有个自己的服务器，作为开发者。我们一般都没有自己的服务器。
   ![](http://i.imgur.com/7s45j3A.png)

  	可是我们怎么样找一个服务器呢？
	
   [新浪云sae](http://www.sinacloud.com/)是一个不错的选择。
![](http://i.imgur.com/i3aebd5.png)
![](http://i.imgur.com/6WpyrAx.png)
![](http://i.imgur.com/zFQ6Mvk.png)

	 可以选择php的标准环境，这个是不收费的。你可以写上你想要的二级域名，这样你就有了一个外部ip;
	 此时我们可以进入微信公众平台填写我们的服务器配置：
	 url就是你刚获取到的域名
	 token自定义一个名字即可
     EncodingAESKey 随机即可
 ![](http://i.imgur.com/7s45j3A.png)

	  当你填写完你会发现提示“token验证失败”：
![](https://gss0.baidu.com/-vo3dSag_xI4khGko9WTAnF6hhy/zhidao/pic/item/c83d70cf3bc79f3ddd7648aeb9a1cd11738b299c.jpg)
	
	这是因为还需要在你搭建的服务器里面进行 token 验证：
	你先把服务器代码库git clone 下来，然后新建一个index.php
![](http://i.imgur.com/q9qHh7T.png)
[index.php](https://github.com/huainanhai/nodejs-wechat/blob/master/index.php)代码如下

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

	问题:开发者中心配置时，一直提示 token验证失败：

	1、检查url是否和服务器部署的一致，token是否和代码中的TOKEN一致。

	2、在服务器代码中valid()方法中  echo $echoStr;   前面增加   header('content-type:text');代码

	3、跟实名认证没有关系，没有认证不能用高级接口，但是简单的事可以测试的。

	4、PHP文件以utf-8格式保存，并打包.zip






3、 nodejs中微信公众号开发-接口配置和签名验证

