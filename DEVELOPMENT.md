# Development Guide

## Project Structure

```
php-ext-com/
├── .github/
│   └── workflows/
│       └── deploy.yml          # GitHub Pages CI/CD
├── public/
│   ├── sample-data/            # Sample dataset for local dev
│   │   ├── manifest.json
│   │   └── snapshots/
│   │       └── latest.json
│   └── favicon.svg
├── src/
│   ├── components/             # React components
│   │   ├── CellDrawer.tsx     # Cell detail drawer
│   │   ├── ExtensionDetail.tsx # Extension detail page
│   │   ├── FilterBar.tsx      # Filter controls
│   │   └── MatrixGrid.tsx     # Virtualized grid
│   ├── hooks/                  # Custom React hooks
│   │   ├── useDataset.ts      # Dataset loading
│   │   └── useFilters.ts      # Filter state management
│   ├── types/                  # TypeScript definitions
│   │   └── index.ts           # All type definitions
│   ├── utils/                  # Utility functions
│   │   ├── data.ts            # Data transformation
│   │   └── url.ts             # URL param handling
│   ├── App.tsx                # Main app component
│   ├── main.tsx               # Entry point
│   ├── index.css              # Global styles
│   └── vite-env.d.ts          # Vite type definitions
├── index.html
├── package.json
├── tsconfig.json
├── tsconfig.node.json
├── vite.config.ts
└── README.md
```

## Development Workflow

### Local Development

```bash
# Install dependencies
bun install

# Start dev server
bun run dev

# Open http://localhost:5173
```

The dev server uses sample data from `public/sample-data/`.

### Building

```bash
# Production build
bun run build

# Preview production build locally
bun run preview
```

### Linting

```bash
# Run ESLint
bun run lint
```

## Key Features

### 1. Data Loading (`useDataset` hook)

- Fetches manifest.json
- Loads latest snapshot
- Handles errors gracefully
- Supports both relative and absolute URLs

### 2. Filtering (`useFilters` hook)

- Manages filter state
- Syncs with URL query parameters
- Allows sharing filtered views via URL

### 3. Virtualization (`MatrixGrid`)

- Uses `react-window` for efficient rendering
- Only renders visible rows
- Handles 200+ extensions smoothly

### 4. State Management

- No external state library needed
- React hooks for local state
- URL for shareable state
- Memoization for performance

## Adding Features

### Adding a New Filter

1. Update `FilterState` in `src/types/index.ts`
2. Add filter logic in `src/utils/data.ts` → `filterExtensions`
3. Add UI control in `src/components/FilterBar.tsx`
4. Update URL sync in `src/utils/url.ts`

### Customizing Styles

All styles are in `src/index.css`. Uses CSS custom properties for theming:

```css
:root {
  --primary: #4f46e5;
  --success: #10b981;
  /* etc... */
}
```

### Changing Data Source

Update `VITE_DATASET_URL` environment variable:

```bash
# .env.local (for local development)
VITE_DATASET_URL=https://api.example.com/manifest.json
```

Or set during build:
```bash
VITE_DATASET_URL=https://api.example.com/manifest.json bun run build
```

## Performance Tips

### Optimization Techniques Used

1. **Virtualization**: Only render visible rows
2. **Memoization**: `useMemo` for expensive calculations
3. **Code Splitting**: Separate chunks for vendor code
4. **Debouncing**: Could add for search input
5. **Lazy Loading**: Could add for drawer components

### Profiling

```bash
# Build with source maps
bun run build

# Use browser DevTools → Performance
# Profile component renders with React DevTools
```

## Testing

### Manual Testing Checklist

- [ ] Filters work correctly
- [ ] Search finds extensions
- [ ] Matrix cells display correctly
- [ ] Cell drawer shows details
- [ ] Extension detail shows all data
- [ ] URL parameters persist filters
- [ ] Mobile responsive
- [ ] Data loads from external URL
- [ ] Error states display properly
- [ ] 200+ extensions perform well

### Testing with Different Datasets

1. Create a test dataset in `public/test-data/`
2. Update manifest.json
3. Set `VITE_DATASET_URL=./test-data/manifest.json`
4. Run dev server

## Troubleshooting

### TypeScript Errors

```bash
# Check types
bun run build

# Common issues:
# - Missing type imports
# - Incorrect prop types
# - Missing null checks
```

### Build Errors

```bash
# Clear cache and rebuild
rm -rf dist node_modules bun.lockb
bun install
bun run build
```

### Dev Server Issues

```bash
# Kill process on port 5173
lsof -ti:5173 | xargs kill -9

# Restart
bun run dev
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## Architecture Decisions

### Why Vite?
- Fast HMR for development
- Optimized production builds
- Modern ESM-based

### Why react-window?
- Lightweight virtualization
- Simple API
- Good performance

### Why No State Library?
- App is simple enough
- React hooks sufficient
- Reduces bundle size

### Why URL Parameters?
- Shareable links
- Browser back/forward works
- No additional state management needed
