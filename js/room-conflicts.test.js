import { describe, it, expect } from "vitest";
import { findRoomConflicts } from "./room-conflicts.js";

const booked = (id, room, start, end) => ({
  id,
  name: `Aktivitet ${id}`,
  room,
  updated_start_time: start,
  updated_end_time: end,
});

const planned = (room, start, end, id = "") => ({ id, room, start, end });

describe("findRoomConflicts", () => {
  it("reports the activity that overlaps in the same room, not the one in another room", () => {
    const activities = [booked("1", "Gula rummet", "10:00", "11:00"), booked("2", "Blå rummet", "10:00", "11:00")];
    expect(findRoomConflicts(planned("Gula rummet", "10:30", "11:30"), activities)).toEqual([activities[0]]);
  });
  it("does not report activities that only touch the start or the end", () => {
    const activities = [booked("1", "Gula rummet", "09:00", "10:00"), booked("2", "Gula rummet", "11:00", "12:00")];
    expect(findRoomConflicts(planned("Gula rummet", "10:00", "11:00"), activities)).toEqual([]);
  });
  it("does not report the activity being edited", () => {
    const activities = [booked("7", "Gula rummet", "10:00", "11:00")];
    expect(findRoomConflicts(planned("Gula rummet", "10:00", "11:00", "7"), activities)).toEqual([]);
  });
  it("treats room names that differ only in case and surrounding spaces as one room", () => {
    const activities = [booked("1", "Stora Salen", "10:00", "11:00")];
    expect(findRoomConflicts(planned(" stora salen", "10:00", "11:00"), activities)).toEqual(activities);
  });
  it("leaves out a booked activity longer than three hours, but not one of exactly three", () => {
    const activities = [booked("1", "Gula rummet", "10:00", "13:00"), booked("2", "Gula rummet", "10:00", "13:01")];
    expect(findRoomConflicts(planned("Gula rummet", "12:00", "12:30"), activities)).toEqual([activities[0]]);
  });
  it("reports nothing for a planned activity longer than three hours", () => {
    const activities = [booked("1", "Gula rummet", "12:00", "13:00")];
    expect(findRoomConflicts(planned("Gula rummet", "10:00", "13:01"), activities)).toEqual([]);
  });
  it("reports nothing while no room is filled in", () => {
    const activities = [booked("1", "", "10:00", "11:00")];
    expect(findRoomConflicts(planned("", "10:00", "11:00"), activities)).toEqual([]);
  });
  it("reports nothing while a time is not written as hours and minutes", () => {
    const activities = [booked("1", "Gula rummet", "10:00", "11:00")];
    expect(findRoomConflicts(planned("Gula rummet", "10", "11:00"), activities)).toEqual([]);
  });
});
