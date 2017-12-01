#!/usr/bin/env node

var querystring = require('querystring');
var http = require('http');
var fs = require('fs');
var amqp = require('amqplib/callback_api');

amqp.connect('amqp://localhost', function(err, conn) {
  conn.createChannel(function(err, ch) {

    var q = 'magazzino';

    ch.assertQueue(q, {durable: true});
    ch.prefetch(1);
    console.log("In ascolto sulla coda %s. Premere CTRL+C per uscire", q);
    ch.consume(q, function(msg) {

      var secs = msg.content.toString().split('.').length - 1;

      PostCode(msg.content.toString());
	  // console.log("Registrato ordine per: %s", msg.content.prodotto.nome.toString());

      setTimeout(function() {
        ch.ack(msg);
      }, secs * 1000);
    }, {noAck: false});
  });
});


function PostCode(codestring) {

  var post_data = codestring;

  // An object of options to indicate where to post to
  var post_options = {
      host: 'localhost',
      port: '5984',
      path: '/ordini/',
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
          'Content-Length': Buffer.byteLength(post_data)
      }
  };

  // Set up the request
  var post_req = http.request(post_options, function(res) {
      res.setEncoding('utf8');
      res.on('data', function (chunk) {
          console.log('Response: ' + chunk);
      });
  });

  // post the data
  post_req.write(post_data);
  post_req.end();

}
