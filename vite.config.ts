import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Automatically determine base path for GitHub Pages
// If VITE_BASE_PATH is set, use it
// Otherwise, if running in GitHub Actions for a non-root repo, use /{repo-name}/
// Otherwise, use /
const getBasePath = () => {
  if (process.env.VITE_BASE_PATH) {
    return process.env.VITE_BASE_PATH;
  }
  
  // In GitHub Actions, GITHUB_REPOSITORY is set to owner/repo
  if (process.env.GITHUB_REPOSITORY) {
    const repo = process.env.GITHUB_REPOSITORY.split('/')[1];
    // Only use repo name as base if it's not the special github.io repo
    if (!repo.endsWith('.github.io')) {
      return `/${repo}/`;
    }
  }
  
  return '/';
};

export default defineConfig({
  plugins: [react()],
  base: getBasePath(),
  build: {
    outDir: 'dist',
    sourcemap: true,
    rollupOptions: {
      output: {
        manualChunks: {
          'react-vendor': ['react', 'react-dom', 'react-router-dom'],
          'virtualization': ['react-window']
        }
      }
    }
  }
})
