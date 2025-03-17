# VentlyX Frontend

## Overview
This is the frontend component of the VentlyX application, built with Vue.js 3 and TypeScript. It provides a modern, responsive user interface for event browsing, booking, and managing tickets.

## Technology Stack
- **Framework**: Vue.js 3
- **Language**: TypeScript
- **Routing**: Vue Router
- **State Management**: Pinia
- **Styling**: Tailwind CSS
- **Testing**: Vitest (Unit Tests) and Playwright (E2E Tests)

## Key Features
- Responsive design for all device types
- Interactive event browsing with filtering and search
- User authentication and profile management
- Ticket booking flow with seat selection
- QR code ticket display
- Payment processing with Stripe/PayPal integration
- Event organizer dashboard
- Admin controls and analytics

## Project Structure
- `src/assets`: Static assets like images and global styles
- `src/components`: Reusable Vue components
- `src/views`: Vue components that represent pages
- `src/router`: Vue Router configuration
- `src/stores`: Pinia state management stores
- `src/services`: API services for backend communication
- `src/types`: TypeScript type definitions
- `src/utils`: Utility functions and helpers
- `tests`: Unit and E2E tests

## Development Tools
- ESLint for code linting
- Prettier for code formatting
- Vite for fast development server and building
- Vue DevTools for debugging

## Recommended IDE Setup
[VSCode](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur).

## Type Support for `.vue` Imports in TS
TypeScript cannot handle type information for `.vue` imports by default, so we replace the `tsc` CLI with `vue-tsc` for type checking. In editors, we need [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) to make the TypeScript language service aware of `.vue` types.

## Project Setup and Usage

```sh
# Install dependencies
npm install

# Compile and Hot-Reload for Development
npm run dev

# Type-Check, Compile and Minify for Production
npm run build

# Run Unit Tests with Vitest
npm run test:unit

# Run End-to-End Tests with Playwright
# Install browsers for the first run
npx playwright install

# Runs the end-to-end tests
npm run test:e2e

# Runs the tests only on Chromium
npm run test:e2e -- --project=chromium

# Lint with ESLint
npm run lint
```

## Design and Style Guide
The application follows Material Design principles with a custom color scheme:
- Primary color: #3498db
- Secondary color: #2ecc71
- Accent color: #f39c12
- Text: #333333 / #ffffff

## See Also
- [RUNNING.md](../RUNNING.md) for setup and running instructions
- [Backend README](../backend/README.md) for backend documentation
