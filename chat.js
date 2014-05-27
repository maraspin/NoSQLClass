/* 
 * Code sample taken from: http://garydengblog.wordpress.com/2013/06/28/simple-chat-application-using-redis-socket-io-and-node-js/comment-page-1/
 */
var app = require('http').createServer(handler);
app.listen(8088);
var io = require('socket.io').listen(app);
var redis = require('redis');
var fs = require('fs');

function handler(req,res){
    fs.readFile(__dirname + '/chat.html', function(err,data){
        if(err){
            res.writeHead(500);
            return res.end('Error loading chat.html');
        }
        res.writeHead(200);
        console.log("Connection Accepted");
        res.end(data);
    });
}

var store = redis.createClient();
var pub = redis.createClient();
var sub = redis.createClient();

io.sockets.on('connection', function (client) {
    sub.subscribe("chatting");
    sub.on("message", function (channel, message) {
        console.log("message received on server from publish ");
        client.send(message);
    });
    client.on("message", function (msg) {
        console.log(msg);
        if(msg.type == "chat"){
            pub.publish("chatting",msg.message);
        }
        else if(msg.type == "setUsername"){
            pub.publish("chatting","A new user in connected:" + msg.user);
            store.sadd("onlineUsers",msg.user);
        }
    });
    client.on('disconnect', function () {
        sub.quit();
        pub.publish("chatting","User is disconnected :" + client.id);
    });
  });