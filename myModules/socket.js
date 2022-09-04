require('dotenv').config()
const debug = require('../server.js').debug
const io = require('socket.io')(process.env.SOCKET_LISTEN_PORT, {
    cors: {
      origin: [`http://${process.env.SERVER_ADDRESS}:${process.env.HTTP_LISTEN_PORT}`]
    },
  })
  io.on('connection', (socket) => {
    if (debug) console.log("socket connection");
  });
  module.exports = { io }