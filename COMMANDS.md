# Commands Reference

## Development

```bash
# Install dependencies
bun install

# Start development server
bun run dev

# Start on different port
bun run dev -- --port 3000

# Start with custom dataset URL
VITE_DATASET_URL=https://api.example.com/manifest.json bun run dev
```

## Building

```bash
# Production build
bun run build

# Build with custom dataset URL
VITE_DATASET_URL=https://api.example.com/manifest.json bun run build

# Preview production build locally
bun run preview

# Build and preview
bun run build && bun run preview
```

## Linting

```bash
# Run ESLint
bun run lint

# Auto-fix issues
bunx eslint . --ext ts,tsx --fix
```

## Testing

```bash
# Test build
bun run build

# Test dev server
bun run dev

# Test with different dataset
VITE_DATASET_URL=./test-data/manifest.json bun run dev
```

## Deployment

```bash
# Deploy to GitHub Pages (automatic)
git add .
git commit -m "Update site"
git push origin main

# Manual deploy (build only)
bun run build
# Then upload dist/ folder to your hosting
```

## Maintenance

```bash
# Update dependencies
bun update

# Clean build artifacts
rm -rf dist

# Clean all
rm -rf dist node_modules bun.lock
bun install

# Check for outdated packages
bun outdated
```

## Utilities

```bash
# Check TypeScript types
bunx tsc --noEmit

# Format code (if prettier installed)
bunx prettier --write "src/**/*.{ts,tsx}"

# Count lines of code
find src -name "*.tsx" -o -name "*.ts" | xargs wc -l

# View bundle analysis
bunx vite-bundle-visualizer
```

## Development Workflow

```bash
# 1. Create feature branch
git checkout -b feature/my-feature

# 2. Make changes and test
bun run dev

# 3. Build and verify
bun run build
bun run preview

# 4. Lint
bun run lint

# 5. Commit and push
git add .
git commit -m "Add feature"
git push origin feature/my-feature

# 6. Create PR on GitHub
```

## Environment Variables

```bash
# Development (.env.local)
VITE_DATASET_URL=https://dev-api.example.com/manifest.json

# Production (GitHub Actions)
# Set in repository settings → Variables → DATASET_URL

# One-time use
VITE_DATASET_URL=https://api.example.com/data.json bun run build
```

## Troubleshooting

```bash
# Port already in use
lsof -ti:5173 | xargs kill -9

# Clear Vite cache
rm -rf node_modules/.vite

# Reinstall dependencies
rm -rf node_modules bun.lock
bun install

# Check what's using port 5173
lsof -i:5173

# View build output in detail
bun run build --debug

# Check for TypeScript errors
bunx tsc --noEmit
```

## Quick Commands

```bash
# Start fresh development
rm -rf node_modules dist bun.lock && bun install && bun run dev

# Full rebuild
bun run build

# Deploy (if on main branch)
git push

# Test production build locally
bun run build && bun run preview
```
