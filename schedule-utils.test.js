import { describe, it, expect } from "vitest";
import {
  escapeHtml,
  calcDuration,
  gridClass,
  computeGroupIndex,
  renderActivity,
} from "./schedule-utils.js";

describe("escapeHtml", () => {
  it("escapes HTML special characters", () => {
    expect(escapeHtml('<script>alert("xss")</script>')).toBe(
      "&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;"
    );
  });
  it("escapes ampersands", () => {
    expect(escapeHtml("a & b")).toBe("a &amp; b");
  });
  it("handles null", () => {
    expect(escapeHtml(null)).toBe("");
  });
  it("handles undefined", () => {
    expect(escapeHtml(undefined)).toBe("");
  });
  it("passes through plain text unchanged", () => {
    expect(escapeHtml("hello world")).toBe("hello world");
  });
});

describe("calcDuration", () => {
  it("calculates duration in minutes", () => {
    expect(calcDuration("10:00", "11:30")).toBe(90);
  });
  it("handles same start and end", () => {
    expect(calcDuration("12:00", "12:00")).toBe(0);
  });
  it("handles short sessions", () => {
    expect(calcDuration("14:45", "15:00")).toBe(15);
  });
  it("handles whole hours", () => {
    expect(calcDuration("09:00", "12:00")).toBe(180);
  });
});

describe("gridClass", () => {
  it("returns correct class for non-fullday activity", () => {
    const activity = { type_rownumber: "1", activity_column: "MOVIE" };
    expect(gridClass(activity, 0)).toBe("grid-start-3");
  });
  it("offsets by row number for non-fullday", () => {
    const activity = { type_rownumber: "3", activity_column: "LECTURE" };
    expect(gridClass(activity, 0)).toBe("grid-start-5");
  });
  it("returns grid-start for visible fullday activity", () => {
    const activity = { type_rownumber: "2", activity_column: "FULLDAY" };
    expect(gridClass(activity, 1)).toBe("grid-start-3");
  });
  it("hides fullday activity when row < groupIndex", () => {
    const activity = { type_rownumber: "0", activity_column: "FULLDAY" };
    expect(gridClass(activity, 2)).toBe("activity-hide");
  });
  it("shows fullday activity when row equals groupIndex", () => {
    const activity = { type_rownumber: "2", activity_column: "FULLDAY" };
    expect(gridClass(activity, 2)).toBe("grid-start-4");
  });
});

const fulldayActivities = [
  { activity_column: "FULLDAY", type_rownumber: "0" },
  { activity_column: "FULLDAY", type_rownumber: "1" },
  { activity_column: "FULLDAY", type_rownumber: "2" },
];

describe("computeGroupIndex", () => {
  it("returns a higher index later in the hour", () => {
    const early = computeGroupIndex(fulldayActivities, 0);
    const late = computeGroupIndex(fulldayActivities, 45);
    expect(early).toBeLessThan(late);
  });
  it("returns -1 when no fullday activities and minute is mid-hour", () => {
    const noFullday = [{ activity_column: "MOVIE", type_rownumber: "1" }];
    expect(computeGroupIndex(noFullday, 30)).toBe(-1);
  });
  it("uses only FULLDAY activities to determine maxRow", () => {
    const mixed = [
      { activity_column: "MOVIE", type_rownumber: "5" },
      { activity_column: "FULLDAY", type_rownumber: "1" },
    ];
    const withOnly = [{ activity_column: "FULLDAY", type_rownumber: "1" }];
    expect(computeGroupIndex(mixed, 30)).toBe(computeGroupIndex(withOnly, 30));
  });
});

const baseActivity = {
  id: "42",
  name: "Test aktivitet",
  type: "LECTURE",
  responsible_staff: "Test Person",
  summary: null,
  room: "Sal 1",
  explicit: false,
  updated_start_time: "10:00",
  updated_end_time: "11:00",
  outdated_start_time: null,
  outdated_end_time: null,
  icon_filename: "lecture.png",
  activity_column: "LECTURE",
  type_rownumber: "1",
  printing_rownumber: "0",
  printing_columnnumber: "0",
};

describe("renderActivity", () => {
  it("renders a section element", () => {
    const html = renderActivity(baseActivity, 0, false);
    expect(html).toContain("<section");
    expect(html).toContain("</section>");
  });
  it("shows activity name", () => {
    const html = renderActivity(baseActivity, 0, false);
    expect(html).toContain("Test aktivitet");
  });
  it("shows duration", () => {
    const html = renderActivity(baseActivity, 0, false);
    expect(html).toContain("60 min");
  });
  it("shows room", () => {
    const html = renderActivity(baseActivity, 0, false);
    expect(html).toContain("Sal 1");
  });
  it("does not show edit link when not logged in", () => {
    const html = renderActivity(baseActivity, 0, false);
    expect(html).not.toContain("edit_activity");
  });
  it("shows edit link when logged in", () => {
    const html = renderActivity(baseActivity, 0, true);
    expect(html).toContain("edit_activity?activity_id=42");
  });
  it("escapes HTML in name", () => {
    const activity = { ...baseActivity, name: "<b>XSS</b>" };
    const html = renderActivity(activity, 0, false);
    expect(html).toContain("&lt;b&gt;XSS&lt;/b&gt;");
  });
  it("shows explicit warning when explicit is true", () => {
    const activity = { ...baseActivity, explicit: true };
    const html = renderActivity(activity, 0, false);
    expect(html).toContain("18+");
  });
  it("does not show explicit warning when explicit is false", () => {
    const html = renderActivity(baseActivity, 0, false);
    expect(html).not.toContain("18+");
  });
  it("shows changed time when outdated_start_time is set", () => {
    const activity = { ...baseActivity, outdated_start_time: "09:00", outdated_end_time: "10:00" };
    const html = renderActivity(activity, 0, false);
    expect(html).toContain("Ändrad från");
    expect(html).toContain("09:00");
  });
  it("converts newlines to br tags in summary", () => {
    const activity = { ...baseActivity, summary: "Rad 1\nRad 2" };
    const html = renderActivity(activity, 0, false);
    expect(html).toContain("Rad 1<br>Rad 2");
  });
  it("escapes HTML in summary", () => {
    const activity = { ...baseActivity, summary: "<script>evil()</script>" };
    const html = renderActivity(activity, 0, false);
    expect(html).not.toContain("<script>");
    expect(html).toContain("&lt;script&gt;");
  });
  it("omits summary when null", () => {
    const html = renderActivity(baseActivity, 0, false);
    expect(html).not.toContain("<p>");
  });
  it("handles null activity_column gracefully", () => {
    const activity = { ...baseActivity, activity_column: null };
    expect(() => renderActivity(activity, 0, false)).not.toThrow();
  });
  it("sets print CSS variables on section", () => {
    const activity = { ...baseActivity, printing_rownumber: "2", printing_columnnumber: "1" };
    const html = renderActivity(activity, 0, false);
    expect(html).toContain("--printing-row: 4");
    expect(html).toContain("--printing-column: 1");
  });
});
