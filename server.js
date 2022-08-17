const debug = true


require('dotenv').config()
const bcrypt = require('bcrypt');
var cookieParser = require('cookie-parser')
var im = require('imagemagick');
const fs = require('fs-extra')
var log = fs.createWriteStream(__dirname + '/log.txt', { flags: 'a' });
// #region ----socketIO setup-----------------------------
const io = require('socket.io')(process.env.SOCKET_LISTEN_PORT, {
  cors: {
    // origin: ['http//localhost:'+process.env.LISTEN_PORT],
    origin: [`http//${process.env.SERVER_ADDRESS}:${process.env.LISTEN_PORT}`],
  },
})
io.on('connection', (socket) => {
  if (debug) console.log("socket connection");
});
// #endregion
// #region ----email setup--------------------------------
var nodemailer = require('nodemailer');

var transporter = nodemailer.createTransport({
  service: 'gmail',
  auth: {
    user: 'digital.army@gmail.com',
    pass: 'imhauijwestayjxs'
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
// sendMail('digital.army@gmail.com','test','is this working?')
// #endregion
// #region ----express setup-----------------------------
const express = require('express')
const sessions = require('express-session');
const fileUpload = require('express-fileupload');

const app = express()

app.use(cookieParser())
app.use(fileUpload());
app.set("view engine", "ejs")

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(__dirname + '/public'));


const userLevel = (level) => {
  return (req, res, next) => {
    switch (level) {
      case "admin":
        if (req.session.userLevel == "admin") {
          next()
          break
        }
      case "user":
        if (req.session.userLevel == "user" || req.session.userLevel == "admin") {
          next()
          break
        }
      default:
        res.send("you are not authorized to access this page")
        break;
    }

    return
  }
}

const department = (department) => {
  return (req, res, next) => {
    switch (department) {
      case "print":
        if (req.session.department == "print" || req.session.userLevel == "admin") {
          next()
          break
        }
      case "stores":
        if (req.session.department == "stores" || req.session.userLevel == "admin") {
          next()
          break
        }
      case "office":
        if (req.session.department == "office" || req.session.userLevel == "admin") {
          next()
          break
        }
      default:
        res.send("you are not authorized to access this page")
        break;
    }

    return
  }
}
var darkmode
app.use((req, res, next) => {
  if (req.cookies.darkmode == true) {
    darkmode = true
  } else {
    darkmode = false
  }
  next()
})

app.use((err, req, res, next) => {
  if (debug) console.error(err.stack)
  res.status(500).send('Something broke!')
})


const oneDay = 1000 * 60 * 60 * 24;
app.use(sessions({
  secret: process.env.SESSION_SECRET_KEY,
  saveUninitialized: true,
  cookie: { maxAge: oneDay / 4 },
  resave: false
}))

var session
app.use((req, res, next) => {
  session = req.session
  next()
})

app.listen(process.env.LISTEN_PORT, () => {
  if (debug) console.log('server started on port: ' + process.env.LISTEN_PORT) //debug
})
// #endregion
// #region ----sqlite setup------------------------------
var sqlite3 = require('sqlite3');
const { parse } = require('dotenv');
var db = new sqlite3.Database(process.env.DB_LOCATION, sqlite3.OPEN_READWRITE, (err) => {
  if (err) {
    console.log("Getting error " + err);
    return (1);
  } else {
    if (debug) console.log('access to database enabled. location= ' + process.env.DB_LOCATION)
  }

});

// #endregion


app.get('/', (req, res) => {
  res.redirect('/production')
})
// #region admin
app.post('/login', logger, (req, res) => {
  db.get(`SELECT * FROM users WHERE user='${req.body.user.toLowerCase()}'`, (err, row) => {
    if (err) console.error(err)
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
        
        if (req.body.password == '123456'){
          res.redirect('/user')
        }else{
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
  db.get(`SELECT * FROM users WHERE user ='${req.session.userName}'`, (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      res.render("user.ejs", { row, session, darkmode })
    }
  })
})
//---------------------------------------------------------
app.post('/user', logger, (req, res) => {
  
  if (req.body.changePassword) {
    bcrypt.hash(req.body.password, 10, (err, hash) => {
      if(err) {
        onError(err,res)
      }else{
      db.run("UPDATE users SET password = ? WHERE user = ?",[hash, req.session.userName],(err) => {
          if (err) {
            onError(err, res)
          } 
        })
      }  
    })
  }

  if (req.body.changeEmail){
    db.run("UPDATE users SET email = ?",[req.body.email],(err) =>{
      if (err) onError(err, res)
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
    res.render("admin/machines.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.post('/admin/machines', logger, userLevel('admin'), (req, res) => {
  if (req.body.add) {
    db.run(`INSERT INTO machines (name, printheads, dryers) VALUES (?,?,?)`,
      [req.body.machine, req.body.printheads, req.body.dryers], (err) => {
        if (err) {
          onError(err, res)
        } else {
          res.redirect('back')
          return
        }
      })
  }
  if (req.body.delete) {
    db.run(`DELETE FROM machines WHERE id=${req.body.delete}`, (err) => {
      if (err) {
        onError(err, res)
      } else {
        res.redirect('back')
        return
      }
    })
  }
})
//---------------------------------------------------------
app.get('/admin/reps', userLevel('admin'), (req, res) => {
  db.all("SELECT * FROM reps", (err, row) => {
    res.render("admin/reps.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.post('/admin/reps', logger, userLevel('admin'), (req, res) => {
  if (req.body.add) {
    db.run(`INSERT INTO reps (name, email) VALUES (?,?)`,
      [req.body.name, req.body.email], (err) => {
        if (err) {
          onError(err, res)
        } else {
          res.redirect('back')
          return
        }
      })
  }
  if (req.body.delete) {
    db.run(`DELETE FROM reps WHERE id=${req.body.delete}`, (err) => {
      if (err) {
        onError(err, res)
      } else {
        res.redirect('back')
        return
      }
    })
  }
})
//---------------------------------------------------------
app.get('/admin/users', userLevel('admin'), (req, res) => {
  db.all("SELECT * FROM users", (err, row) => {
    res.render("admin/users.ejs", { row, session, darkmode })
  })
})
//---------------------------------------------------------
app.post('/admin/users', logger, userLevel('admin'), (req, res) => {
  if (req.body.add) {
    bcrypt.hash(req.body.password, 10, (err, hash) => {
      db.run(`INSERT INTO users (user,password, email, department, userlevel) VALUES (?,?,?,?,?)`,
        [req.body.username, hash, req.body.email, req.body.department, req.body.userlevel], (err) => {
          if (err) {
            onError(err, res)
          } else {
            res.redirect('back')
            return
          }
        })
    })
  }
  if (req.body.delete) {
    db.run(`DELETE FROM users WHERE id=${req.body.delete}`, (err) => {
      if (err) {
        onError(err, res)
      } else {
        res.redirect('back')
        return
      }
    })
  }
})
// #endregion

// #region samples
app.get('/samples/upload', userLevel("user"), department("print"), (req, res) => {
  res.render("samples/upload.ejs", { session, darkmode })
})
//---------------------------------------------------------
app.post('/samples/upload', logger, userLevel("user"), department("print"), (req, res) => {
  if (!req.files || Object.keys(req.files).length === 0) {
    return res.status(400).send('No files were uploaded.');
  }

  let date = Date.now() / 1000


  if (Array.isArray(req.files.files)) {
    var picsArray = [...req.files.files]
  } else {
    var picsArray = []
    picsArray.push(req.files.files)
  }

  picsArray.reverse()


  let picsJSON = "["

  picsArray.forEach(e => { // move files
    e.mv(__dirname + '/public/Files_Images/' + e.name);
    picsJSON = picsJSON.concat(`"${e.name}",`)
  });
  picsJSON = picsJSON.slice(0, -1) + "]"

  picsArray.forEach(e => { // make thums and save in .tn
    im.convert([__dirname + '/public/Files_Images/' + e.name, '-resize', '320x240', __dirname + '/public/Files_Images.tn/' + e.name],
      function (err, stdout) {
        if (err) onError(err)
        if (debug) console.log(__dirname + '/public/Files_Images/' + e.name + 'uploaded to database');
        // console.log(stdout)
      });
  })

  db.run(`INSERT INTO samples (name, number, date, otherref, printdata, printdataback, printdataother, notes, printer, pics) VALUES (?,?,?,?,?,?,?,?,?,?)`,
    [req.body.jobname, req.body.ordernumber, date, req.body.otherref, req.body.printdata1, req.body.printdata2, req.body.printdata3, req.body.notes, req.session.userName, picsJSON], (err) => {
      if (err) {
        onError(err, res)
      } else {
        db.get("SELECT rowid FROM samples ORDER BY rowid DESC LIMIT 1", (err, row) => {
          if (err) {
            onError(err, res)
          } else {
            res.redirect(`/samples/view/${row.rowid}`)
          }
        })
      }
    })
})
//---------------------------------------------------------
app.get('/samples', (req, res) => {
  res.redirect('/samples/search')
})
//---------------------------------------------------------
app.post('/samples/search', (req, res) => {
  db.all(`SELECT * FROM samples WHERE name LIKE '%${req.body.search.trim()}%' OR number LIKE '%${req.body.search.trim()}%' OR otherref LIKE '%${req.body.search.trim()}%' ORDER BY date DESC`, (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      res.render("samples/search.ejs", { row, session, darkmode })
    }
  })
})
//---------------------------------------------------------
app.get('/samples/search', (req, res) => {
  db.all("SELECT * FROM samples ORDER BY rowid DESC LIMIT 5", (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      const Recent = 1
      res.render("samples/search.ejs", { row, session, darkmode, Recent })
    }
  })
})
//---------------------------------------------------------
app.get('/samples/view/:id', (req, res) => {
  db.get(`SELECT * FROM samples WHERE rowid=${req.params.id}`, (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      res.render("samples/view.ejs", { row, session, darkmode })
    }
  })
})
//---------------------------------------------------------
app.get('/samples/edit/:id', department("print"), (req, res) => {
  db.get(`SELECT * FROM samples WHERE rowid=${req.params.id}`, (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      if (req.session.userName == row.printer || req.session.userLevel == "admin") {
        res.render("samples/edit.ejs", { row, session, darkmode })
      } else {
        res.status(400).send("You cant edit others work. sorry")
      }
    }
  })
})
//---------------------------------------------------------
app.post('/samples/edit/:id', logger, department("print"), (req, res) => {
  if (req.body.delete === "delete") {
    db.run(`DELETE FROM samples WHERE rowid =${req.params.id}`, (err) => {
      if (err) {
        onError(err, res)
      } else {
        res.redirect('/samples/search')
        return
      }
    })
  }
  if (req.body.submit) {
    db.get(`SELECT * FROM samples WHERE rowid=${req.params.id}`, (err, row) => {
      if (err) {
        onError(err, res)
      } else {
        if (req.session.userName == row.printer || req.session.userLevel == "admin") {
          db.run(`UPDATE samples SET name = ?, number = ?, otherref = ?, printdata = ?, printdataback = ?, printdataother = ?, notes = ? WHERE rowid=${req.params.id}`,
            [req.body.jobname, req.body.ordernumber, req.body.otherref, req.body.printdata1, req.body.printdata2, req.body.printdata3, req.body.notes]
          )
          res.redirect(`/samples/view/${req.params.id}`)
        } else {
          res.send("You cant edit others work. sorry")
        }
      }
    })
  }
})
// #endregion

// #region orders
app.get('/orders/add', userLevel('admin'), (req, res) => {
  res.render("orders/add.ejs", { session, darkmode })
})
//---------------------------------------------------------
app.post('/orders/add', logger, userLevel('admin'), (req, res, next) => {
  if (req.body.addjob) {
    db.run("INSERT INTO jobs ( print,emb,transfer,aspre,digi,sample,swatch,ordernumber,ordername,takendate,duedate,machine,pos,screens,stitchcount,date,byuser,json,notes,complete,deliverytime ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
      [req.body.print, req.body.emb, req.body.transfer, req.body.aspre, req.body.digi, req.body.sample, req.body.swatch, req.body.ordernumber, req.body.ordername, (new Date(req.body.takendate)).getTime() / 1000, (new Date(req.body.duedate)).getTime() / 1000, "unallocated", req.body.pos, req.body.screens, req.body.stitchcount, Date.now() / 1000, req.session.userName, JSON.stringify(req.body), "", "0", req.body.deliverytime], (err) => {
        if (err) {
          onError(err, res)
        } else {
          next()
        }
      })
  }
}, (req, res) => {
  db.get("SELECT id FROM jobs ORDER BY id DESC LIMIT 1", (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      res.redirect(`/orders/edit/${row.id}`)
    }
  })
})
//---------------------------------------------------------
app.get('/orders/edit/:id', userLevel("admin"), (req, res, next) => {

  db.all("SELECT name FROM machines", (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      res.locals.mach = row
      next()
    }
  })

}, (req,res,next) => {

  db.all("SELECT name FROM reps", (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      res.locals.reps = row
      next()
    }
  })

} ,(req, res) => {
  db.get(`SELECT * FROM jobs WHERE id=${req.params.id}`, (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      const mach = res.locals.mach
      const reps = res.locals.reps
      res.render("orders/edit.ejs", { row, session, darkmode, mach, reps })
    }
  })
})
//---------------------------------------------------------
app.post('/orders/edit/:id', logger, userLevel("admin"), (req, res, next) => { // put admin only back
  if (req.body.edit) {
    db.run(`UPDATE jobs set 'print' = ? ,'emb' = ?,'transfer' = ?, 'aspre' = ?, 'digi' = ?, 'sample' = ?, 'swatch' = ?, 'ordernumber' = ?, 'ordername' = ?, 'takendate' = ?,'duedate' = ?,'pos' = ?,'screens' = ?,'hasscreens' = ?,'hasstock' = ?,'hasapp' = ?,'stitchcount' = ?, 'ispacked' = ?, 'deliverytime' = ? WHERE id=${req.params.id}`,
      [req.body.print, req.body.emb, req.body.transfer, req.body.aspre, req.body.digi, req.body.sample, req.body.swatch, req.body.ordernumber, req.body.ordername, (new Date(req.body.takendate)).getTime() / 1000, (new Date(req.body.duedate)).getTime() / 1000, req.body.pos, req.body.screens, req.body.hasscreens, req.body.hasstock, req.body.hasapp, req.body.stitchcount, req.body.ispacked, req.body.deliverytime], (err) => {
        if (err) {
          onError(err, res)
        }
      })
  }

  if (req.body.addStock) {
    db.get(`SELECT garments FROM jobs WHERE id=${req.params.id}`, (err, row) => {
      if (err) {
        onError(err, res)
      } else {
        res.locals.add = req.body.addStock
        res.locals.garments = row.garments
        next()
        return
      }
    })
  }

  if (req.body.deleteStock) {
    db.get(`SELECT garments FROM jobs WHERE id=${req.params.id}`, (err, row) => {
      if (err) {
        onError(err, res)
      } else {
        res.locals.delete = req.body.deleteStock
        res.locals.garments = row.garments
        next()
        return
      }
    })
  }

  if (req.body.addNotes) {
    db.run(`UPDATE jobs SET notes = ? WHERE id=${req.params.id}`, [req.body.notes.trim()], (err) => {
      if (err) {
        onError(err, res)
      }
    })
  }

  if (req.body.bookIn) {
    db.run(`UPDATE jobs SET bookindate = ?, machine = ? WHERE id=${req.params.id}`, [timestampfromFormInputDate(req.body.bookInDate), req.body.machine], (err) => {
      if (err) {
        onError(err, res)
      }
      //send email here
    })
  }

  if(req.body.options){
    db.run(`UPDATE jobs SET rep = ?, emailrep = ? WHERE id=${req.params.id}`, [req.body.rep,req.body.email], (err) => {
      if (err) {
        onError(err, res)
      }
    })
  }

  io.emit('refresh', req.params.id) // refresh clients
  res.redirect('back')
}, (req, res) => {


  if (res.locals.delete) {
    var garments = JSON.parse(res.locals.garments)
    var newGarments = []
    for (var i = 0; i < garments.length; i++) {
      if (res.locals.delete == i) { continue }
      newGarments.push(garments[i])
    }

    db.run(`UPDATE jobs SET garments = ? WHERE id=${req.params.id}`, [JSON.stringify(newGarments)], (err) => {
      if (err) {
        onError(err, res)
      }
    })
  }

  if (res.locals.add) {

    try { // if garments not valid json eg. when no garments exist
      var garments = JSON.parse(res.locals.garments)
      garments.push({ "code": req.body.code, "colour": req.body.colour, "size": req.body.size, "amount": req.body.amount })
    } catch {
      var garments = []
      garments.push({ "code": req.body.code, "colour": req.body.colour, "size": req.body.size, "amount": req.body.amount })
    }

    db.run(`UPDATE jobs SET garments = ? WHERE id=${req.params.id}`, [JSON.stringify(garments)], (err) => {
      if (err) {
        onError(err, res)
      }
    })
  }

})
//---------------------------------------------------------
app.get('/orders/search', (req, res, next) => {

  db.all("SELECT name FROM machines", (err, machines) => {
    if (err) {
      onError(err, res)
    } else {
      res.locals.machines = machines
      next()
    }

  })

}, (req, res) => {
  const todayTimestamp = timestampOfTodaysDate()
  const machines = res.locals.machines
  if (req.query.search) {
    //if (req.query.search == '' || req.query.search == undefined) { req.query.search = "%" } // make empty search return all
    if (req.query.all) { // show all jobs
      db.all(`SELECT * FROM jobs WHERE ordername LIKE '%${req.query.search}%' OR ordernumber LIKE '%${req.query.search}%' ORDER BY duedate ASC `, (err, row) => {
        if (err) {
          onError(err, res)
        } else {
          res.render("orders/search.ejs", { row, session, darkmode, machines, todayTimestamp })
        }

      })
    } else { // show only incomplete jobs
      db.all(`SELECT * FROM jobs WHERE (ordername LIKE '%${req.query.search}%' OR ordernumber LIKE '%${req.query.search}%') AND complete="0" ORDER BY duedate ASC `, (err, row) => {
        if (err) {
          onError(err, res)
        } else {
          res.render("orders/search.ejs", { row, session, darkmode, machines, todayTimestamp })
        }
      })
    }
    return
  }

  if (req.query.select) {
    db.all(`SELECT * FROM jobs WHERE complete="0" AND  machine='${req.query.select}' ORDER BY duedate ASC `, (err, row) => {
      if (err) {
        onError(err, res)
      } else {
        res.render("orders/search.ejs", { row, session, darkmode, machines, todayTimestamp })
      }
    })
    return
  }

  // if no options given
  db.all(`SELECT * FROM jobs WHERE complete="0" ORDER BY duedate ASC `, (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      res.render("orders/search.ejs", { row, session, darkmode, machines, todayTimestamp })
    }
  })
  return
})
//---------------------------------------------------------
app.post('/orders/search', (req, res) => {

})
//---------------------------------------------------------
app.get('/orders/view/:id', (req, res, next) => {

  db.get(`SELECT * FROM jobs WHERE id=${req.params.id}`, (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      res.locals.job = row
      next()
    }
  })

}, (req, res) => {

  db.get(`SELECT rowid FROM samples WHERE number =${res.locals.job.ordernumber}`, (err, sampleID) => {
    const row = res.locals.job
    res.render("orders/view.ejs", { row, session, darkmode, sampleID })
  })
})
//--------------------------------------------------------- 
app.post('/orders/view/:id', logger, userLevel('user'), (req, res) => {

  if (req.body.update) {
    db.run(`UPDATE jobs SET notes = ?, hasstock = ?, hasscreens = ?, hasapp = ?, ispacked = ? WHERE rowid=${req.params.id}`,
      [req.body.notes.trim(), req.body.hasstock, req.body.hasscreens, req.body.hasapp, req.body.ispacked], (err) => {
        if (err) {
          onError(err, res)
        }
      })
  }

  if (req.body.completejob) {
    db.run(`UPDATE jobs SET completed = ?, complete = ?, completeby = ? WHERE rowid=${req.params.id}`,
      [Date.now() / 1000, "on", req.session.userName], (err) => {
        if (err) {
          onError(err, res)
        }
      })
  }

  io.emit('refresh', req.params.id) // refresh clients
  res.redirect('back')
})
// #endregion

// #region stores
app.get('/stores/stock-out/', (req, res) => {

  if (req.query.search == "") { req.query.search = "%" }  // make empty search return all
  db.all(`SELECT * FROM goodsout WHERE number LIKE '%${req.query.search}%' OR name LIKE '%${req.query.search}%'`, (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      req.session.search = req.query.search
      res.render("stores/stock-out.ejs", { row, session, darkmode })
    }
  })

})
//---------------------------------------------------------
app.post('/stores/stock-out', logger, userLevel('user'), (req, res, next) => {
  if (req.body.remove) { // remove stock from stores
    db.get(`SELECT id FROM goodsout WHERE number LIKE '%${req.body.ordernumber}%'`, (err, row) => {
      if (err) {
        onError(err, res)
      } else {
        if (row == undefined) {
          next()
        } else {
          res.send("already out")
          return
        }
      }
    })


  }
}, (req, res) => {
  db.run(`INSERT INTO goodsout (name, number, date, removedby, complete) VALUES (?,?,?,?,?)`,
    [req.body.ordername, req.body.ordernumber, timestampOfNow(), req.session.userName, req.body.complete], (err) => {
      if (err) {
        onError(err, res)
      } else {
        res.redirect(`/stores/stock-out?search=${req.body.ordernumber}`)
        return
      }
    })
})
//---------------------------------------------------------
app.get('/stores/search/', (req, res) => {

  if (req.query.search == "") { req.query.search = "%" }  // make empty search return all
  db.all(`SELECT * FROM short WHERE ordernumber LIKE '%${req.query.search}%' OR productcode LIKE '%${req.query.search}%'`, (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      req.session.search = req.query.search
      res.render("stores/short.ejs", { row, session, darkmode })
    }
  })

})
//---------------------------------------------------------
app.post('/stores/search', logger, department('stores'), (req, res) => {

  if (req.body.delete) {
    //delete short
    db.run(`DELETE FROM short WHERE id =${req.body.delete}`, (err) => {
      if (err) {
        onError(err, res)
      }
    })
    res.redirect('back')
  }

  if (req.body.add) {
    db.run(`INSERT INTO short (ordernumber, productcode, size, colour, amount, date) VALUES (?,?,?,?,?,?)`,
      [req.body.ordernumber.trim(), req.body.productcode.trim(), req.body.size.trim(), req.body.colour.trim(), req.body.amount.trim(), req.body.date.trim()], (err) => {
        if (err) {
          onError(err, res)
        } else {
          res.redirect(`/stores/search/?search=${req.body.ordernumber}`)
          return
        }
      })
  }

  if (req.body.edit) {
    db.run(`UPDATE short SET ordernumber = ?, productcode = ?, size = ?, colour = ?, amount = ?, date = ? WHERE id=${req.body.id}`,
      [req.body.ordernumber.trim(), req.body.productcode.trim(), req.body.size.trim(), req.body.colour.trim(), req.body.amount.trim(), req.body.date.trim()], (err) => {
        if (err) {
          onError(err, res)
        } else {
          res.redirect('back')
          return
        }
      })
  }



})
//---------------------------------------------------------
// #endregion

