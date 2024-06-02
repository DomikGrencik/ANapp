FROM node:18-alpine
WORKDIR /AN

COPY package.json .
COPY package-lock.json .
COPY vite.config.ts .
COPY tsconfig.json .

RUN npm install

COPY ./src ./src
COPY index.html .

EXPOSE 80
CMD ["npm", "run", "dev"]
