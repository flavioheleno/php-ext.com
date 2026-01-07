# PHP Extension Matrix - v2

A production-grade static site for visualizing PHP extension availability across different platforms, architectures, and PHP versions.

## Features

- 📊 **Matrix Grid View**: Comprehensive grid showing extension availability
- 🏠 **Multi-page App**: Home page, matrix view, and extension detail pages
- 🔍 **Advanced Filtering**: Filter by channel, platform, platform version, and architecture
- 🔎 **Search**: Quick search across extensions
- 📱 **Responsive Design**: Works on desktop and mobile
- ⚡ **High Performance**: Virtualized rows for 200+ extensions
- 🔗 **Shareable URLs**: Filters persist in URL query parameters
- 🚀 **GitHub Pages Ready**: Automatic deployment with configurable base path
- 📦 **Zero Backend**: Pure static site

## Tech Stack

- **React 18** with TypeScript
- **React Router** for client-side routing
- **Vite** for blazing fast builds
- **react-window** for virtualization
- **GitHub Pages** for hosting
- **GitHub Actions** for CI/CD
- **Bun** for package management

## Routes

- `/` - Home page with statistics and overview
- `/matrix` - Matrix grid view with filters
- `/ext/:name` - Extension detail page

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

### Environment Variables

The site supports the following build-time environment variables:

**VITE_BASE_PATH** (Required for GitHub Pages project sites)
- Sets the base path for routing and assets
- Default: `/`
- Project site: `/repository-name/` (e.g., `/php-ext-com/`)
- User/org site: `/`

**VITE_DATASET_BASE_URL** (Optional)
- Base URL for dataset files
- Default: `/sample-data/`
- Example: `https://api.example.com/data/` or `/data/`

**VITE_DATASET_URL** (Optional - overrides VITE_DATASET_BASE_URL)
- Full URL to manifest.json
- Example: `https://api.example.com/manifest.json`

### Local Development

```bash
# Use defaults
bun run dev

# With custom base path
VITE_BASE_PATH=/php-ext-com/ bun run dev

# With custom dataset
VITE_DATASET_URL=https://api.example.com/manifest.json bun run dev
```

### GitHub Pages Deployment

See **[GITHUB_PAGES_SETUP.md](./GITHUB_PAGES_SETUP.md)** for complete setup instructions.

Quick setup:
1. Enable GitHub Pages (Settings → Pages → Source: GitHub Actions)
2. Add repository variables in Settings → Secrets and variables → Actions:
   - `VITE_BASE_PATH`: `/repository-name/` (e.g., `/php-ext-com/`)
   - `VITE_DATASET_URL`: (optional) URL to your dataset
3. Push to main branch

Your site will be at: `https://username.github.io/repository-name/`

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

The site automatically deploys to GitHub Pages via GitHub Actions when you push to the `main` branch.

**See [GITHUB_PAGES_SETUP.md](./GITHUB_PAGES_SETUP.md) for complete configuration instructions.**

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
