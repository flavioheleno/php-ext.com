# Deployment Summary

This document provides a quick reference for the GitHub Pages deployment configuration.

## Deployment Files

### 1. `.github/workflows/deploy.yml`

✅ **Status**: Production-ready

**Key Features:**
- Uses official GitHub Actions: `upload-pages-artifact@v3` and `deploy-pages@v4`
- Proper permissions: `pages: write`, `id-token: write`
- Concurrency control to prevent concurrent deployments
- Environment variables configurable via repository settings
- Two-job workflow: build → deploy
- Bun for fast installs and builds

**Environment Variables:**
- `VITE_BASE_PATH` - Auto-detected or set manually
- `VITE_DATASET_URL` - Optional custom dataset location
- `VITE_DATASET_BASE_URL` - Optional dataset base path

### 2. `vite.config.ts`

✅ **Status**: Auto-detection enabled

**Key Features:**
- Automatic base path detection from `GITHUB_REPOSITORY`
- Handles both user sites (username.github.io) and project sites
- Manual override via `VITE_BASE_PATH` environment variable
- Optimized build with code splitting
- Source maps enabled for debugging

**Auto-detection Logic:**
```
If VITE_BASE_PATH is set → use it
Else if in GitHub Actions:
  - If repo is username.github.io → use /
  - Otherwise → use /repo-name/
Else → use /
```

### 3. `src/App.tsx`

✅ **Status**: HashRouter implemented

**Router Choice:** HashRouter (not BrowserRouter)

**Why HashRouter?**
- ✅ No server configuration needed
- ✅ Works on GitHub Pages out of the box
- ✅ Refresh works on any page
- ✅ Direct links work (/#/matrix, /#/ext/redis)
- ✅ Query parameters preserved (/#/matrix?platform=alpine)

**Trade-off:**
- URLs have `#` (e.g., `/#/matrix` instead of `/matrix`)
- This is standard for GitHub Pages SPAs

### 4. `README.md`

✅ **Status**: Comprehensive documentation

**Sections:**
1. Quick Start - Get running in 3 commands
2. Configuration - All environment variables explained
3. GitHub Pages Deployment - Step-by-step setup
4. Dataset Publishing - 3 different approaches
5. Troubleshooting - 7 common issues with solutions
6. Project Structure - Complete file tree
7. Performance Features - Optimization details

## Deployment Checklist

### First-Time Setup

- [ ] Fork/clone repository
- [ ] Enable GitHub Pages (Settings → Pages → Source: GitHub Actions)
- [ ] (Optional) Set repository variables if needed
- [ ] Push to main branch
- [ ] Wait for Actions workflow to complete
- [ ] Visit your site at `https://username.github.io/repo-name/`

### Updating Content

- [ ] Make changes locally
- [ ] Test with `bun run dev`
- [ ] Build with `bun run build`
- [ ] Commit and push to main
- [ ] GitHub Actions deploys automatically

### Custom Dataset

**Option 1: Include in repository**
```bash
# Add dataset to public folder
mkdir -p public/data
cp manifest.json public/data/
cp -r snapshots public/data/

# Set repository variable
VITE_DATASET_BASE_URL=/data/

# Commit and push
git add public/data
git commit -m "Add dataset"
git push
```

**Option 2: External dataset**
```bash
# Set repository variable to external URL
VITE_DATASET_URL=https://data.example.com/manifest.json

# Ensure CORS is configured on the external server
# Then commit any code changes and push
```

## Verification

After deployment, verify:

1. **Site loads**: Visit your GitHub Pages URL
2. **Routing works**: Navigate to `/#/matrix` and `/#/ext/gd`
3. **Refresh works**: Hard refresh (Ctrl+Shift+R) on any page
4. **Filters work**: Apply filters, check URL parameters
5. **Data loads**: Check browser console for errors
6. **Assets load**: Check Network tab, all CSS/JS should load

## Monitoring

- **Build status**: Check Actions tab for workflow status
- **Deploy logs**: Click on workflow run for detailed logs
- **Console errors**: Use browser DevTools to check for runtime errors
- **Performance**: Use Lighthouse to audit performance

## Rollback

If a deployment fails:

```bash
# Revert the commit
git revert HEAD

# Push to trigger new deployment
git push origin main
```

Or use GitHub's deployment history:
1. Go to Settings → Pages
2. Find previous successful deployment
3. Click to redeploy

## Common Issues

### Issue: 404 on refresh
**Solution**: Should not happen with HashRouter. If it does, verify:
- `src/App.tsx` uses `HashRouter` (not `BrowserRouter`)
- Browser URL includes `#` (e.g., `/#/matrix`)

### Issue: Assets not loading
**Solution**: Check base path configuration:
```bash
# In browser console
console.log(import.meta.env.BASE_URL)

# Should match your deployment path
# User site: "/"
# Project site: "/repo-name/"
```

### Issue: Data not loading
**Solution**: Check CORS and dataset URL:
1. Open browser DevTools → Network tab
2. Look for manifest.json request
3. Check response status (should be 200)
4. If CORS error, configure server headers
5. If 404, verify VITE_DATASET_URL is correct

## Support

- **Documentation**: See README.md for detailed guides
- **Issues**: GitHub Issues for bug reports
- **Discussions**: GitHub Discussions for questions

## File Locations

```
.github/workflows/deploy.yml    # Deployment workflow
vite.config.ts                  # Build configuration
src/App.tsx                     # Router setup
README.md                       # User documentation
DEPLOYMENT_SUMMARY.md           # This file
```

## Quick Commands

```bash
# Local development
bun run dev

# Build for production
bun run build

# Preview production build
bun run preview

# Check TypeScript
bunx tsc --noEmit

# Check bundle size
ls -lh dist/assets/

# Serve dist folder locally
bunx serve dist
```
