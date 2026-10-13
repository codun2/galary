# Nuxt SSR production build for galary-frontend
FROM node:20
WORKDIR /app
COPY package.json yarn.lock ./
RUN corepack enable && yarn install --frozen-lockfile --network-timeout 600000
COPY . .
RUN yarn build
EXPOSE 3000
CMD ["node", ".output/server/index.mjs"]
