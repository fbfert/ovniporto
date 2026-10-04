# Análise do bundle (vite build, 2026-10-04)

- `Pages/Content/Faq.tsx`: 21 chunks JS estáticos, 594 kB sem gzip; código de mapa no carregamento inicial: não
- `Pages/Home.tsx`: 30 chunks JS estáticos, 645 kB sem gzip; código de mapa no carregamento inicial: não
- `Pages/Sightings/Logbook.tsx`: 24 chunks JS estáticos, 610 kB sem gzip; código de mapa no carregamento inicial: não
- `Pages/Store/Index.tsx`: 23 chunks JS estáticos, 598 kB sem gzip; código de mapa no carregamento inicial: não

Leaflet só entra por import dinâmico (LazyMap, PointPicker): `assets/leaflet.markercluster-src-BjAg-9RQ.js`, `assets/leaflet-src-Bes0EAlV.js`.
