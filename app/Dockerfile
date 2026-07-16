FROM node:20-alpine

WORKDIR /app

ENV CHOKIDAR_USEPOLLING=true

# Copia apenas os manifests primeiro pra aproveitar o cache de build
COPY package*.json ./
RUN npm ci

COPY . .

EXPOSE 5173

# --host é necessário para o Vite aceitar conexões de fora do container
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
