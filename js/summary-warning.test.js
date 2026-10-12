import { describe, it, expect } from "vitest";
import { summaryWarningMessage } from "./summary-warning.js";

describe("summaryWarningMessage", () => {
  it("shows the plain counter when the summary is not too long", () => {
    expect(summaryWarningMessage(10, 300, 0)).toEqual({ text: "10 av 300 tecken", isWarning: false });
  });
  it("is not too long when the summary is exactly at the limit", () => {
    expect(summaryWarningMessage(300, 300, 0).isWarning).toBe(false);
  });
  it("counts down while the summary is too long", () => {
    expect(summaryWarningMessage(301, 300, 120000)).toEqual({
      text: "Beskrivningen är för lång: 301 av 300 tecken. Aktiviteten tas bort om 2:00.",
      isWarning: true,
    });
  });
  it("rounds the remaining time up to the next whole second", () => {
    expect(summaryWarningMessage(301, 300, 59500).text).toContain("om 1:00.");
    expect(summaryWarningMessage(301, 300, 1).text).toContain("om 0:01.");
  });
  it("shows the activity as removed once the countdown reaches zero", () => {
    expect(summaryWarningMessage(301, 300, 0)).toEqual({ text: "Aktiviteten är nu borttagen.", isWarning: true });
  });
  it("keeps showing the activity as removed after the deadline has passed", () => {
    expect(summaryWarningMessage(301, 300, -5000)).toEqual({ text: "Aktiviteten är nu borttagen.", isWarning: true });
  });
});
