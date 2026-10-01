import { cubicBezier } from 'motion/react';

/**
 * Motion tokens shared by every animated component. Values follow the
 * `animate` / `emil-design-eng` skills: strong custom curves, UI under 300ms.
 */
export const ease = {
    /** Entrances, exits, anything answering the user. */
    snap: [0.23, 1, 0.32, 1],
    /** Movement across the screen. */
    glide: [0.77, 0, 0.175, 1],
    /** Drawers and full-screen panels. */
    drawer: [0.32, 0.72, 0, 1],
} as const satisfies Record<string, readonly [number, number, number, number]>;

/** Same curves as functions, for scroll-linked useTransform segments. */
export const easeFn = {
    snap: cubicBezier(...ease.snap),
    glide: cubicBezier(...ease.glide),
    linear: (v: number) => v,
} as const;

export const duration = {
    press: 0.14,
    ui: 0.22,
    panel: 0.42,
    reveal: 0.6,
} as const;

export const spring = {
    /** Polaroids settling on the table. */
    paper: { type: 'spring', duration: 0.6, bounce: 0.18 },
    /** Hover straightening: interruptible, no overshoot. */
    soft: { type: 'spring', duration: 0.45, bounce: 0 },
} as const;

/** Stagger between siblings (the brief asks for 80ms between polaroids). */
export const STAGGER = 0.08;
