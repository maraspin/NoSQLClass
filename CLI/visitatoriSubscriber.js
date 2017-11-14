#!/usr/bin/env node

var amqp = require('amqplib/callback_api');

amqp.connect('amqp://localhost', function(err, conn) {

  conn.createChannel(function(err, ch) {

    var ex = 'visitatori';

    ch.assertExchange(ex, 'fanout', {durable: false});

    ch.assertQueue('', {exclusive: true}, function(err, q) {
      console.log("In attesa di messaggi da %s... Per uscire premere CTRL+C", ex);
      ch.bindQueue(q.queue, ex, '');

      ch.consume(q.queue, function(msg) {
        console.log("%s", msg.content.toString());
      }, {noAck: true});
    });
  });
});