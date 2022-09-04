require('dotenv').config()
let debug = undefined
if (process.env.NODE_ENV === 'production') {
  debug = false
}
if (process.env.NODE_ENV === 'dev') {
  debug = true
  console.log("DEBUG MODE")
}
module.exports = { debug }

const bcrypt = require('bcrypt'); // for password hash
const cookieParser = require('cookie-parser')
const im = require('imagemagick'); // to make thubnails
const fs = require('fs-extra') // log to text file
const log = fs.createWriteStream(__dirname + '/log.txt', { flags: 'a' });
const { db, runSQL } = require('./myModules/sqlite.js') // sqlite3 database and runSQL as promise
const sendMail = require('./myModules/email.js').sendMail
const { timestampOfTodaysDate, timestampfromFormInputDate, timestampOfNow, timestampToDateAndTime } = require('./myModules/functions.js')
const { userLevel, department } = require('./myModules/authentication')
const io = require('./myModules/socket.js').io // socket.io for auto page refresh on new data

// #region ----express setup-----------------------------
const express = require('express')
const http = require('http');
const https = require('https');
var key = fs.readFileSync(process.env.HTTPS_SSL_KEY);
var cert = fs.readFileSync(process.env.HTTPS_SSL_CERTIFICATE);
var options = {
  key: key,
  cert: cert
};

const fileUpload = require('express-fileupload');

const app = express()

app.use(cookieParser())
app.use(fileUpload());
app.set("view engine", "ejs")

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(__dirname + '/public'));

var session = require('express-session')
var SQLiteStore = require('connect-sqlite3')(session)
const oneDay = 1000 * 60 * 60 * 24
const oneHour = 1000 * 60 * 60
app.use(session({
  store: new SQLiteStore,
  secret: process.env.SESSION_SECRET_KEY,
  cookie: { maxAge: oneHour * 8 }, // users can stay loged in all day
  resave: false,
  saveUninitialized: false,
  rolling: true
}));

var session
app.use((req, res, next) => {
  session = req.session
  next()
})

var darkmode
app.use((req, res, next) => {
  if (req.cookies.darkmode == true) {
    darkmode = true
  } else {
    darkmode = false
  }
  next()
})

if (process.env.HTTP === 'on') {
  http.createServer(app).listen(process.env.HTTP_LISTEN_PORT, () => {
    console.log('HTTP Server running on port', process.env.HTTP_LISTEN_PORT);

  });
}
if (process.env.HTTPS === 'on') {
  https.createServer(options, app).listen(process.env.HTTPS_LISTEN_PORT, () => {

    console.log('HTTPS Server running on port', process.env.HTTPS_LISTEN_PORT);
  });
}

app.get('/', (req, res) => {
  res.redirect('/production')
})
// #endregion
//---------------------------------------------------------

app.get('/production', (req, res, next) => {

  var selected_date = null
  if (req.query.selected_date) {
    var searchDate = timestampfromFormInputDate(req.query.selected_date)
    selected_date = req.query.selected_date
  } else {
    var searchDate = timestampOfTodaysDate()
  }
  db.all(`SELECT * FROM jobs WHERE bookindate = ? ORDER BY duedate ASC `, [searchDate], (err, row) => {
    if (err) return next(err)
    const todayTimestamp = timestampOfTodaysDate()
    res.render('production.ejs', { row, session, darkmode, searchDate, selected_date, todayTimestamp })
  })

})
//---------------------------------------------------------

