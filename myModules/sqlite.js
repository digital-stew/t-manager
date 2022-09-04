require('dotenv').config()
const debug = require('../server.js').debug
var sqlite3 = require('sqlite3');
var db = new sqlite3.Database(process.env.DB_LOCATION, sqlite3.OPEN_READWRITE, (err) => {
  if (err) {
    console.log("Getting error " + err);
    return (1);
  } else {
    if (debug) console.log('access to database enabled. ', process.env.DB_LOCATION)
  }

});

const runSQL = (sql, data, next) => {
  return new Promise((resolve, reject) => {
    db.all(sql, data, (err, results) => {
      if (err) {
        next(err)
      } else {
        resolve(results)
      }
    })
  })
}

module.exports = { db, runSQL }