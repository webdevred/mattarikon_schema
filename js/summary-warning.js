const EXPIRED_TEXT = "Aktiviteten är nu borttagen.";

function formatClock(remainingMs) {
  const totalSeconds = Math.ceil(remainingMs / 1000);
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = totalSeconds % 60;
  return `${minutes}:${String(seconds).padStart(2, "0")}`;
}

export function summaryWarningMessage(length, warningLength, remainingMs) {
  if (length <= warningLength) {
    return { text: `${length} av ${warningLength} tecken`, isWarning: false };
  }
  if (remainingMs > 0) {
    return {
      text: `Beskrivningen är för lång: ${length} av ${warningLength} tecken. Aktiviteten tas bort om ${formatClock(remainingMs)}.`,
      isWarning: true,
    };
  }
  return { text: EXPIRED_TEXT, isWarning: true };
}
