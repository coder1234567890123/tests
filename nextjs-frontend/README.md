# Next.js Frontend

This is the Next.js frontend for the application.

## Getting Started

First, install the dependencies:
```bash
npm install
# or
yarn install
```

Then, run the development server:
```bash
npm run dev
# or
yarn dev
```

Open [http://localhost:3000](http://localhost:3000) with your browser to see the result.

## Environment Configuration
This project uses environment variables for configuration. For local development, create a `.env.local` file in the `nextjs-frontend` root directory:
```bash
cp .env.example .env.local
```
Update the variables in `.env.local` as needed. The primary variable is `NEXT_PUBLIC_API_URL` which should point to your running backend API.
