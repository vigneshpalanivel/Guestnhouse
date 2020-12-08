var app = require('express')(); 
var server = require('http').Server(app);
var io = require('socket.io')(server);
var redis = require('redis');

server.listen(7000, function() {
	console.log('Connected 7000');
});

var redisClient    = redis.createClient({
   port      : 6381,               // replace with your port
   host      : '127.0.0.1',        // replace with your hostanme or IP address
});

//var redisClient = redis.createClient();
redisClient.subscribe('laravel_database_chat');

redisClient.on("message", function(channel, data) {
  data = JSON.parse(data);
  if(data.type!='booking'){
	  var reservation_id = data.reservation_id;
	  io.emit('message_'+reservation_id, data);
	  io.emit('message_mobile_'+reservation_id, data.instant_message_mobile);
  }
  if(data.inbox=='yes'){
  	io.emit('inbox','');
  }
  data.count['reservation_id']= reservation_id;
  if(data.instant_message) {
    if(data.instant_message.user_to) {
      io.emit('dashboard_'+data.instant_message.user_to,data.instant_message);
    }                               
  }
  io.emit('message_count', data.count);
});
  
  io.on('connection', (socket) => {
    socket.on("message_mobile_",function(data){
        socket.broadcast.emit('message_mobile_'+data.reservation_id, data);
    });
    socket.on("typing_",function(reservation_id){
        socket.broadcast.emit('typing_'+reservation_id, reservation_id);
    });
    socket.on("stop_typing_",function(reservation_id){
        socket.broadcast.emit('stop_typing_'+reservation_id, reservation_id);
    });
});


