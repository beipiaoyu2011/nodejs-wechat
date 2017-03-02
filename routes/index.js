var express = require('express');
var router = express.Router();
var config = require('../config/config');
var utils = require('../common/utils');
var wechatapi = require('../common/wechatapi');
var xml2js= require('xml2js');

// 获取验证access_token 存入redis
router.use(function(req, res, next){
    console.log('666');
    //从redis 里面获取access_token
    utils.get(config.wechat.token).then(function(data){
        if(data){
            return Promise.resolve(data);
        }else {//redis没有的话 从微信获取
            return wechatapi.updateAccessToken();
        }
    }).then(function(data){
        res.send(data);
        //没有expire_in值--此data是redis中获取到的
        if(!data.expires_in){
            console.log('redis获取到值');
            req.accessToken = data;
            next();
        }else {//有expires_in--此data是微信端获取到的
            console.log('redis无此值');
            /**
             * 保存到redis中,由于微信的access_token是7200秒过期,
             * 存到redis中的数据减少20秒,设置为7180秒过期
             */
            utils.set(config.wechat.token, `${data.access_token}`, 7180).then(function(result){
                if(result == 'ok'){
                    req.accessToken = data.access_token;
                    next();
                }
            });
        }
    });
});

/* GET home page. */
router.get('/', function(req, res, next) {
    console.log(req);
    res.render('index', { title: 'Express' });
});

module.exports = router;
