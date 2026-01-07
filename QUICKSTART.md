# Quick Start Guide

Get php-ext.com v2 running in under 5 minutes!

## 🚀 Quick Start

### 1. Clone and Install

```bash
git clone <your-repo-url> php-ext-com
cd php-ext-com
bun install
```

### 2. Run Locally

```bash
bun run dev
```

Open [http://localhost:5173](http://localhost:5173) - you'll see sample data with 10 extensions.

### 3. Deploy to GitHub Pages

```bash
# Initialize git (if not already)
git init
git add .
git commit -m "Initial commit"

# Push to GitHub
git remote add origin <your-github-repo>
git push -u origin main
```

Enable GitHub Pages in repository settings → Pages → Source: "GitHub Actions"

Done! Your site will be live at `https://yourusername.github.io/repo-name/`

## 🎯 Common Tasks

### Use Custom Dataset

**Option 1: Environment Variable (one-time)**
```bash
VITE_DATASET_URL=https://api.example.com/manifest.json bun run dev
```

**Option 2: GitHub Variable (persistent)**
1. Go to Settings → Secrets and variables → Actions → Variables
2. Add `DATASET_URL` = `https://api.example.com/manifest.json`
3. Push to trigger deployment

### Share Filtered View

Just copy the URL! Filters are in query parameters:
```
https://site.com/?channel=stable&platform=linux&pv=alpine3.19&arch=x86_64
```

### Add More Sample Data

Edit `public/sample-data/snapshots/latest.json` and add extensions:

```json
{
  "name": "your-extension",
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
```

## 📊 Dataset Format

Your external dataset needs two files:

**manifest.json**
```json
{
  "latest": "snapshots/latest.json",
  "snapshots": [...]
}
```

**snapshots/latest.json**
```json
{
  "timestamp": "2024-01-01T00:00:00Z",
  "extensions": [
    {
      "name": "extension-name",
      "availability": [...]
    }
  ]
}
```

See `public/sample-data/` for complete examples.

## 🔧 Configuration

### Vite Config

Located in `vite.config.ts`. Key settings:

- `base: '/'` - Change for non-root deployments
- Build optimization already configured
- Code splitting enabled

### TypeScript

Strict mode enabled. See `tsconfig.json`.

### Linting

ESLint configured with React rules. Run:
```bash
bun run lint
```

## 📱 Features Overview

### Matrix Grid
- Shows all extensions × PHP versions
- Green = available, Gray = not available
- Click cell for details
- Virtualized for performance (handles 200+ extensions)

### Filters
- **Search**: Type extension name
- **Channel**: stable, edge, etc.
- **Platform**: linux, etc.
- **Platform Version**: alpine3.19, debian12, etc.
- **Architecture**: x86_64, aarch64, etc.

### Details
- **Click extension name**: Full extension details
- **Click cell**: Availability details for that PHP version

## 🐛 Troubleshooting

**Port 5173 already in use**
```bash
lsof -ti:5173 | xargs kill -9
bun run dev
```

**Build fails**
```bash
rm -rf dist
bun run build
```

**Data not loading**
- Check browser console for errors
- Verify dataset URL is accessible
- Check CORS headers if using external URL

## 📚 Learn More

- [README.md](./README.md) - Full documentation
- [DEVELOPMENT.md](./DEVELOPMENT.md) - Architecture & development
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Deployment details

## 🎨 Customization

### Change Colors

Edit CSS variables in `src/index.css`:
```css
:root {
  --primary: #4f46e5;  /* Change to your brand color */
  --success: #10b981;  /* Available cell color */
}
```

### Change Port

```bash
bun run dev -- --port 3000
```

### Change Title

Edit `index.html`:
```html
<title>Your Custom Title</title>
```

## 💡 Tips

1. **Performance**: Site handles 200+ extensions smoothly thanks to virtualization
2. **Sharing**: URLs include filter state - just copy and share
3. **Updates**: Dataset updates automatically on page reload
4. **Mobile**: Fully responsive design
5. **No Backend**: Everything runs client-side

## 🚢 Production Checklist

- [ ] Set correct `VITE_DATASET_URL`
- [ ] Test with real dataset
- [ ] Verify all filters work
- [ ] Test on mobile devices
- [ ] Check page load time
- [ ] Verify GitHub Pages deployment
- [ ] Set up custom domain (optional)

Need help? Check the detailed guides in README.md and DEVELOPMENT.md!
