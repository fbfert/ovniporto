# syntax=docker/dockerfile:1.7
#
# OVNIPORTO Inertia SSR server (node bootstrap/ssr/ssr.js, port 13714).
#
# The SSR bundle keeps these packages external (bare imports in ssr.js):
#   react, react/jsx-runtime, react-dom/server, @inertiajs/react(/server),
#   framer-motion (dependency of `motion`).
# The runtime stage therefore installs the production dependencies only.
#
# Build context: repository root.

ARG NODE_VERSION=22

FROM node:${NODE_VERSION}-alpine AS build
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci
# Lint configs are included so `make lint` can run `npm run lint` in this stage.
COPY vite.config.js vitest.config.js tsconfig.json eslint.config.js .prettierrc .prettierignore ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM node:${NODE_VERSION}-alpine AS runtime
ENV NODE_ENV=production
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci --omit=dev --omit=optional && npm cache clean --force
COPY --from=build /app/bootstrap/ssr ./bootstrap/ssr

USER node
EXPOSE 13714
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1:13714/health || exit 1
CMD ["node", "bootstrap/ssr/ssr.js"]
