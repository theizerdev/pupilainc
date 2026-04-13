FROM node:20-alpine

WORKDIR /app

# Dependencias del sistema necesarias para instalar paquetes y healthcheck
RUN apk add --no-cache git curl

# Install dependencies
COPY package*.json ./
RUN npm install

# Copy source code
COPY . .

# Create sessions directory
RUN mkdir -p sessions

# Expose port
EXPOSE 3000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD sh -c 'curl -f http://localhost:${PORT:-3001}/health || exit 1'

# Start application
CMD ["npm", "start"]