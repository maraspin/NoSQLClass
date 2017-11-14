#!/usr/bin/env node

var amqp = require('amqplib/callback_api');

amqp.connect('amqp://localhost', function(err, conn) {
  conn.createChannel(function(err, ch) {

    var q = 'magazzino';

    ch.assertQueue(q, {durable: true});
    ch.prefetch(1);
    console.log("In ascolto sulla coda %s. Premere CTRL+C per uscire", q);
    ch.consume(q, function(msg) {
      var secs = msg.content.toString().split('.').length - 1;

      console.log("Spedire Oggetto: %s", msg.content.toString());
      setTimeout(function() {
        ch.ack(msg);
      }, secs * 1000);
    }, {noAck: false});
  });
});