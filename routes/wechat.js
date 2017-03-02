var express = require('express');
var router = express.Router();
var wechat = require('wechat');

var config = {
    token:'beipiaoyu',
    appId:'wxb815dd214eec6a4c',
    appSecret:'045a0a6863394969b6a7da5945851e06',
    encodingAESKey :''
};
router.use(express.query());
router.get('/', wechat(config, function(req, res, next){
    var message = req.weixin;
    console.log(message);
    if(message.Content == '1'){
        res.reply('hehe');
    }
}));
module.exports = router;
