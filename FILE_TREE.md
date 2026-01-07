# Complete File Tree and Contents

## File Tree Structure

```
php-ext-com/
├── .github/
│   └── workflows/
│       └── deploy.yml                 # GitHub Actions workflow
├── public/
│   ├── sample-data/
│   │   ├── snapshots/
│   │   │   └── latest.json           # Sample snapshot data
│   │   └── manifest.json             # Sample manifest
│   └── favicon.svg                   # Site favicon
├── src/
│   ├── components/
│   │   ├── CellDrawer.tsx           # Cell detail drawer component
│   │   ├── ExtensionDetail.tsx      # Extension detail modal (legacy)
│   │   ├── FilterBar.tsx            # Filter controls component
│   │   ├── Layout.tsx               # Main layout with nav/footer
│   │   └── MatrixGrid.tsx           # Virtualized matrix grid
│   ├── hooks/
│   │   ├── useDataset.ts            # Dataset loading hook
│   │   └── useFilters.ts            # Filter state management hook
│   ├── pages/
│   │   ├── ExtensionDetailPage.tsx  # /ext/:name route page
│   │   ├── HomePage.tsx             # / route page
│   │   └── MatrixPage.tsx           # /matrix route page
│   ├── types/
│   │   └── index.ts                 # TypeScript type definitions
│   ├── utils/
│   │   ├── data.ts                  # Data transformation utilities
│   │   └── url.ts                   # URL parameter utilities
│   ├── App.tsx                      # Main app with router
│   ├── index.css                    # Global styles
│   ├── main.tsx                     # Entry point
│   └── vite-env.d.ts               # Vite environment types
├── .eslintrc.cjs                    # ESLint configuration
├── .gitignore                       # Git ignore rules
├── COMMANDS.md                      # Commands reference
├── DEPLOYMENT.md                    # Deployment guide
├── DEVELOPMENT.md                   # Development guide
├── GITHUB_PAGES_SETUP.md           # GitHub Pages setup guide
├── index.html                       # HTML template
├── package.json                     # NPM dependencies
├── PROJECT_SUMMARY.md              # Project summary
├── QUICKSTART.md                   # Quick start guide
├── README.md                        # Main documentation
├── tsconfig.json                    # TypeScript configuration
├── tsconfig.node.json              # TypeScript Node configuration
└── vite.config.ts                  # Vite build configuration
```

## Key Files Content Overview

### Configuration Files

**vite.config.ts**
- Configures base path from `VITE_BASE_PATH` environment variable
- Sets up code splitting (react-vendor, virtualization)
- Enables source maps
- Defines build output directory

**tsconfig.json**
- TypeScript strict mode enabled
- ES2020 target
- React JSX configuration
- Module resolution: bundler

**package.json**
- React 18 + react-router-dom
- TypeScript 5
- Vite 5
- react-window for virtualization
- ESLint for linting
- Bun scripts (dev, build, preview, lint)

**.github/workflows/deploy.yml**
- Triggers on push to main
- Builds with Bun
- Uses repository variables: VITE_BASE_PATH, VITE_DATASET_URL, VITE_DATASET_BASE_URL
- Deploys to GitHub Pages

### Source Code

**src/App.tsx**
- Sets up BrowserRouter with basename from import.meta.env.BASE_URL
- Defines routes: /, /matrix, /ext/:name
- Wraps all routes in Layout component

**src/components/Layout.tsx**
- Navigation bar with Home and Matrix links
- Active route highlighting
- Footer
- Responsive container

**src/pages/HomePage.tsx**
- Landing page with hero section
- Statistics cards (extensions, channels, platforms, etc.)
- Feature list
- Links to /matrix

**src/pages/MatrixPage.tsx**
- Filter bar
- Virtualized matrix grid
- Cell drawer for details
- Navigation to extension detail pages

**src/pages/ExtensionDetailPage.tsx**
- Extension detail view
- Groups availability by PHP version
- Back navigation button
- Displays all platform configurations

