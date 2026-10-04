# Contraste dos pares de tokens (npm run contrast, 2026-10-04)

```
ok    text-beam              on night      8.81:1
ok    text-beam              on night-blue 7.03:1
ok    text-beam-glow         on night      12.10:1
ok    text-beam-glow         on night-blue 9.65:1
ok    text-car               on night      10.81:1
ok    text-car               on night-blue 8.63:1
ok    text-horizon           on moonlight  7.90:1
ok    text-moonlight         on night      17.18:1
ok    text-moonlight         on night-blue 13.72:1
skip  text-moonlight/40      on night      3.55:1  (decorative "@" prefix in large type)
skip  text-moonlight/40      on night-blue 3.41:1  (decorative "@" prefix in large type)
ok    text-moonlight/50      on night      4.92:1
ok    text-moonlight/50      on night-blue 4.52:1
ok    text-moonlight/55      on night      5.70:1
ok    text-moonlight/55      on night-blue 5.14:1
ok    text-moonlight/60      on night      6.63:1
ok    text-moonlight/60      on night-blue 5.82:1
ok    text-moonlight/65      on night      7.59:1
ok    text-moonlight/65      on night-blue 6.63:1
ok    text-moonlight/70      on night      8.70:1
ok    text-moonlight/70      on night-blue 7.43:1
ok    text-moonlight/75      on night      9.85:1
ok    text-moonlight/75      on night-blue 8.28:1
ok    text-moonlight/80      on night      11.06:1
ok    text-moonlight/80      on night-blue 9.27:1
ok    text-moonlight/85      on night      12.48:1
ok    text-moonlight/85      on night-blue 10.26:1
ok    text-moonlight/90      on night      13.91:1
ok    text-moonlight/90      on night-blue 11.33:1
ok    text-night             on moonlight  17.18:1
ok    text-night/60          on moonlight  4.84:1
ok    text-night/65          on moonlight  5.73:1
ok    text-night/70          on moonlight  6.88:1
ok    text-night/75          on moonlight  8.16:1
ok    text-night/80          on moonlight  9.71:1
ok    text-night/85          on moonlight  11.60:1

Todos os pares de texto passam em AA (4,5:1).
```

Ajustes feitos (só opacidade): text-moonlight/45 → /50, text-night/55 → /60, placeholder:text-night/40 → /60.
