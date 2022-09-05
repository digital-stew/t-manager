const {debug, log} = require('../server.js')
const sendMail = require('./email.js').sendMail
const runSQL = require('./sqlite.js').runSQL

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

function logger (req, res, next) {

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

async function updateRep(rep, jobID, msg, next) {
    const email = await runSQL("SELECT email FROM reps WHERE name = ?", [rep], next)
    const job = await runSQL("SELECT * FROM jobs WHERE id = ?", [jobID], next)
    sendMail(email[0].email, `Order update ${job[0].ordernumber} - ${job[0].ordername}`, msg)
    log.write("email sent to " + rep + '\n')
  }

module.exports = { timestampOfTodaysDate, timestampfromFormInputDate, timestampOfNow, timestampToDateAndTime, logger, updateRep }