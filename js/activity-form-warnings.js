import { findRoomConflicts } from "./room-conflicts.js";

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

function showSummaryLength(note, warningLength) {
  const length = summary.value.length;
  const isTooLong = length > warningLength;
  note.textContent = isTooLong
    ? `Beskrivningen är för lång: ${length} av ${warningLength} tecken.`
    : `${length} av ${warningLength} tecken`;
  note.classList.toggle("form-warning", isTooLong);
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
