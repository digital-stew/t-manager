async function logout() {
  const res = await fetch("/api/logout.php");
  if (res.ok) window.location = "/";
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

function HRtimestamp(full = false) {
  let timestamp = document.querySelectorAll(".timestamp");
  let formattedDate;
  timestamp.forEach((element) => {
    if (element.dataset.datetime == "true") {
      formattedDate = new Date(element.innerText * 1000).toLocaleString();
    } else {
      formattedDate = new Date(element.innerText * 1000).toLocaleDateString();
    }

    if (formattedDate === "Invalid Date") return;
    element.innerText = formattedDate;
  });
}

async function showModal(link) {
  closeModal();
  const element = document.getElementById("modal");
  const res = await fetch(link);
  const reply = await res.text();
  element.innerHTML = reply;
  HRtimestamp();
  element.showModal();
  element.addEventListener("click", function (event) {
    var rect = element.getBoundingClientRect();
    var isInDialog =
      rect.top <= event.clientY &&
      event.clientY <= rect.top + rect.height &&
      rect.left <= event.clientX &&
      event.clientX <= rect.left + rect.width;

    // stop modal closing on input selection
    if (event.target.matches("select")) return;
    if (event.target.matches("option")) return;
    if (event.target.matches("button")) return;
    if (event.target.matches("input")) return;

    if (!isInDialog) {
      closeModal();
    }
  });
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
