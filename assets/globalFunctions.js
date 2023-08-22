async function logout() {
  const res = await fetch("/api/logout.php");
  if (res.ok) window.location.reload();
}

async function replaceElement(element, link) {
  const res = await fetch(link);
  const reply = await res.text();
  try {
    const json = JSON.parse(reply);
    if (json) return setError(json.error);
  } catch (error) {
    document.getElementById(element).outerHTML = reply;
    HRtimestamp();
  }
}

function HRtimestamp() {
  let timestamp = document.querySelectorAll(".timestamp");
  timestamp.forEach((element) => {
    let formattedDate = new Date(element.innerText * 1000).toLocaleDateString();
    if (formattedDate === "Invalid Date") return;
    element.innerText = formattedDate;
  });
}

function setError(string) {
  document.getElementById("error").innerText = string;
  setTimeout(() => {
    document.getElementById("error").innerText = "";
  }, 2000);
}
