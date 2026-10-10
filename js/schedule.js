import { computeGroupIndex, pickOnePerColumn, renderActivity } from "./schedule-utils.js";

const ROTATION_INTERVAL_MS = 10000;

const params = new URLSearchParams(window.location.search);
const monitorMode = params.get("m") === "1";
const timeOverride = params.get("t");
const conventionOverride = params.get("c") === "1";

let loadedSchedule;

const apiQuery = (() => {
  const p = new URLSearchParams();
  if (timeOverride) p.set("t", timeOverride);
  if (conventionOverride) p.set("c", "1");
  if (monitorMode) p.set("m", "1");
  const s = p.toString();
  return s ? `?${s}` : "";
})();

function currentMinute() {
  const time = timeOverride ?? new Date().toLocaleTimeString("sv-SE", {
    timeZone: "Europe/Stockholm",
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });
  return parseInt(time.split(":")[1], 10);
}

function currentTime() {
  return timeOverride ?? new Date().toLocaleTimeString("sv-SE", {
    timeZone: "Europe/Stockholm",
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });
}

function currentTurn() {
  return Math.floor(Date.now() / ROTATION_INTERVAL_MS);
}

function msUntilNextTurn() {
  return ROTATION_INTERVAL_MS - (Date.now() % ROTATION_INTERVAL_MS);
}

function updateClock() {
  const clockEl = document.getElementById("clock");
  if (clockEl && !timeOverride) {
    clockEl.textContent = currentTime();
  }
}

function renderActivityContainer(activities, { withHeadings = false, isLoggedIn = false } = {}) {
  const groupIndex = computeGroupIndex(activities, currentMinute());
  let html = `<div class="activity-container">`;
  if (withHeadings) {
    html += `<h3 class="desktop-only-heading">Filmer</h3>`;
    html += `<h3 class="desktop-only-heading">Aktiviteter</h3>`;
  }
  for (const activity of activities) {
    html += renderActivity(activity, groupIndex, isLoggedIn);
  }
  html += `</div>`;
  return html;
}

function renderActivities(data, isLoggedIn) {
  const activitiesEl = document.getElementById("activities");
  let html = "";

  if (data.current !== undefined) {
    const ongoing = monitorMode ? pickOnePerColumn(data.current, currentTurn()) : data.current;
    html += `<h2>Pågående</h2>`;
    html += renderActivityContainer(ongoing, { withHeadings: true, isLoggedIn });
    html += `<h2>Kommande</h2>`;
    html += renderActivityContainer(data.coming, { withHeadings: true, isLoggedIn });
  } else {
    html += `<h2>Schema</h2>`;
    html += renderActivityContainer(data.activities, { isLoggedIn });
  }

  activitiesEl.innerHTML = html;

  if (monitorMode && data.fullday !== undefined) {
    const fulldayEl = document.getElementById("fullday-activities");
    fulldayEl.innerHTML = renderActivityContainer(data.fullday, { isLoggedIn });
  }

  window.dispatchEvent(new Event("resize"));
}

async function loadNotifications() {
  const notifQuery = timeOverride ? `?t=${encodeURIComponent(timeOverride)}` : "";
  const response = await fetch(`api_notifications${notifQuery}`);
  if (!response.ok) throw new Error(`HTTP ${response.status}`);
  const { notifications } = await response.json();
  const el = document.getElementById("notifications");
  if (!el) return;
  el.innerHTML = notifications
    .map((msg) => `<div class="notification"><img src="icons/notifiering.png" />${msg}</div>`)
    .join("");
}

async function loadSchedule(isLoggedIn) {
  const response = await fetch(`api_list_activities${apiQuery}`);
  if (!response.ok) throw new Error(`HTTP ${response.status}`);
  const { data } = await response.json();
  loadedSchedule = data;
  renderActivities(loadedSchedule, isLoggedIn);
}

(async () => {
  const isLoggedIn = document.getElementById("activities").dataset.loggedIn === "1";

  try {
    await Promise.all([loadSchedule(isLoggedIn), loadNotifications()]);
  } catch (err) {
    console.error("Failed to load activities:", err);
    document.getElementById("activities").innerHTML = "<p>Kunde inte ladda schemat.</p>";
  }

  updateClock();
  setInterval(updateClock, 60000);

  setInterval(async () => {
    try {
      await Promise.all([loadSchedule(isLoggedIn), loadNotifications()]);
      updateClock();
    } catch (err) {
      console.error("Failed to reload:", err);
    }
  }, 60000);

  if (monitorMode) {
    const showNextTurn = () => {
      setTimeout(showNextTurn, msUntilNextTurn());
      if (loadedSchedule) renderActivities(loadedSchedule, isLoggedIn);
    };
    setTimeout(showNextTurn, msUntilNextTurn());
  }
})();
