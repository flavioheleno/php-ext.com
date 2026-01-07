# GitHub Pages Configuration Guide

## Complete Setup Instructions

### Step 1: Enable GitHub Pages

1. Go to your repository on GitHub
2. Click **Settings** (top menu)
3. Click **Pages** (left sidebar)
4. Under "Build and deployment":
   - **Source**: Select "GitHub Actions"
   - Save changes

### Step 2: Configure Environment Variables

Go to **Settings** → **Secrets and variables** → **Actions** → **Variables** tab

Add the following repository variables:

#### Required Variables

**`VITE_BASE_PATH`** (Required for project sites)
- **Purpose**: Sets the base path for routing and assets
- **Value for project site**: `/repository-name/` (e.g., `/php-ext-com/`)
- **Value for user/org site**: `/`
- **Example**: `/php-ext-com/`

**Note**: Root-level GitHub Pages (username.github.io) use `/`, while project sites (username.github.io/repo-name) use `/repo-name/`

#### Optional Variables

**`VITE_DATASET_BASE_URL`** (Optional)
- **Purpose**: Base URL for dataset files
- **Default**: `/sample-data/`
- **Example**: `https://api.example.com/data/` or `/data/`
- Use this when your manifest.json is at a predictable base path

**`VITE_DATASET_URL`** (Optional - overrides VITE_DATASET_BASE_URL)
- **Purpose**: Full URL to manifest.json
- **Default**: Not set (uses VITE_DATASET_BASE_URL + manifest.json)
- **Example**: `https://api.example.com/v1/manifest.json`
- Use this for a complete custom URL

### Step 3: Push to GitHub

```bash
git add .
git commit -m "Configure GitHub Pages deployment"
git push origin main
```

The GitHub Action will automatically:
1. Install dependencies with Bun
2. Build the site with configured environment variables
3. Deploy to GitHub Pages

### Step 4: Access Your Site

After deployment completes (check Actions tab):
- **Project site**: `https://username.github.io/repository-name/`
- **User/org site**: `https://username.github.io/`

## Environment Variable Examples

### Example 1: Project Site with Sample Data
```
VITE_BASE_PATH=/php-ext-com/
# VITE_DATASET_BASE_URL and VITE_DATASET_URL not set - uses default /sample-data/
```

### Example 2: Project Site with External Dataset
```
VITE_BASE_PATH=/php-ext-com/
VITE_DATASET_URL=https://data.example.com/php-extensions/manifest.json
```

### Example 3: User/Org Site with Custom Data Path
```
VITE_BASE_PATH=/
VITE_DATASET_BASE_URL=/data/extensions/
```

### Example 4: Project Site with Relative Data Path
```
VITE_BASE_PATH=/php-ext-com/
VITE_DATASET_BASE_URL=/php-ext-com/data/
```

## Local Development

### Default Behavior
```bash
bun run dev
```
Uses `/sample-data/` by default, accessible at `http://localhost:5173/`

### With Environment Variables
```bash
VITE_BASE_PATH=/php-ext-com/ bun run dev
```

Or create `.env.local`:
```env
VITE_BASE_PATH=/php-ext-com/
VITE_DATASET_BASE_URL=/sample-data/
```

Then run:
```bash
bun run dev
```

### Preview Production Build
```bash
VITE_BASE_PATH=/php-ext-com/ bun run build
bun run preview
```

## Troubleshooting

### Issue: Blank page or 404s after deployment
**Solution**: Check `VITE_BASE_PATH` matches your repository structure
- Project site: Must include leading and trailing slashes (e.g., `/repo-name/`)
- User site: Use `/`

### Issue: Routing doesn't work (404 on refresh)
**Cause**: GitHub Pages doesn't support client-side routing by default
**Solution**: The site uses `BrowserRouter` which works with GitHub Pages SPA handling

### Issue: Assets not loading (CSS/JS 404)
**Solution**: Verify `VITE_BASE_PATH` is correctly set
```bash
# Check build output
cat dist/index.html | grep -o 'src="[^"]*"'
# Should show: src="/php-ext-com/assets/..."
```

### Issue: Dataset not loading
**Solutions**:
1. Check `VITE_DATASET_BASE_URL` or `VITE_DATASET_URL` is correct
2. Verify dataset URL is accessible (check CORS if external)
3. Check browser console for errors
4. Verify JSON format matches expected schema

### Issue: Different behavior locally vs production
**Solution**: Build and preview locally with same env vars:
```bash
VITE_BASE_PATH=/php-ext-com/ bun run build
bun run preview
```

## Advanced Configuration

### Custom Domain

1. Add a `CNAME` file to `/public/`:
   ```
   your-domain.com
   ```

2. Configure DNS:
   ```
   Type: CNAME
   Name: @
   Value: username.github.io
   ```

3. In GitHub Pages settings, set custom domain

4. Update `VITE_BASE_PATH` to `/`

### Multiple Environments

Use different branches for different environments:

**Production** (main branch):
```
VITE_BASE_PATH=/php-ext-com/
VITE_DATASET_URL=https://api.example.com/prod/manifest.json
```

**Staging** (staging branch):
```
VITE_BASE_PATH=/php-ext-com-staging/
VITE_DATASET_URL=https://api.example.com/staging/manifest.json
```

## Verification Checklist

After deployment:
- [ ] Site loads at GitHub Pages URL
- [ ] Navigation works (Home, Matrix links)
- [ ] Can navigate to extension detail pages
- [ ] Browser refresh works on all routes
- [ ] Filters work and persist in URL
- [ ] Data loads correctly
- [ ] Assets (CSS, JS) load without errors
- [ ] No console errors

## Quick Reference

| Variable | Purpose | Default | Example |
|----------|---------|---------|---------|
| `VITE_BASE_PATH` | Base path for routing/assets | `/` | `/php-ext-com/` |
| `VITE_DATASET_BASE_URL` | Base URL for dataset | `/sample-data/` | `https://api.example.com/` |
| `VITE_DATASET_URL` | Full URL to manifest.json | Uses BASE_URL + `manifest.json` | `https://api.example.com/v1/manifest.json` |

## Need Help?

Check the Actions tab in your repository for build logs and error messages.