// #region INK
app.get('/ink', (req, res, next) => {
  db.all("SELECT * FROM inkdata", (err, row) => {
    if (err) return next(err)
    res.render("ink.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.post('/ink', logger, (req, res) => {

  if (req.body.add) {
    if (!req.files || Object.keys(req.files).length === 0) {
      return res.status(400).send('No files were uploaded.');
    }

    req.files.uploadFile.mv(__dirname + '/public/ink/' + req.files.uploadFile.name)
    db.run(`INSERT INTO inkdata (filename, note) VALUES (?,?)`,
      [req.files.uploadFile.name, req.body.note], (err) => {
        if (err) return next(err)
        res.redirect('back')
      })
  }

  if (req.body.delete) {
    db.run("DELETE FROM inkdata WHERE id=?", [req.body.delete], (err) => {
      if (err) return next(err)
      res.redirect('back')
    })
  }

})
//---------------------------------------------------------
// #endregion

// #region admin
app.post('/login', logger, (req, res) => {
  db.get("SELECT * FROM users WHERE user=?", [req.body.user.toLowerCase()], (err, row) => {
    if (err) return next(err)
    if (row == undefined) {
      res.send("user not exsis")
      return 1
    }
    bcrypt.compare(req.body.password, row.password, (err, result) => {
      if (result) {
        //is loged in
        req.session.userName = row.user
        req.session.userLevel = row.userlevel
        req.session.department = row.department

        if (req.body.password == '123456') { //if silly passwords
          res.redirect('/user')
        } else {
          res.redirect('back')
        }

      } else {
        //fail login
        res.send("incorrect password")
      }
    })
  })
})
//---------------------------------------------------------
app.get('/logout', logger, (req, res) => {
  req.session.destroy()
  res.redirect('/production')
})
//---------------------------------------------------------
app.get('/user', userLevel('user'), (req, res) => {
  db.get("SELECT * FROM users WHERE user =?", [req.session.userName], (err, row) => {
    if (err) return next(err)
    res.render("user.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.post('/user', logger, (req, res) => {

  if (req.body.changePassword) {
    bcrypt.hash(req.body.password, 10, (err, hash) => {
      if (err) return next(err)
      db.run("UPDATE users SET password = ? WHERE user = ?", [hash, req.session.userName], (err) => {
        if (err) return next(err)
      })
    })
  }

  if (req.body.changeEmail) {
    db.run("UPDATE users SET email = ?", [req.body.email], (err) => {
      if (err) return next(err)
    })
  }

  res.redirect('back')
})
//---------------------------------------------------------
app.post('/darkmode', logger, (req, res) => {
  if (req.cookies.darkmode == 1) {
    res.cookie('darkmode', 0, { maxAge: oneDay * 30, httpOnly: true })
  } else {
    res.cookie('darkmode', 1, { maxAge: oneDay * 30, httpOnly: true })
  }
  res.redirect('back')
})
//---------------------------------------------------------
app.get('/admin/machines', userLevel('admin'), (req, res) => {
  db.all("SELECT * FROM machines", (err, row) => {
    if (err) return next(err)
    res.render("admin/machines.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.post('/admin/machines', logger, userLevel('admin'), (req, res) => {

  if (req.body.add) {
    db.run(`INSERT INTO machines (name, printheads, dryers) VALUES (?,?,?)`, [req.body.machine, req.body.printheads, req.body.dryers], (err) => {
      if (err) return next(err)
      res.redirect('back')
    })
  }

  if (req.body.delete) {
    db.run("DELETE FROM machines WHERE id=?", [req.body.delete], (err) => {
      if (err) return next(err)
      res.redirect('back')
    })
  }

})
//---------------------------------------------------------
app.get('/admin/reps', userLevel('admin'), (req, res) => {
  db.all("SELECT * FROM reps", (err, row) => {
    if (err) return next(err)
    res.render("admin/reps.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.post('/admin/reps', logger, userLevel('admin'), (req, res) => {

  if (req.body.add) {
    db.run(`INSERT INTO reps (name, email) VALUES (?,?)`, [req.body.name, req.body.email], (err) => {
      if (err) return next(err)
      res.redirect('back')
    })
  }

  if (req.body.delete) {
    db.run("DELETE FROM reps WHERE id=?", [req.body.delete], (err) => {
      if (err) return next(err)
      res.redirect('back')
    })
  }

})
//---------------------------------------------------------
app.get('/admin/users', userLevel('admin'), (req, res) => {
  db.all("SELECT * FROM users", (err, row) => {
    if (err) return next(err)
    res.render("admin/users.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.post('/admin/users', logger, userLevel('admin'), (req, res) => {

  if (req.body.add) {
    bcrypt.hash(req.body.password, 10, (err, hash) => {
      if (err) return next(err)
      db.run(`INSERT INTO users (user,password, email, department, userlevel) VALUES (?,?,?,?,?)`, [req.body.username, hash, req.body.email, req.body.department, req.body.userlevel], (err) => {
        if (err) return next(err)
        res.redirect('back')
      })
    })
  }

  if (req.body.delete) {
    db.run("DELETE FROM users WHERE id=?", [req.body.delete], (err) => {
      if (err) return next(err)
      res.redirect('back')
    })
  }

})
//---------------------------------------------------------
// #endregion

// #region samples
app.get('/samples/upload', userLevel("user"), department("print"), (req, res) => {
  res.render("samples/upload.ejs", { session, darkmode })
})
//---------------------------------------------------------
app.post('/samples/upload', logger, userLevel("user"), department("print"), async (req, res, next) => {

  if (!req.files || Object.keys(req.files).length === 0) {
    return res.status(400).send('No files were uploaded.');
  }

  if (Array.isArray(req.files.files)) { // forEach will only work on arrays so make sure it is. is there a better way to do this?
    var picsArray = [...req.files.files]
  } else {
    var picsArray = []
    picsArray.push(req.files.files)
  }

  picsArray.sort() // first taken pic will become the thumbnail

  let picNames = []


  await new Promise((resolve, reject) => {

    picsArray.forEach(e => { // move files
      e.mv(__dirname + '/public/Files_Images/' + e.name, (err) => { if (err) return next(err) })
      picNames.push(e.name)
      if (debug) console.log(`${e.name} moved to Files_Images`)
    })

    if (debug) console.log("Move uploaded files complete")
    setTimeout(() => { resolve() }, 1000);

  })


  await im.convert([__dirname + '/public/Files_Images/' + picsArray[0].name, '-resize', '320x240', __dirname + '/public/Files_Images.tn/' + picsArray[0].name], (err) => {
    if (err) console.error(err)
    if (debug) console.log(__dirname + '/public/Files_Images/' + picsArray[0].name + ' added to thumbnails');
  })

  db.run(`INSERT INTO samples (name, number, date, otherref, printdata, printdataback, printdataother, notes, printer, pics) VALUES (?,?,?,?,?,?,?,?,?,?)`,
    [req.body.jobname, req.body.ordernumber, timestampOfNow(), req.body.otherref, req.body.printdata1, req.body.printdata2, req.body.printdata3, req.body.notes, req.session.userName, JSON.stringify(picNames)], async (err) => {
      if (err) return next(err)
      const lastID = await runSQL("SELECT rowid FROM samples ORDER BY rowid DESC LIMIT 1", [], next)
      res.redirect(`/samples/view/${lastID[0].rowid}`)
    })

})
//---------------------------------------------------------
app.get('/samples', (req, res) => {
  res.redirect('/samples/search')
})
//---------------------------------------------------------
app.post('/samples/search', (req, res, next) => {
  db.all(`SELECT * FROM samples WHERE name LIKE ? OR number LIKE ? OR otherref LIKE ? ORDER BY date DESC`, [`%${req.body.search.trim()}%`, `%${req.body.search.trim()}%`, `%${req.body.search.trim()}%`], (err, row) => {
    if (err) return next(err)
    res.render("samples/search.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.get('/samples/search', (req, res, next) => {
  db.all("SELECT * FROM samples ORDER BY rowid DESC LIMIT 5", (err, row) => {
    if (err) return next(err)
    const Recent = 1
    res.render("samples/search.ejs", { row, session, darkmode, Recent })
  })
})
//---------------------------------------------------------
app.get('/samples/view/:id', (req, res, next) => {
  db.get(`SELECT * FROM samples WHERE rowid=${req.params.id}`, (err, row) => {
    if (err) return next(err)
    res.render("samples/view.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.get('/samples/edit/:id', department("print"), (req, res, next) => {
  db.get("SELECT * FROM samples WHERE rowid=?", [req.params.id], (err, row) => {
    if (err) return next(err)
    if (req.session.userName == row.printer || req.session.userLevel == "admin") {
      res.render("samples/edit.ejs", { row, session, darkmode })
    } else {
      res.status(400).send("You cant edit others work. sorry")
    }
  })
})
//---------------------------------------------------------
app.post('/samples/edit/:id', logger, department("print"), (req, res, next) => {

  if (req.body.delete === "delete") {
    db.run("DELETE FROM samples WHERE rowid =?", [req.params.id], (err) => {
      if (err) return next(err)
      res.redirect('/samples/search')
    })
  }

  if (req.body.submit) {
    db.get(`SELECT * FROM samples WHERE rowid=${req.params.id}`, (err, row) => {
      if (err) return next(err)
      if (req.session.userName == row.printer || req.session.userLevel == "admin") {
        db.run("UPDATE samples SET name = ?, number = ?, otherref = ?, printdata = ?, printdataback = ?, printdataother = ?, notes = ? WHERE rowid=?",
          [req.body.jobname, req.body.ordernumber, req.body.otherref, req.body.printdata1, req.body.printdata2, req.body.printdata3, req.body.notes, req.params.id]
        )
        res.redirect(`/samples/view/${req.params.id}`)
      } else {
        res.send("You cant edit others work. sorry")
      }
    })
  }

})
//---------------------------------------------------------
// #endregion

// #region orders
app.get('/orders/add', userLevel("admin"), (req, res) => {
  res.render("orders/add.ejs", { session, darkmode })
})
//---------------------------------------------------------
app.post('/orders/add', logger, userLevel("admin"), (req, res, next) => {
  if (req.body.addjob) {
    db.run("INSERT INTO jobs ( print,emb,transfer,aspre,digi,sample,swatch,ordernumber,ordername,takendate,duedate,machine,pos,screens,stitchcount,date,byuser,json,notes,complete,deliverytime ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
      [req.body.print, req.body.emb, req.body.transfer, req.body.aspre, req.body.digi, req.body.sample, req.body.swatch, req.body.ordernumber, req.body.ordername, (new Date(req.body.takendate)).getTime() / 1000, (new Date(req.body.duedate)).getTime() / 1000, "unallocated", req.body.pos, req.body.screens, req.body.stitchcount, Date.now() / 1000, req.session.userName, JSON.stringify(req.body), "", "0", req.body.deliverytime], async (err) => {
        if (err) return next(err)
        newOrderID = await runSQL("SELECT id FROM jobs ORDER BY id DESC LIMIT 1", [], next)
        res.redirect(`/orders/edit/${newOrderID[0].id}`)
      })
  }
})
//---------------------------------------------------------
app.get('/orders/edit/:id', userLevel("admin"), (req, res, next) => {
  db.get("SELECT * FROM jobs WHERE id=?", [req.params.id], async (err, row) => {
    if (err) return next(err)
    const reps = await runSQL("SELECT name FROM reps", [], next)
    const machines = await runSQL("SELECT name FROM machines", [], next)
    const debugVar = debug
    res.render("orders/edit.ejs", { row, session, darkmode, machines, reps, debugVar })
  })
})
//---------------------------------------------------------
app.post('/orders/edit/:id', logger, userLevel("admin"), async (req, res, next) => {

  if (req.body.edit) {
    db.run("UPDATE jobs set 'print' = ? ,'emb' = ?,'transfer' = ?, 'aspre' = ?, 'digi' = ?, 'sample' = ?, 'swatch' = ?, 'ordernumber' = ?, 'ordername' = ?, 'takendate' = ?,'duedate' = ?,'pos' = ?,'screens' = ?,'hasscreens' = ?,'hasstock' = ?,'hasapp' = ?,'stitchcount' = ?, 'ispacked' = ?, 'deliverytime' = ? WHERE id=?",
      [req.body.print, req.body.emb, req.body.transfer, req.body.aspre, req.body.digi, req.body.sample, req.body.swatch, req.body.ordernumber, req.body.ordername, (new Date(req.body.takendate)).getTime() / 1000, (new Date(req.body.duedate)).getTime() / 1000, req.body.pos, req.body.screens, req.body.hasscreens, req.body.hasstock, req.body.hasapp, req.body.stitchcount, req.body.ispacked, req.body.deliverytime, req.params.id], (err) => {
        if (err) return next(err)
      })
  }

  if (req.body.addStock) {
    var result = await runSQL("SELECT garments FROM jobs WHERE id=?", [req.params.id], next)
    try {
      var garments = JSON.parse(result[0].garments)
      garments.push({ "code": req.body.code, "colour": req.body.colour, "size": req.body.size, "amount": req.body.amount })
    } catch {// if garments not valid json eg. when no garments exist
      var garments = []
      garments.push({ "code": req.body.code, "colour": req.body.colour, "size": req.body.size, "amount": req.body.amount })
    }
    db.run("UPDATE jobs SET garments = ? WHERE id=?", [JSON.stringify(garments), req.params.id], (err) => {
      if (err) return next(err)
    })
  }

  if (req.body.deleteStock) {
    var result = await runSQL("SELECT garments FROM jobs WHERE id=?", [req.params.id], next)
    try { var garments = JSON.parse(result[0].garments) } catch { var garments = []; console.error("delete garments error in parse") }
    var newGarments = []
    for (var i = 0; i < garments.length; i++) {
      if (req.body.deleteStock == i) { continue }
      newGarments.push(garments[i])
    }
    db.run("UPDATE jobs SET garments = ? WHERE id=?", [JSON.stringify(newGarments), req.params.id], (err) => {
      if (err) return next(err)
    })
  }

  if (req.body.addNotes) {
    db.run("UPDATE jobs SET notes = ? WHERE id=?", [req.body.notes.trim(), req.params.id], (err) => {
      if (err) return next(err)
    })
  }

  if (req.body.bookIn) {
    db.run("UPDATE jobs SET bookindate = ?, machine = ? WHERE id=?", [timestampfromFormInputDate(req.body.bookInDate), req.body.machine, req.params.id], (err) => {
      if (err) return next(err)
    })
    var job = await runSQL("SELECT rep, emailrep FROM jobs WHERE id=?", [req.params.id], next) // get job options 
    if (job[0].emailrep === 'on') {
      updateRep(job[0].rep, req.params.id, `Order booked in for production ${timestampToDateAndTime(timestampfromFormInputDate(req.body.bookInDate))}`, next) //send email
    }
  }

  if (req.body.options) {
    db.run("UPDATE jobs SET rep = ?, emailrep = ? WHERE id=?", [req.body.rep, req.body.email, req.params.id], (err) => {
      if (err) return next(err)
    })
  }

  io.emit('refresh', req.params.id) // refresh clients
  res.redirect('back')
})
//---------------------------------------------------------
app.get('/orders/search', async (req, res, next) => {

  const todayTimestamp = timestampOfTodaysDate()
  const machines = await runSQL("SELECT name FROM machines", [], next)

  if (req.query.search) {
    if (req.query.all) { // show all jobs
      db.all("SELECT * FROM jobs WHERE ordername LIKE ? OR ordernumber LIKE ? ORDER BY duedate ASC", [`%${req.query.search}%`, `%${req.query.search}%`], (err, row) => {
        if (err) return next(err)
        res.render("orders/search.ejs", { row, session, darkmode, machines, todayTimestamp })
      })
    } else { // show only incomplete jobs
      db.all("SELECT * FROM jobs WHERE (ordername LIKE ? OR ordernumber LIKE ?) AND complete='0' ORDER BY duedate ASC", [`%${req.query.search}%`, `%${req.query.search}%`], (err, row) => {
        if (err) return next(err)
        res.render("orders/search.ejs", { row, session, darkmode, machines, todayTimestamp })
      })
    }
    return
  }

  if (req.query.select) {
    db.all("SELECT * FROM jobs WHERE complete='0' AND  machine=? ORDER BY duedate ASC ", [req.query.select], (err, row) => {
      if (err) return next(err)
      res.render("orders/search.ejs", { row, session, darkmode, machines, todayTimestamp })
    })
    return
  }

  // if no options given
  db.all("SELECT * FROM jobs WHERE complete='0' ORDER BY duedate ASC", (err, row) => {
    if (err) return next(err)
    res.render("orders/search.ejs", { row, session, darkmode, machines, todayTimestamp })
  })

})
//---------------------------------------------------------
app.post('/orders/search', (req, res) => {

})
//---------------------------------------------------------
app.get('/orders/view/:id', async (req, res, next) => {

  const socketIoIP = process.env.SERVER_ADDRESS
  const socketIoPort = process.env.SOCKET_LISTEN_PORT

  db.get("SELECT * FROM jobs WHERE id =?", [req.params.id], async (err, row) => {
    if (err) return next(err)
    const sampleID = await runSQL("SELECT rowid FROM samples WHERE number =?", [row?.ordernumber], next)
    const debugVar = debug
    res.render("orders/view.ejs", { row, session, darkmode, sampleID, socketIoIP, socketIoPort, debugVar })
  })

})
//--------------------------------------------------------- 
app.post('/orders/view/:id', logger, userLevel('user'), async (req, res, next) => {

  if (req.body.update) {
    db.run("UPDATE jobs SET notes = ?, hasstock = ?, hasscreens = ?, hasapp = ?, ispacked = ? WHERE rowid=?",
      [req.body.notes.trim(), req.body.hasstock, req.body.hasscreens, req.body.hasapp, req.body.ispacked, req.params.id], (err) => {
        if (err) return next(err)
        io.emit('refresh', req.params.id) // refresh clients 
        res.redirect('back')
      })
  }

  if (req.body.completejob) { //complete job
    db.run("UPDATE jobs SET completed = ?, complete = ?, completeby = ? WHERE rowid=?", [timestampOfNow(), "on", req.session.userName, req.params.id], (err) => {
      if (err) return next(err)
      io.emit('refresh', req.params.id) // refresh clients 
      res.redirect('back')
    })
    const completedJob = await runSQL("SELECT rep, emailrep FROM jobs WHERE id =?", [req.params.id], next)
    if (completedJob[0].emailrep === 'on') updateRep(completedJob[0].rep, req.params.id, 'Order complete', next) //send email
  }

})
//--------------------------------------------------------- 
// #endregion

// #region stores
app.get('/stores/stock-out/', (req, res, next) => {

  // if (req.query.search == "") { req.query.search = "%" }  // make empty search return all
  db.all("SELECT * FROM goodsout WHERE number LIKE ? OR name LIKE ? ", [`%${req.query.search}%`, `%${req.query.search}%`], (err, row) => {
    if (err) return next(err)
    req.session.search = req.query.search
    res.render("stores/stock-out.ejs", { row, session, darkmode })
  })

})
//---------------------------------------------------------
app.post('/stores/stock-out', logger, userLevel('user'), async (req, res, next) => {

  if (req.body.remove) { // remove stock from stores
    const result = await runSQL("SELECT id FROM goodsout WHERE number LIKE ?", [`%${req.body.ordernumber}%`], next)

    if (result.length != 0) {
      res.send(`<script>alert('already removed form stores');window.location.replace('/stores/stock-out?search=${req.body.ordernumber}');</script>`)
      return
    }

    db.run(`INSERT INTO goodsout (name, number, date, removedby, complete) VALUES (?,?,?,?,?)`, [req.body.ordername, req.body.ordernumber, timestampOfNow(), req.session.userName, req.body.complete], (err) => {
      if (err) return next(err)
      res.redirect(`/stores/stock-out?search=${req.body.ordernumber}`)
    })

  }

})
//---------------------------------------------------------
app.get('/stores/search/', (req, res, next) => {

  //  if (req.query.search == "") { req.query.search = " " }  // make empty search return all
  db.all("SELECT * FROM short WHERE ordernumber LIKE ? OR productcode LIKE ?", [`%${req.query.search}%`, `%${req.query.search}%`], (err, row) => {
    if (err) return next(err)
    req.session.search = req.query.search
    res.render("stores/short.ejs", { row, session, darkmode })
  })

})
//---------------------------------------------------------
app.post('/stores/search', logger, department('stores'), (req, res, next) => {

  if (req.body.delete) { //delete short    
    db.run(`DELETE FROM short WHERE id =${req.body.delete}`, (err) => {
      if (err) return next(err)
      res.redirect('back')
    })
  }

  if (req.body.add) { // add short
    db.run(`INSERT INTO short (ordernumber, productcode, size, colour, amount, date) VALUES (?,?,?,?,?,?)`,
      [req.body.ordernumber.trim(), req.body.productcode.trim(), req.body.size.trim(), req.body.colour.trim(), req.body.amount.trim(), req.body.date.trim()], (err) => {
        if (err) return next(err)
        res.redirect(`/stores/search/?search=${req.body.ordernumber}`)
      })
  }

  if (req.body.edit) { // edit short
    db.run("UPDATE short SET ordernumber = ?, productcode = ?, size = ?, colour = ?, amount = ?, date = ? WHERE id=?",
      [req.body.ordernumber.trim(), req.body.productcode.trim(), req.body.size.trim(), req.body.colour.trim(), req.body.amount.trim(), req.body.date.trim(), req.body.id], (err) => {
        if (err) return next(err)
        res.redirect('back')
      })
  }

})
//---------------------------------------------------------
app.use((err, req, res, next) => { // error handler

  if (res.headersSent) {
    return next(err)
  }
  if (process.env.NODE_ENV === 'production') {
    res.send(`Something broke!<br>${err}<br>if the problem persists contact your administrator `)
  }
  log.write("ERROR, " + err + '\n')

})
//---------------------------------------------------------
// #endregion

// #region functions
async function updateRep(rep, jobID, msg, next) {
  const email = await runSQL("SELECT email FROM reps WHERE name = ?", [rep], next)
  const job = await runSQL("SELECT * FROM jobs WHERE id = ?", [jobID], next)
  sendMail(email[0].email, `Order update ${job[0].ordernumber} - ${job[0].ordername}`, msg)
  log.write("email sent to " + rep + '\n')
}

function logger(req, res, next) {
  let messages = []

  messages.push(req.originalUrl)

  if (req.body.login) {
    messages.push(JSON.stringify(req.body.user))
  } else {
    messages.push(JSON.stringify(req.body))
  }

  if (req.session.userName) {
    messages.push(JSON.stringify(req.session.userName))
  } else {
    messages.push(JSON.stringify("UNKNOWN"))
  }

  messages.push(new Date().toLocaleString())

  if (debug) console.log(messages.join(" , "))
  log.write(messages.join(" , ") + "\n")

  next()

}

// #endregion