# Deployment Guide

## GitHub Pages Deployment

### Automatic Deployment

The site is configured to automatically deploy to GitHub Pages when you push to the `main` branch.

#### Setup Steps:

1. **Enable GitHub Pages**:
   - Go to your repository settings
   - Navigate to "Pages" section
   - Under "Build and deployment", select "GitHub Actions" as the source

2. **Configure Dataset URL (Optional)**:
   - Go to repository Settings → Secrets and variables → Actions → Variables
   - Add a new variable named `DATASET_URL`
   - Set the value to your dataset manifest URL (e.g., `https://data.example.com/manifest.json`)
   - If not set, it will use the sample data included in the repository

3. **Push to main branch**:
   ```bash
   git add .
   git commit -m "Initial commit"
   git push origin main
   ```

4. **Monitor deployment**:
   - Go to the "Actions" tab in your repository
   - Watch the deployment workflow run
   - Once complete, your site will be live at `https://yourusername.github.io/php-ext.com/`

### Manual Deployment

If you prefer to deploy manually:

```bash
# Build the project
bun run build

# The dist/ folder contains all static files
# Upload to any static hosting service
```

### Custom Domain

To use a custom domain:

1. Add a `CNAME` file to the `public/` directory with your domain name
2. Configure DNS settings to point to GitHub Pages
3. Enable "Enforce HTTPS" in repository settings

## Environment Variables

### Build-time Variables

- `VITE_DATASET_URL`: URL to the dataset manifest.json
  - Default: `./sample-data/manifest.json`
  - Example: `https://api.example.com/data/manifest.json`

Set during build:
```bash
VITE_DATASET_URL=https://api.example.com/data/manifest.json bun run build
```

## Dataset Configuration

### Using External Dataset

To use a dataset hosted elsewhere:

1. **GitHub Actions**: Set the `DATASET_URL` repository variable
2. **Local build**: Use environment variable as shown above

### Using Relative Paths

The dataset URL can be relative to the site:

```bash
# Will load from https://yoursite.com/data/manifest.json
VITE_DATASET_URL=./data/manifest.json bun run build
```

### Dataset URL Resolution

The app resolves URLs in this order:

1. Runtime: Uses `VITE_DATASET_URL` if set during build
2. Fallback: Uses `./sample-data/manifest.json`

Snapshot URLs in manifest.json are resolved relative to the manifest URL:
- Absolute URLs (starting with `http`) are used as-is
- Relative URLs are resolved relative to the manifest.json location

## Troubleshooting

### Build Fails

1. Check TypeScript errors: `bun run build`
2. Verify all dependencies are installed: `bun install`
3. Check Node/Bun version compatibility

### Data Not Loading

1. Check browser console for errors
2. Verify dataset URL is accessible (check CORS)
3. Validate JSON format matches expected schema
4. Check network tab for 404s

### GitHub Actions Failing

1. Check if GitHub Pages is enabled
2. Verify workflow permissions (Settings → Actions → General → Workflow permissions)
3. Ensure `DATASET_URL` variable is set correctly (if using external data)

## Performance Optimization

The build is already optimized with:

- Code splitting (react-vendor, virtualization chunks)
- Tree shaking
- Minification
- Gzip compression

For 200+ extensions:
- Virtualization ensures only visible rows are rendered
- Filtering is done in-memory with memoization
- No unnecessary re-renders

## Monitoring

Track performance in production:

1. Use browser DevTools → Performance tab
2. Monitor Core Web Vitals
3. Check bundle sizes in build output
4. Test on various devices and network speeds
