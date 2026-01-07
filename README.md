# PHP Extension Matrix - v2

A production-grade static site for visualizing PHP extension build status across different platforms, architectures, and PHP versions.

**Live Demo:** [https://your-username.github.io/php-ext.com/](https://your-username.github.io/php-ext.com/)

## Features

- 🏠 **Home Dashboard**: Dataset status, overall readiness, top failing extensions
- 📊 **Matrix Grid View**: Comprehensive grid showing build status across PHP versions
- 📝 **Extension Details**: Deep-dive into individual extension build results
- 🔍 **Advanced Filtering**: Filter by channel, platform, platform version, and architecture
- 🔎 **Real-time Search**: Quick search across all extensions
- 📱 **Responsive Design**: Works seamlessly on desktop, tablet, and mobile
- ⚡ **High Performance**: Virtualized rows handle 200+ extensions smoothly
- 🔗 **Shareable URLs**: All filters and state persist in URL for easy sharing
- 🎨 **Accessible**: ARIA labels, keyboard navigation, color contrast
- 🚀 **GitHub Pages Ready**: Hash routing, automatic deployment, configurable paths
- 📦 **Zero Backend**: Pure static site with client-side data loading

## Tech Stack

- **React 18** with TypeScript (strict mode)
- **React Router** with HashRouter for GitHub Pages compatibility
- **Vite** for blazing fast builds and HMR
- **react-window** for virtualization
- **Bun** for fast package management and builds
- **GitHub Actions** for automated deployment
- **GitHub Pages** for hosting

## Routes

- `/#/` - Home page with statistics, failing extensions, dataset status
- `/#/matrix` - Matrix grid view with filters and cell details
- `/#/ext/:name` - Extension detail page with build history and failures

## Quick Start

### Prerequisites

- **Bun** 1.0 or higher ([Install Bun](https://bun.sh/))

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/php-ext.com.git
   cd php-ext.com
   ```

2. **Install dependencies**
   ```bash
   bun install
   ```

3. **Start development server**
   ```bash
   bun run dev
   ```

4. **Open in browser**
   ```
   http://localhost:5173
   ```

The site will load sample data from `public/sample-data/` by default.

### Available Scripts

```bash
bun run dev        # Start dev server (http://localhost:5173)
bun run build      # Build for production (outputs to dist/)
bun run preview    # Preview production build locally
bun run lint       # Run ESLint
```

## Configuration

### Environment Variables

Configure the site using environment variables at build time:

#### `VITE_BASE_PATH` (Optional)
- **Purpose**: Sets the base path for assets and routing
- **Default**: Auto-detected from repository name
- **Examples**: 
  - User/org site: `/`
  - Project site: `/php-ext.com/`
- **When to set**: Only needed to override auto-detection

#### `VITE_DATASET_URL` (Optional)
- **Purpose**: Full URL to your manifest.json file
- **Default**: Uses `VITE_DATASET_BASE_URL` + `manifest.json`
- **Example**: `https://data.example.com/php-builds/manifest.json`

#### `VITE_DATASET_BASE_URL` (Optional)
- **Purpose**: Base URL for dataset files (manifest and snapshots)
- **Default**: `/sample-data/` (uses public folder in development)
- **Example**: `https://data.example.com/php-builds/`

### Local Development with Custom Dataset

```bash
# Point to a remote dataset
VITE_DATASET_URL=https://api.example.com/manifest.json bun run dev

# Use a custom base URL
VITE_DATASET_BASE_URL=https://data.example.com/datasets/ bun run dev

# Build with custom base path (if needed)
VITE_BASE_PATH=/my-app/ bun run build
```

## GitHub Pages Deployment

### Automatic Setup

The project is configured for automatic deployment to GitHub Pages using GitHub Actions.

1. **Enable GitHub Pages**
   - Go to **Settings** → **Pages**
   - Under **Source**, select **GitHub Actions**

2. **Configure Repository Variables** (Optional)
   
   Go to **Settings** → **Secrets and variables** → **Actions** → **Variables** tab:

   | Variable | Value | Required? |
   |----------|-------|-----------|
   | `VITE_BASE_PATH` | `/` or `/repo-name/` | ⚠️ Only if auto-detection fails |
   | `VITE_DATASET_URL` | URL to your manifest.json | ❌ No (defaults to sample data) |
   | `VITE_DATASET_BASE_URL` | Base URL for datasets | ❌ No |

   **Note**: The build automatically detects your repository name and sets the base path. You typically don't need to set `VITE_BASE_PATH` manually.

3. **Deploy**
   ```bash
   git push origin main
   ```

4. **Access Your Site**
   - User/org site: `https://username.github.io/`
   - Project site: `https://username.github.io/repository-name/`

### Routing Strategy

This project uses **HashRouter** (`#/` URLs) for reliable GitHub Pages compatibility:

- ✅ **Works everywhere**: No server configuration needed
- ✅ **Shareable URLs**: Direct links work (e.g., `/#/matrix?platform=alpine`)
- ✅ **Refresh safe**: Browser refresh works on any page
- ✅ **Filter preservation**: Query params work with hash routing

URLs look like:
- `https://example.com/#/`
- `https://example.com/#/matrix?platform=alpine`
- `https://example.com/#/ext/redis`

## Publishing Your Own Dataset

### Dataset Format (Schema v1.0)

Your dataset consists of two files:

#### 1. `manifest.json`
```json
{
  "schema_version": "1.0",
  "generated_at": "2024-01-01T00:00:00Z",
  "publisher": {
    "repo": "your-org/your-repo",
    "commit": "abc123def456"
  },
  "latest": {
    "id": "2024-01-01",
    "url": "snapshots/latest.json",
    "timestamp": "2024-01-01T00:00:00Z"
  },
  "datasets": [...],
  "axes": {
    "php_versions": ["8.1", "8.2", "8.3"],
    "channels": ["stable", "dev"],
    "platforms": ["alpine", "debian"],
    "arches": ["amd64", "arm64"]
  }
}
```

#### 2. `snapshots/latest.json`
```json
{
  "schema_version": "1.0",
  "timestamp": "2024-01-01T00:00:00Z",
  "results": [
    {
      "extension": "gd",
      "extension_version": "1.0.0",
      "channel": "stable",
      "php_version": "8.2",
      "platform": "alpine",
      "platform_version": "alpine3.19",
      "arch": "amd64",
      "status": "success",
      "started_at": "2024-01-01T10:00:00Z",
      "finished_at": "2024-01-01T10:05:00Z",
      "run": {
        "url": "https://github.com/org/repo/actions/runs/123"
      },
      "artifact": {
        "url": "https://github.com/org/repo/releases/download/v1/gd.tar.gz"
      }
    }
  ]
}
```

### Publishing Options

#### Option 1: GitHub Pages (Same Repository)

1. Put your dataset in `public/data/`:
   ```
   public/
     data/
       manifest.json
       snapshots/
         latest.json
   ```

2. Set in repository variables:
   ```
   VITE_DATASET_BASE_URL=/data/
   ```

3. Deploy - your data will be included in the build.

#### Option 2: Separate GitHub Repository

1. Create a `datasets` repository
2. Add your JSON files
3. Enable GitHub Pages for the repo
4. Set in your app's repository variables:
   ```
   VITE_DATASET_URL=https://username.github.io/datasets/manifest.json
   ```

#### Option 3: External Hosting (CDN, S3, etc.)

1. Upload your dataset files to any CORS-enabled server
2. Set in repository variables:
   ```
   VITE_DATASET_URL=https://cdn.example.com/php-builds/manifest.json
   ```

**Important**: Ensure CORS headers allow your Pages domain!

## Troubleshooting

### Issue: Blank page or 404 on refresh

**Cause**: Browser routing vs. server routing mismatch

**Solution**: This project uses HashRouter which should prevent this. If you see this:
1. Verify `src/App.tsx` uses `HashRouter` (not `BrowserRouter`)
2. Check browser console for errors
3. Verify your GitHub Pages is configured to use GitHub Actions

### Issue: Assets not loading (404 on CSS/JS)

**Cause**: Incorrect base path configuration

**Solution**:
1. Check that `VITE_BASE_PATH` matches your actual deployment path
2. For `username.github.io` → use `/`
3. For `username.github.io/repo-name` → use `/repo-name/` (or let auto-detection handle it)
4. Clear browser cache and hard refresh (Ctrl+Shift+R)

### Issue: CORS errors when loading dataset

**Cause**: Dataset server doesn't allow cross-origin requests

**Solutions**:

**If using GitHub Pages for dataset:**
```json
// No action needed - GitHub Pages sets proper CORS headers
```

**If using custom server:**
```nginx
# Nginx configuration
add_header Access-Control-Allow-Origin "https://your-username.github.io" always;
add_header Access-Control-Allow-Methods "GET, OPTIONS" always;
add_header Access-Control-Allow-Headers "Content-Type" always;
```

**If using AWS S3:**
```json
{
  "CORSRules": [{
    "AllowedOrigins": ["https://your-username.github.io"],
    "AllowedMethods": ["GET"],
    "AllowedHeaders": ["*"],
    "MaxAgeSeconds": 3000
  }]
}
```

### Issue: Data not updating after deployment

**Cause**: Browser cache or CDN cache

**Solutions**:
1. **Clear browser cache**: Ctrl+Shift+R (hard refresh)
2. **Cache busting**: Add timestamp to dataset URLs
3. **Check cache headers**: Dataset should set appropriate `Cache-Control`
4. **Reload function**: Use the reload button in the UI (bypasses cache)

### Issue: Matrix shows "No extensions match"

**Cause**: Filters are too restrictive or data format mismatch

**Solutions**:
1. Click "Reset All Filters" in the filter bar
2. Check browser console for schema validation warnings
3. Verify your dataset matches the schema (see `src/types/index.ts`)
4. Check that `results` array exists in snapshot (not `extensions`)

### Issue: GitHub Actions build fails

**Solutions**:
1. Check the Actions tab for detailed error logs
2. Verify `bun.lock` is committed
3. Ensure TypeScript compilation succeeds locally: `bun run build`
4. Check environment variable syntax in `.github/workflows/deploy.yml`

### Development Tips

**Fast Refresh Not Working?**
```bash
# Clear Vite cache
rm -rf node_modules/.vite
bun run dev
```

**TypeScript Errors?**
```bash
# Run type checking
bunx tsc --noEmit
```

**Build Size Too Large?**
```bash
# Analyze bundle
bunx vite-bundle-visualizer
```

## Project Structure

```
php-ext.com/
├── .github/
│   └── workflows/
│       └── deploy.yml              # GitHub Pages deployment workflow
├── public/
│   └── sample-data/                # Sample dataset for local development
│       ├── manifest.json
│       └── snapshots/
│           └── latest.json
├── src/
│   ├── components/                 # React components
│   │   ├── CellDrawer.tsx         # Build detail drawer
│   │   ├── FilterBar.tsx          # Filter controls
│   │   ├── FilterSummary.tsx      # Active filters display
│   │   ├── Layout.tsx             # Navigation wrapper
│   │   ├── MatrixGrid.tsx         # Virtualized matrix grid
│   │   └── SchemaWarning.tsx      # Schema version warning
│   ├── context/
│   │   └── DatasetContext.tsx     # Global dataset state
│   ├── hooks/
│   │   ├── useFilters.ts          # Filter state management
│   │   └── useMatrixModel.ts      # Matrix computation
│   ├── pages/
│   │   ├── HomePage.tsx           # Landing page
│   │   ├── MatrixPage.tsx         # Matrix grid page
│   │   └── ExtensionDetailPage.tsx # Extension detail page
│   ├── types/
│   │   └── index.ts               # TypeScript interfaces
│   ├── utils/
│   │   ├── cache.ts               # localStorage caching
│   │   ├── data.ts                # Data utilities
│   │   ├── matrix.ts              # Matrix computation helpers
│   │   ├── schema.ts              # Schema validation
│   │   └── url.ts                 # URL parameter utilities
│   ├── App.tsx                    # Main app with routing
│   ├── main.tsx                   # React entry point
│   └── index.css                  # Global styles
├── .eslintrc.cjs                  # ESLint configuration
├── index.html                     # HTML template
├── package.json                   # Dependencies
├── tsconfig.json                  # TypeScript config
├── vite.config.ts                 # Vite configuration
└── README.md                      # This file
```

## Performance Features

- **Code Splitting**: React vendor bundle separate from app code
- **Virtualization**: Only renders visible rows in large matrices
- **Memoization**: Expensive computations cached with useMemo
- **Caching**: 10-minute localStorage cache for dataset
- **Tree Shaking**: Unused code eliminated in production
- **Asset Optimization**: Images and fonts optimized by Vite

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Credits

Built with ❤️ using React, TypeScript, and Vite.

## Links

- **Documentation**: See `DEVELOPMENT.md`, `DEPLOYMENT.md`, `GITHUB_PAGES_SETUP.md`
- **Issues**: [GitHub Issues](https://github.com/your-username/php-ext.com/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-username/php-ext.com/discussions)
