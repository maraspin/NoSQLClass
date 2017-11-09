var redis = require("redis");

console.log("Applicazione avvviata");

var clientSusbcribe = redis.createClient();
clientSusbcribe.subscribe("visitatori");

clientSusbcribe.on("message", function(channel, message){
    console.log("Messaggio: %s, dal canale: %s", message, channel);
});