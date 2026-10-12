import { findRoomConflicts } from "./room-conflicts.js";
import { summaryWarningMessage } from "./summary-warning.js";

const COUNTDOWN_MS = 2 * 60 * 1000;

const summary = document.querySelector("#summary");
const room = document.querySelector("#room");
const startTime = document.querySelector("#start-time");
const endTime = document.querySelector("#end-time");
const activityId = document.querySelector("input[name='activity_id']");

function noteAfter(field) {
  const note = document.createElement("p");
  note.className = "form-note";
  note.setAttribute("role", "status");
  field.closest("fieldset").after(note);
  return note;
}

let countdownDeadline = null;
let countdownExpired = false;
let countdownTimer = null;

function stopCountdownTimer() {
  if (countdownTimer) {
    clearInterval(countdownTimer);
    countdownTimer = null;
  }
}

function showSummaryLength(note, warningLength) {
  const length = summary.value.length;
  const isTooLong = length > warningLength;

  if (!isTooLong) {
    stopCountdownTimer();
    countdownDeadline = null;
    countdownExpired = false;
  } else if (countdownDeadline === null && !countdownExpired) {
    countdownDeadline = Date.now() + COUNTDOWN_MS;
    countdownTimer = setInterval(() => showSummaryLength(note, warningLength), 1000);
  }

  const remainingMs = isTooLong ? (countdownExpired ? 0 : countdownDeadline - Date.now()) : 0;
  if (isTooLong && !countdownExpired && remainingMs <= 0) {
    countdownExpired = true;
    stopCountdownTimer();
  }

  const { text, isWarning } = summaryWarningMessage(length, warningLength, remainingMs);
  note.textContent = text;
  note.classList.toggle("form-warning", isWarning);
}

function showRoomConflicts(note, activities) {
  const conflicts = findRoomConflicts({ id: activityId.value, room: room.value, start: startTime.value, end: endTime.value },
    activities);
  const listed = conflicts.map((activity) => `${activity.name} (${activity.updated_start_time} - ${activity.updated_end_time})`);
  note.textContent = conflicts.length > 0 ? `Samma rum och tid som: ${listed.join(", ")}` : "";
  note.classList.toggle("form-warning", conflicts.length > 0);
}

async function loadActivities() {
  // The API lists what is ongoing or coming at ?t, so midnight returns every activity.
  const response = await fetch("api_list_activities?t=00:00");
  if (!response.ok) throw new Error(`HTTP ${response.status}`);
  const { data } = await response.json();
  return [...data.current, ...data.coming];
}

const warningLength = Number(summary.dataset.warningLength);
if (warningLength > 0) {
  const lengthNote = noteAfter(summary);
  summary.addEventListener("input", () => showSummaryLength(lengthNote, warningLength));
  showSummaryLength(lengthNote, warningLength);
}

const conflictNote = noteAfter(endTime);
try {
  const activities = await loadActivities();
  for (const field of [room, startTime, endTime]) {
    field.addEventListener("input", () => showRoomConflicts(conflictNote, activities));
  }
  showRoomConflicts(conflictNote, activities);
} catch (err) {
  console.error("Failed to load activities for the room check:", err);
  conflictNote.textContent = "Kunde inte kontrollera om rummet är upptaget.";
  conflictNote.classList.add("form-warning");
}
