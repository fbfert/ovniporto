/** "2026-10-07" → "07/10/2026", without letting the time zone move the day. */
export function reviewedOn(isoDate: string): string {
    const [year, month, day] = isoDate.split('-');
    return `${day}/${month}/${year}`;
}
