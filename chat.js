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
            // utilizzare pub.publish per mostrare il messaggio dell'utente (msg.message)
        }
        else if(msg.type == "setUsername"){
            // usare il canale pub.publish per notificare partecipazione nuovo utente
            store.sadd("onlineUsers",msg.user);
        }
    });
    client.on('disconnect', function () {
        sub.quit();
        // pub.publish(__CHANNEL_NAME__,"User is disconnected :" + client.id);
    });
  });