export function escapeHtml(text) {
  return String(text ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

export function calcDuration(start, end) {
  const [sh, sm] = start.split(":").map(Number);
  const [eh, em] = end.split(":").map(Number);
  return (eh - sh) * 60 + (em - sm);
}

export function gridClass(activity, groupIndex) {
  const row = parseInt(activity.type_rownumber, 10);
  if (activity.activity_column === "FULLDAY") {
    return row >= groupIndex
      ? `grid-start-${row + groupIndex}`
      : "activity-hide";
  }
  return `grid-start-${row + 2}`;
}

export function computeGroupIndex(activities, minute) {
  let maxRow = 0;
  for (const a of activities) {
    const row = parseInt(a.type_rownumber, 10);
    if (a.activity_column === "FULLDAY" && row > maxRow) {
      maxRow = row;
    }
  }
  let groupIndex = Math.floor((minute + 1) / (60 / (maxRow + 1))) - 1;
  if (groupIndex === maxRow) groupIndex -= 2;
  return groupIndex;
}

export function renderActivity(activity, groupIndex, isLoggedIn) {
  const duration = calcDuration(activity.updated_start_time, activity.updated_end_time);
  const col = (activity.activity_column ?? "other").toLowerCase();
  const cls = gridClass(activity, groupIndex);

  const printRow = parseInt(activity.printing_rownumber, 10) + 2;
  const printCol = parseInt(activity.printing_columnnumber, 10);
  let html = `<section class="activity ${cls} activity-${col}" style="--printing-row: ${printRow}; --printing-column: ${printCol};">`;
  html += `<img src="icons/${escapeHtml(activity.icon_filename)}" />`;
  html += `<span class="time">${escapeHtml(activity.updated_start_time)} - ${escapeHtml(activity.updated_end_time)} (${duration} min)</span>`;
  html += ` i <strong>${escapeHtml(activity.room)}</strong>`;

  if (activity.outdated_start_time) {
    html += `<div class="changed-time">Ändrad från ${escapeHtml(activity.outdated_start_time)} - ${escapeHtml(activity.outdated_end_time)}</div>`;
  }

  html += `<h3>${escapeHtml(activity.name)}</h3>`;

  if (activity.summary) {
    html += `<p>${escapeHtml(activity.summary).replace(/\n/g, "<br>")}</p>`;
  }

  if (isLoggedIn) {
    html += `<a href="edit_activity?activity_id=${escapeHtml(activity.id)}">Redigera aktivitet</a><br>`;
  }

  html += `<strong>Värd:</strong> ${escapeHtml(activity.responsible_staff)}<br />`;

  if (activity.explicit) {
    html += `<strong style="color: red;">OBS! Ej lämpat för barn. Åldersgräns 18+</strong>`;
  }

  html += "</section>";
  return html;
}
