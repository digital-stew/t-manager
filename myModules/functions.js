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

module.exports = { timestampOfTodaysDate, timestampfromFormInputDate, timestampOfNow, timestampToDateAndTime}