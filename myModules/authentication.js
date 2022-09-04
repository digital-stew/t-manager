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

  module.exports = {department, userLevel }