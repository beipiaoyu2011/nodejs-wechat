
/*
 * 微信相关操作api
 */
var wechatapi = {};
var config = require("../config/config");
var appId = config.wechat.appId;
var appSecret = config.wechat.appSecret;
var utils = require('../common/utils');
var api = {
    accessToken: `${config.wechat.prefix}token?grant_type=client_credential`,
    upload: `${config.wechat.prefix}media/upload?`
};

//获取access_token
wechatapi.updateAccessToken = function(){
    var url=`${api.accessToken}&appid=${appId}&secret=${appSecret}`;
    console.log(url);
    var option = {
        url: url,
        json: true
    };
    return utils.request(option).then(function(data){
        return Promise.resolve(data);
    });
};

module.exports = wechatapi;
