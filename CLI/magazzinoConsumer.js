var redis = require("redis");

console.log("Applicazione Queue Worker avvviata");

var client = redis.createClient();
waitForPush();

function waitForPush () {
  client.brpop(['magazzino',0], function (listName, item) {
    // do stuff
    console.log("Spedire Oggetto: %s", item);
    waitForPush();
  });
}