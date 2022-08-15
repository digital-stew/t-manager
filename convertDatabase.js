require('dotenv').config()
var sqlite3 = require('sqlite3');
var db = new sqlite3.Database(process.env.DB_LOCATION, sqlite3.OPEN_READWRITE, (err) => {
    if (err) {
        console.log("Getting error " + err);
        return (1);
    }

});
console.log('access to database enabled. location= ' + process.env.DB_LOCATION)

// var count = 1 // to 711 // CHANGE rowid to id
for (let c = 1; c <= 740; c++) {
    db.get(`SELECT * FROM samples WHERE rowid=${c}`, (err, row) => {
        try {
            var picsString = `{"pics":${row.pics}}`
        db.run("UPDATE samples SET pics = ? WHERE rowid = ?", picsString , c);
        } catch (error) {
            // console.log(error)

        }


    })
}



