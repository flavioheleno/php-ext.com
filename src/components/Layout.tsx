import { Link, useLocation } from 'react-router-dom';
import { ReactNode } from 'react';

interface LayoutProps {
  children: ReactNode;
}

export function Layout({ children }: LayoutProps) {
  const location = useLocation();

  return (
    <div className="app">
      <nav className="nav">
        <div className="nav-container">
          <Link to="/" className="nav-brand">
            PHP Extension Matrix
          </Link>
          <div className="nav-links">
            <Link 
              to="/" 
              className={location.pathname === '/' ? 'nav-link active' : 'nav-link'}
            >
              Home
            </Link>
            <Link 
              to="/matrix" 
              className={location.pathname === '/matrix' ? 'nav-link active' : 'nav-link'}
            >
              Matrix
            </Link>
          </div>
        </div>
      </nav>
      <main className="main">
        {children}
      </main>
      <footer className="footer">
        <div className="footer-container">
          <p>© 2024 PHP Extension Matrix | Open Source</p>
        </div>
      </footer>
    </div>
  );
}
