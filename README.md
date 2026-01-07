# PHP Extension Matrix - v2

A production-grade static site for visualizing PHP extension availability across different platforms, architectures, and PHP versions.

## Features

- 📊 **Matrix Grid View**: Comprehensive grid showing extension availability
- 🔍 **Advanced Filtering**: Filter by channel, platform, platform version, and architecture
- 🔎 **Search**: Quick search across extensions
- 📱 **Responsive Design**: Works on desktop and mobile
- ⚡ **High Performance**: Virtualized rows for 200+ extensions
- 🔗 **Shareable URLs**: Filters persist in URL query parameters
- 📦 **Zero Backend**: Pure static site, hosted on GitHub Pages

## Tech Stack

- **React 18** with TypeScript
- **Vite** for blazing fast builds
- **react-window** for virtualization
- **GitHub Pages** for hosting
- **GitHub Actions** for CI/CD

## Getting Started

### Prerequisites

- Bun 1.0+

### Installation

```bash
bun install
```

### Development

Run the development server with sample data:

```bash
bun run dev
```

Open [http://localhost:5173](http://localhost:5173) in your browser.

### Build

```bash
bun run build
```

### Preview Production Build

```bash
bun run preview
```

## Configuration

The site loads dataset from a configurable URL. Set the dataset URL via:

1. **Environment variable** (build time):
   ```bash
   VITE_DATASET_URL=https://example.com/data bun run build
   ```

2. **Default**: Uses `./sample-data/manifest.json` for local development

## Dataset Format

The site expects two JSON files:

### manifest.json
```json
{
  "latest": "snapshots/latest.json",
  "snapshots": [
    {
      "id": "2024-01-01",
      "url": "snapshots/2024-01-01.json",
      "timestamp": "2024-01-01T00:00:00Z"
    }
  ]
}
```

### snapshots/latest.json
```json
{
  "timestamp": "2024-01-01T00:00:00Z",
  "extensions": [
    {
      "name": "gd",
      "availability": [
        {
          "channel": "stable",
          "platform": "linux",
          "platform_version": "alpine3.19",
          "arch": "x86_64",
          "php_versions": ["8.1", "8.2", "8.3"],
          "available": true
        }
      ]
    }
  ]
}
```

## Deployment

The site is automatically deployed to GitHub Pages via GitHub Actions when you push to the `main` branch.

### Manual Deployment

```bash
bun run build
# Upload the dist/ folder to your static hosting
```

## Project Structure

```
php-ext-com/
├── .github/
│   └── workflows/
│       └── deploy.yml          # GitHub Pages deployment
├── public/
│   └── sample-data/            # Sample dataset for local dev
│       ├── manifest.json
│       └── snapshots/
│           └── latest.json
├── src/
│   ├── components/             # React components
│   ├── hooks/                  # Custom React hooks
│   ├── types/                  # TypeScript type definitions
│   ├── utils/                  # Utility functions
│   ├── App.tsx                 # Main app component
│   ├── main.tsx               # Entry point
│   └── index.css              # Global styles
├── index.html                 # HTML template
├── package.json
├── tsconfig.json
├── vite.config.ts
└── README.md
```

## License

MIT
