const FULLDAY_MINUTES = 180;

function toMinutes(time) {
  const match = /^(\d{1,2}):(\d{2})$/.exec(time.trim());
  return match ? Number(match[1]) * 60 + Number(match[2]) : NaN;
}

function toInterval(start, end) {
  const [startMinute, endMinute] = [toMinutes(start), toMinutes(end)];
  return { start: startMinute, end: endMinute, isFullday: endMinute - startMinute > FULLDAY_MINUTES };
}

function normalizeRoom(room) {
  return room.trim().toLowerCase();
}

export function findRoomConflicts(candidate, activities) {
  const own = toInterval(candidate.start, candidate.end);
  const room = normalizeRoom(candidate.room);
  if (own.isFullday || room === "") return [];
  return activities.filter((activity) => {
    if (activity.id === candidate.id || normalizeRoom(activity.room) !== room) return false;
    const other = toInterval(activity.updated_start_time, activity.updated_end_time);
    return !other.isFullday && own.start < other.end && other.start < own.end;
  });
}