**src/hooks/useDataset.ts**
- Fetches manifest.json and snapshot data
- Uses VITE_DATASET_URL or VITE_DATASET_BASE_URL
- Default: /sample-data/
- Handles loading and error states

**src/hooks/useFilters.ts**
- Manages filter state
- Syncs with URL query parameters
- Provides setFilters and resetFilters

**src/utils/data.ts**
- filterExtensions: Apply filters to extension list
- buildMatrixData: Build matrix rows
- getUniqueValues: Extract unique filter values
- getUniquePHPVersions: Extract PHP versions

**src/utils/url.ts**
- getFiltersFromURL: Read filters from query params
- setFiltersToURL: Write filters to query params
- Parameters: channel, platform, pv (platform_version), arch, q (search)

### Styles

**src/index.css**
- CSS custom properties for theming
- Navigation styles
- Page layouts
- Home page hero and stats
- Matrix grid styles
- Responsive design
- Mobile optimizations

### Data Files

**public/sample-data/manifest.json**
```json
{
  "latest": "snapshots/latest.json",
  "snapshots": [...]
}
```

**public/sample-data/snapshots/latest.json**
- Sample dataset with 10 extensions
- Multiple channels (stable, edge)
- Multiple platforms (linux)
- Multiple platform versions (alpine3.19, debian12, ubuntu22.04)
- Multiple architectures (x86_64, aarch64)
- PHP versions: 8.1, 8.2, 8.3

## Environment Variables

### Build-time Variables

**VITE_BASE_PATH**
- Purpose: Base path for routing and assets
- Default: `/`
- Project site example: `/php-ext-com/`
- Used in: vite.config.ts, App.tsx (via import.meta.env.BASE_URL)

**VITE_DATASET_URL**
- Purpose: Full URL to manifest.json
- Default: Not set
- Example: `https://api.example.com/manifest.json`
- Overrides VITE_DATASET_BASE_URL

**VITE_DATASET_BASE_URL**
- Purpose: Base URL for dataset files
- Default: `/sample-data/`
- Example: `https://api.example.com/data/`
- Appends `manifest.json` automatically

## Routing Structure

| Route | Component | Description |
|-------|-----------|-------------|
| `/` | HomePage | Landing page with stats and info |
| `/matrix` | MatrixPage | Matrix grid with filters |
| `/ext/:name` | ExtensionDetailPage | Extension detail view |

All routes support:
- URL query parameters for filters
- Direct linking and sharing
- Browser back/forward navigation
- Refresh without losing state

## Build Output

Production build creates:
- `dist/index.html` - HTML entry point
- `dist/assets/` - JS and CSS chunks
  - `react-vendor-*.js` - React and react-router-dom (~176 KB, ~58 KB gzipped)
  - `virtualization-*.js` - react-window (~9 KB, ~3 KB gzipped)
  - `index-*.js` - Application code (~16 KB, ~5 KB gzipped)
  - `index-*.css` - Styles (~9 KB, ~2 KB gzipped)
- `dist/sample-data/` - Sample dataset (copied from public/)
- Source maps for debugging

Total size: ~210 KB (~68 KB gzipped)

## Key Features

1. **Routing with react-router-dom**
   - BrowserRouter with configurable basename
   - Nested routes
   - Programmatic navigation
   - URL parameter support

2. **GitHub Pages Support**
   - Configurable base path via environment variable
   - Works for both user/org and project sites
   - Automatic deployment via GitHub Actions

3. **Environment-based Configuration**
   - VITE_BASE_PATH for routing
   - VITE_DATASET_URL for full dataset URL
   - VITE_DATASET_BASE_URL for base path
   - Separate configs for dev and production

4. **Responsive Design**
   - Mobile-first approach
   - Responsive navigation
   - Flexible grid layouts
   - Touch-friendly controls

5. **Performance Optimizations**
   - Code splitting
   - Virtualized lists
   - Memoization
   - Lazy loading potential
