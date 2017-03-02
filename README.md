
#基于nodeJs 开发微信公众号

## 1、利用express生成项目文件目录是很方便的，命令是： ##

	-  express nodejs-wechat	
	-  cd nodejs-wechat & npm install 进入并安装node的包 
	-  SET DEBUG=nodejs-wechat:* & npm start 启动项目
	 
	对于目前这个文件，你git clone +地址 复制下来就行了，不需要重新开始建立文件；

# 2、如何在本地进行微信公众号的开发和调试 #
	
	开发微信公众号，大家都知道需要一个外部ip，也就是我们需要有个自己的服务器，作为开发者。我们一般都没有自己的服务器。
   ![](http://i.imgur.com/7s45j3A.png)

**可是我们怎么样找一个服务器呢？ [新浪云sae](http://www.sinacloud.com/)是一个不错的选择**。

![](http://i.imgur.com/i3aebd5.png)
![](http://i.imgur.com/6WpyrAx.png)
![](http://i.imgur.com/zFQ6Mvk.png)

	 可以选择php的标准环境，这个是不收费的。你可以写上你想要的二级域名，这样你就有了一个外部ip;
	 此时我们可以进入微信公众平台填写我们的服务器配置：
	 url就是你刚获取到的域名
	 token自定义一个名字即可
     EncodingAESKey 随机即可
 ![](http://i.imgur.com/7s45j3A.png)

**当你填写完你会发现提示“token验证失败”：**

![](https://gss0.baidu.com/-vo3dSag_xI4khGko9WTAnF6hhy/zhidao/pic/item/c83d70cf3bc79f3ddd7648aeb9a1cd11738b299c.jpg)
	
	这是因为还需要在你搭建的服务器里面进行 token 验证：
	你先把服务器代码库git clone 下来，然后新建一个index.php
![](http://i.imgur.com/q9qHh7T.png)
## [index.php](https://github.com/huainanhai/nodejs-wechat/blob/master/index.php)代码如下： ##

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


# 3、nodejs中微信公众号开发-接口配置和签名验证 #

**使用的模块**

	sha1 : 加密模块
	安装 : npm install sha1 -save

**改造项目**

	--创建config文件夹
	项目根目录下创建config文件夹,
	在config文件夹下添加config.json文件,主要是appID,token等
	这些基本参数在微信号个人中心有,复制过来就是了.token必须和配置接口的token一致；

**config.json如下:**

	{
    	"wechat":{
        	"appId": "wx3ae444786cf37295",
        	"appSecret":"fcfe020321b2a1e467849a83ed19c7b8",
        	"token": "beipiaoyu",
        	"prefix": "https://api.weixin.qq.com/cgi-bin/",
        	"mpPrefix": "https://mp.weixin.qq.com/cgi-bin/"
   		}
	}

**封装签名认证**

	项目根目录下创建common文件夹,
	在common文件夹下添加utils.js文件

**utils.js:**

	var utils = {};  
	var sha1 = require('sha1');  
  
	//检查微信签名认证中间件  
	utils.sign = function (config){  
    return function(req, res, next){  
        config = config || {};  
        var q = req.query;  
      var token = config.wechat.token;  
      var signature = q.signature; //微信加密签名  
        var nonce = q.nonce; //随机数  
        var timestamp = q.timestamp; //时间戳  
        var echostr = q.echostr; //随机字符串  
        /* 
            1）将token、timestamp、nonce三个参数进行字典序排序 
            2）将三个参数字符串拼接成一个字符串进行sha1加密 
            3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信 
        */  
        var str = [token, timestamp, nonce].sort().join('');  
        var sha = sha1(str);  
        if (req.method == 'GET') {  
  
            if (sha == signature) {  
                res.send(echostr+'')  
            }else{  
                res.send('err');  
            }  
        }  
        else if(req.method == 'POST'){  
            if (sha != signature) {  
                return;  
            }  
            next();  
        }  
      }  
	};  
  
	module.exports = utils; 

**引入中间件**

	在app.js文件中引入utils.js和config.json,然后使用签名认证中间件

![](http://i.imgur.com/cQR5Go3.png)

这个过程就添加了2个文件,修改了app.js文件

![](http://i.imgur.com/PFcyOGv.png)

# 4、access_token获取与保存 #

	微信的access_token是从获取开始7200秒后失效,也就是2个小时,需要重新获取.
	思路:
	access_token必须能在路由中全局访问到,本系列博文在express框架中测试,
	所以可以挂载到请求的对象(req)上;
	获取一次可以使用2小时,2小时后需要重新获取,这里采用Redis数据库存储access_token,
	为什么使用redis数据库呢疑问,因为redis数据库也带过期特性,感觉天生就和access_token
	的过期匹配,结合起来使用非常方便.开始改造.
	-- 需要的模块
	request  -- 调用微信接口模块
	redis -- redis数据库模块
	xml2js -- xml转为js对象
	安装 npm install request xml2js redis -save

**[redis数据库](https://redis.io/)：**

 	Redis是一个开源的使用ANSIC语言编写、支持网络、可基于内存亦可持久化的日志型、Key-Value数据库，并提供多种语言的API。

	在D盘新建文件夹【redis】，右键解压Redis ZIP包，把所有文件解压到redis文件夹中。（其他盘符也可以滴^_^）
	文件介绍：
	redis-benchmark.exe         #基准测试
	redis-check-aof.exe         # aof
	redischeck-dump.exe        # dump
	redis-cli.exe               # 客户端
	redis-server.exe            # 服务器
	redis.windows.conf          # 配置文件

![](http://i.imgur.com/ikr5pVY.png)

	windows 运行（快捷键：windows键+R键），输入【cmd】命令，进入DOC操作系统窗口。
	使用命令【redis-server.exe  redis.windows.conf】，
	启动redis 服务【如果您没出现如下的错误，直接跳过】。
	如果您也像我一样出现如下的错误，不用急，总有解决办法滴！

![](http://i.imgur.com/ul2G9FP.png)

	解决办法：
	根据提示，是 maxheap 标识有问题,打开配置文件 redis.windows.conf ,
	搜索 maxheap , 然后直接指定好内容即可.
	......
	# 
	# maxheap <bytes>
	maxheap 1024000000
	.......
	然后再次启动,OK,成功.

**服务启动成功状态**

![](http://i.imgur.com/M54l9Ns.png)

	启动redis服务的doc窗口，不用关闭，因为服务需要一直执行，关闭服务，直接关闭窗口就行。
	新打开一个doc窗口，用自带的客户端工具进行测试 命令【redis-cli.exe】,详细操作如下。。
	事例展示了一个基本的读写操作，设置set key->age，value->21，get age 得到key的值。^_^

![](http://i.imgur.com/Gsi5W44.png)

	微信的access_token是从获取开始7200秒后失效,也就是2个小时,需要重新获取.
	思路:
	access_token必须能在路由中全局访问到,本系列博文在express框架中测试,
	所以可以挂载到请求的对象(req)上;
	获取一次可以使用2小时,2小时后需要重新获取,这里采用Redis数据库存储access_token,
	为什么使用redis数据库呢疑问,因为redis数据库也带过期特性,感觉天生就和access_token
	的过期匹配,结合起来使用非常方便.开始改造.

**[redis数据库可视化工具 Redis Desktop Manager](https://redisdesktop.com/download)**

![](http://i.imgur.com/2hl2xqb.png)

**需要的模块**

	request  -- 调用微信接口模块
	redis -- redis数据库模块
	xml2js -- xml转为js对象
	安装 npm install request xml2js redis -save

**封装几个方法**

	1.Promise化request;
	Promise已经是nodejs的内置对象了,可以直接使用,从这里能看出nodejs以后异步代码发展路线估计也是Promise了
	2.redis添加数据
	3.redis获取数据

**common文件夹的utils.js文件代码:**

	var utils = {};  
	var sha1 = require('sha1');  
	var request = require('request');  
	var redis = require("redis");    
	var client = redis.createClient();    
	    
	client.on("error", function (err) {    
	  console.log("Error :" , err);    
	});    
	    
	client.on('connect', function(){    
	  console.log('Redis连接成功.');    
	})   
	  
	//检查微信签名认证中间件  
	utils.sign = function (config){  
    return function(req, res, next){  
        config = config || {};  
        console.log(config);  
        var q = req.query;  
        console.log(q);  
      var token = config.wechat.token;  
      var signature = q.signature; //微信加密签名  
        var nonce = q.nonce; //随机数  
        var timestamp = q.timestamp; //时间戳  
        var echostr = q.echostr; //随机字符串  
        /* 
            1）将token、timestamp、nonce三个参数进行字典序排序 
            2）将三个参数字符串拼接成一个字符串进行sha1加密 
            3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信 
        */  
        var str = [token, timestamp, nonce].sort().join('');  
        var sha = sha1(str);  
        if (req.method == 'GET') {  
  
            if (sha == signature) {  
                res.send(echostr+'')  
            }else{  
                res.send('err');  
            }  
        }  
        else if(req.method == 'POST'){  
            if (sha != signature) {  
                return;  
            }  
            next();  
        }  
    }  
	};  
  
	/**  
	 * 添加string类型的数据  
	 * @param key 键  
	 * @params value 值   
	 * @params expire (过期时间,单位秒;可为空，为空表示不过期)  
	 */    
	utils.set = function(key, value, expire){    
  
    return new Promise(function(resolve, reject){  
    
    client.set(key, value, function(err, result){    
    
      if (err) {    
        console.log(err);    
        reject(err);  
        return;    
      }    
  
      if (!isNaN(expire) && expire > 0) {    
        client.expire(key, parseInt(expire));    
      }    
  
      resolve(result);   
    })   
	  })   
	};    
	    
	/**  
	 * 查询string类型的数据  
	 * @param key 键  
	 */    
	utils.get = function(key){    
  
    return new Promise(function(resolve, reject){  
    
    client.get(key, function(err,result){    
    
      if (err) {    
        console.log(err);    
        reject(err);    
        return;    
      }    
  
      resolve(result);    
    });   
	  })   
	};    
	  
	//Promise化request  
	utils.request = function(opts){  
    opts = opts || {};  
    return new Promise(function(resolve, reject){  
        request(opts,function(error, response, body){  
  
            if (error) {  
                return reject(error);  
            }  
            resolve(body);  
        })  
          
    })  
  
	};  
	  
	module.exports = utils;  

**封装微信操作API**

	在common文件夹下,添加wechatapi.js文件,目前只有获取access_token的方法

**wechatapi.js文件：**

	/* 
	 *微信相关操作api 
	 */  
	var wechatApi = {};  
	var config = require('../config/config');  
	var appID = config.wechat.appID;  
	var appSecret = config.wechat.appSecret;  
	var utils = require('./utils');  
	var api = {  
	    accessToken : `${config.wechat.prefix}token?grant_type=client_credential`,  
	    upload : `${config.wechat.prefix}media/upload?`  
	}  
	  
	//获取access_token  
	wechatApi.updateAccessToken = function(){  
    var url = `${api.accessToken}&appid=${appID}&secret=${appSecret}`;  
    //console.log(url);  
    var option = {  
        url : url,  
        json : true  
    };  
    return utils.request(option).then(function(data){  
  
        return Promise.resolve(data);  
    })  
	}  
	  
	module.exports = wechatApi;

**改造index路由**

	在index.js路由文件中添加router.use方法,router.use方法意思就是这个路由中所有的
	请求都必须先经过这个方法才能往下执行,
	-- 首先是从redis中获取access_token,如果获取到了,传递下去,如果没获取到就从微信端获取,然后传递下去;
	-- 在第二个then方法中判断这个data是redis中的还是微信端的.方法有多种,我这里是判断data.expires_in
	是否存在,如果存在就是微信端的,如果不存在就是redis数据库获取的;
	-- 如果是redis中的access_token,就把他挂载到req对象上,下面的方法就可以通过req.accessToken得到值,
	如果是微信端获取的access_token,就需要先保存到redis数据库中,再挂载到req对象上.

**代码如下:**

	//获取,验证access_token,存入redis中  
	router.use(function(req, res, next) {  
  
    //根据token从redis中获取access_token  
    utils.get(config.wechat.token).then(function(data){  
        //获取到值--往下传递  
        if (data) {  
            return Promise.resolve(data);  
        }  
        //没获取到值--从微信服务器端获取,并往下传递  
        else{  
            return wechatApi.updateAccessToken();  
        }  
    }).then(function(data){  
        console.log(data);  
        //没有expire_in值--此data是redis中获取到的  
        if (!data.expires_in) {  
            console.log('redis获取到值');  
            req.accessToken = data;  
            next();  
        }  
        //有expire_in值--此data是微信端获取到的  
        else{  
            console.log('redis中无值');  
            /** 
             * 保存到redis中,由于微信的access_token是7200秒过期, 
             * 存到redis中的数据减少20秒,设置为7180秒过期 
             */  
            utils.set(config.wechat.token,`${data.access_token}`,7180).then(function(result){  
                if (result == 'OK') {  
                    req.accessToken = data.access_token;  
                    next();  
                }  
            })  
        }  
  
    })  
	}) 

![](http://i.imgur.com/Vstfp5N.png)

**测试**

	访问 : http://localhost/,会经过router.use方法.在'/'方法中页面打印(req.accessToken);

![](http://i.imgur.com/a8ZnPl0.png)

![](http://i.imgur.com/I73A1tQ.png)

![](http://i.imgur.com/cJc47cB.png)

	这就是access_token了
