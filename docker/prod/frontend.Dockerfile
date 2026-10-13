# Nuxt SSR production build for galary-frontend
# node:22 — rollup-plugin-visualizer@7 requires node >=22
FROM node:22
WORKDIR /app
COPY package.json yarn.lock ./
RUN corepack enable && yarn install --frozen-lockfile --network-timeout 600000
COPY . .
RUN yarn build
EXPOSE 3000
CMD ["node", ".output/server/index.mjs"]