app.get('/production', (req, res) => {
  var selected_date = null
  if (req.query.selected_date) {
    var searchDate = timestampfromFormInputDate(req.query.selected_date)
    selected_date = req.query.selected_date
  } else {

    var searchDate = timestampOfTodaysDate()
  }

  db.all(`SELECT * FROM jobs WHERE bookindate = ? ORDER BY duedate ASC `, [searchDate], (err, row) => {
    if (err) {
      onError(err, res)
    } else {
      const todayTimestamp = timestampOfTodaysDate()
      res.render('production.ejs', { row, session, darkmode, searchDate, selected_date, todayTimestamp })
    }
  })

})

// INK
app.get('/ink', (req, res) => {
  db.all("SELECT * FROM inkdata", (err, row) => {
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
        if (err) {
          onError(err, res)
        } else {
          res.redirect('back')
          return
        }
      })
  }
  if (req.body.delete) {
    db.run(`DELETE FROM inkdata WHERE id=${req.body.delete}`, (err) => {
      if (err) {
        onError(err, res)
      } else {
        res.redirect('back')
        return
      }
    })
  }
})









// #region functions
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

function onError(err, res) {
  log.write("ERROR, " + err + '\n')
  if (debug) console.log("ERROR!, " + err + '\n')
  return
}
function timestampOfTodaysDate() {
  var dateString = new Date().toLocaleDateString() // output = 30/12/2022
  var temp = dateString.split("/")
  var timestampOfTodaysDate = new Date(`${temp[2]}-${temp[1]}-${temp[0]}`).getTime() / 1000
  return timestampOfTodaysDate
}
function timestampfromFormInputDate(formInput) {
  return (new Date(formInput)).getTime() / 1000
}
function timestampOfNow() {
  return Date.now() / 1000
}
function timestampToDateAndTime(timestamp) {
  return new Date(timestamp * 1000).toLocaleDateString()
}

// #endregion
