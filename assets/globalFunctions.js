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

async function showModal(link) {
  const element = document.getElementById("modal");
  const res = await fetch(link);
  const reply = await res.text();
  element.innerHTML = reply;
  //const closeButton = document.createElement("button");
  //closeButton.innerHTML = "<button>testing</button>";
  //element.appendChild(closeButton);
  HRtimestamp();
  element.showModal();
}

function closeModal() {
  const modal = document.getElementById("modal");
  modal.innerHTML = "";
  modal.close();
}

function toggleNavbar() {
  let menu = document.getElementById("navbar");
  if (menu.style.display === "flex") {
    menu.style.display = "none";
    menu.style.width = "0";
  } else {
    menu.style.display = "flex";
    menu.style.width = "var(--navbar-width)";
  }
}

function flashUser(text) {
  const element = document.getElementById("modal");
  element.innerText = text;
  element.showModal();
  queryParams.delete("flashUser");
  const newUrl = `${window.location.pathname}?${queryParams.toString()}`;
  window.history.pushState({}, "", newUrl);
  setTimeout(() => {
    closeModal();
  }, 2000);
}

const queryParams = new URLSearchParams(window.location.search);
const flash = queryParams.get("flashUser");
if (flash) flashUser(flash);
