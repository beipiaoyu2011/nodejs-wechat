
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
 ![](http://i.imgur.com/7s45j3A.png)

3、 nodejs中微信公众号开发-接口配置和签名验证

