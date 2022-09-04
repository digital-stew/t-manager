require('dotenv').config()
const debug = require('../server.js').debug
var nodemailer = require('nodemailer');

var transporter = nodemailer.createTransport({
  service: 'gmail',
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_PASSWORD
  }
});


function sendMail(mailTo, subject, msg) {
    var mailOptions = {
      from: 't-manager@t-print.co.uk',
      to: mailTo,
      subject: subject,
      text: msg
    };
  
    transporter.sendMail(mailOptions, function (error, info) {
      if (error) {
        console.error(error);
      } else {
        if (debug) console.log('Email sent: ' + info.response);
      }
    });
  }



  module.exports = { sendMail }